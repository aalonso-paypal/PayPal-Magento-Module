<?php
/**
 * Smart E-commerce do Brasil Tecnologia LTDA
 *
 * INFORMAÇÕES SOBRE LICENÇA
 *
 * Open Software License (OSL 3.0).
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Não edite este arquivo caso você pretenda atualizar este módulo futuramente
 * para novas versões.
 *
 * @category  Esmart
 * @package   Esmart_PayPalBrasil
 * @copyright Copyright (c) 2015 Smart E-commerce do Brasil Tecnologia LTDA. (http://www.e-smart.com.br)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @author        Ricardo Martins <ricardo.martins@e-smart.com.br>
 * @author        Thiago H Oliveira <thiago.oliveira@e-smart.com.br>
 */
class Esmart_PayPalBrasil_Model_Plus extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Code payment method
     * @const string
     */
    const LOG_FILENAME = 'ppplusbrasil_exception.log';

    /**
     * Code payment method
     * @const string
     */
    const CODE = 'paypal_plus';

    /**
     * Intent of the payment default
     * @const string
     */
    const INTENT_PAYMENT = 'sale';

    /**
     * Allowed payment method default
     * @const string
     */
    const ALLOWED_PAYMENT_METHOD = 'IMMEDIATE_PAY';

    /**
     * Tax ID type default
     * @const string
     */
    const PAYER_TAX_ID_TYPE_CPF = 'BR_CPF';

    /**
     * Tax ID type default
     * @const string
     */
    const PAYER_TAX_ID_TYPE_CNPJ = 'BR_CPNJ';

    /**
     * Mode sandbox
     * @const string
     */
    const MODE_SANDBOX = 'sandbox';

    /**
     * Mode live
     * @const string
     */
    const MODE_LIVE = 'live';

    /**
     * Payment method default
     * @const string
     */
    const PAYMENT_METHOD = 'paypal';

    /**
     * Payment method default
     * @const string
     */
    const PROFILER_BASE_NAME = '%s #%d (Module Magento)';

    /**
     * Custom information
     * @const string
     */
    const CUSTOM_BASE_INFORMATION = '%s (Pedido: #%d)';

    protected $_code            = self::CODE;
    protected $_formBlockType   = 'esmart_paypalbrasil/plus_form';
    protected $_infoBlockType   = 'esmart_paypalbrasil/plus_info';

    protected $_isGateway       = true;
    protected $_canAuthorize    = true;
    protected $_canCapture      = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund       = false;
    protected $_canVoid         = true;
    protected $_canUseInternal  = true;
    protected $_canUseCheckout  = true;
    protected $_canUseForMultishipping = true;
    protected $_canSaveCc       = false;
    protected $_canOrder        = true;

    /**
     * Non-persisted data
     * @var Varien_Object
     */
    protected $nonPersistedData;

    /**
     * Constructor
     *
     * By default is looking for first argument as array and assignes it as object attributes
     * This behaviour may change in child classes
     */
    public function __construct()
    {
        parent::__construct();

        $this->nonPersistedData = new Varien_Object();

        spl_autoload_unregister(array(Varien_Autoload::instance(), 'autoload'));
        spl_autoload_register(array($this, 'autoload'), true, true);
        spl_autoload_register(array(Varien_Autoload::instance(), 'autoload'));
    }

    /**
     * Check whether payment method can be used
     *
     * TODO: payment method instance is not supposed to know about quote
     *
     * @param Mage_Sales_Model_Quote|null $quote
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        return Mage::getStoreConfig('payment/paypal_plus/active');
    }

    /**
     * Return Approval URL PaypalPlus
     *
     * @param mixed $quote (Mage_Sales_Model_Quote | Quote ID | null)
     *
     * @return array
     */
    public function getApprovalUrlPaypalPlus($quote = null)
    {
        $payment = $this->createPayment($quote);

        $data = array(
            'approvalUrl' => $payment->getApprovalLink(),
            'mode'        => $this->getMode(),
        );

        Esmart_PayPalBrasil_Model_Debug::appendContent('[APPROVAL URL]', 'createPayment', $data);

        return $data;
    }

    /**
     * Get OAuth credential
     *
     * @return PayPal\Auth\OAuthTokenCredential
     */
    public function getOAuthCredential()
    {
        $helper    = Mage::helper('core');
        $clientId  = $helper->decrypt(Mage::getStoreConfig('payment/paypal_plus/app_client_id'));
        $appSecret = $helper->decrypt(Mage::getStoreConfig('payment/paypal_plus/app_secret'));

        $oAuthToken =  new PayPal\Auth\OAuthTokenCredential($clientId, $appSecret);

        return $oAuthToken;
    }

    /**
     * Get API Context
     *
     * @return PayPal\Rest\ApiContext
     */
    public function getApiContext()
    {
        /** @var PayPal\Rest\ApiContext $apiContext */
        $apiContext  = new PayPal\Rest\ApiContext($this->getOAuthCredential());

        $mode = array(
            'mode' => $this->getMode(),
        );

        $apiContext->setConfig($mode);

        Esmart_PayPalBrasil_Model_Debug::appendContent('[OPERATION MODE]', 'default', $mode);

        return $apiContext;
    }

    /**
     * Create and return Transaction
     *
     * @param Mage_Sales_Model_Quote $quote Quote object
     *
     * @return PayPal\Api\Transaction
     */
    protected function createTransaction(Mage_Sales_Model_Quote $quote)
    {
        /** @var PayPal\Api\Transaction $transaction */
        $transaction = new PayPal\Api\Transaction();

        if (!$quote->getReservedOrderId()) {
            $quote->reserveOrderId()->save();
        }

        $data = array('order' => $quote->getReservedOrderId());
        Esmart_PayPalBrasil_Model_Debug::appendContent('[QUOTE MAGENTO]', 'createPayment', $data);

        $customInfo = array(Mage::getStoreConfig('payment/paypal_plus/paypal_custom'), $quote->getReservedOrderId());
        $customInfo = vsprintf(self::CUSTOM_BASE_INFORMATION, $customInfo);

        $transaction->setAmount($this->createAmount($quote))
            ->setPaymentOptions($this->createPaymentOptions())
            ->setItemList($this->createItemList($quote))
            ->setCustom($customInfo);

        return $transaction;
    }

    /**
     * Create and return Payment
     *
     * @param mixed $quote (Quote object (Mage_Sales_Model_Quote) | Quote ID | null)
     *
     * @return PayPal\Api\Payment
     */
    public function createPayment($quote = null)
    {
        $helper = Mage::helper('esmart_paypalbrasil');

        $quote = $helper->getQuote($quote);

        $transaction = $this->createTransaction($quote);

        $payment = new PayPal\Api\Payment();

        $profileId = Mage::getStoreConfig('payment/paypal_plus/profiler_id');

        $data = array('profile_id' => $profileId);
        Esmart_PayPalBrasil_Model_Debug::appendContent('[PROFILE]', 'createPayment', $data);

        $payment->setIntent(self::INTENT_PAYMENT)
            ->setPayer($this->createPayer())
            ->setRedirectUrls($this->createRedirectUrls())
            ->setTransactions(array($transaction))
            ->setExperienceProfileId($profileId);

        try {
            $payment->create($this->getApiContext());

            $quote->getPayment()
                ->setAdditionalInformation('paypal_plus_payment_id', $payment->getId())
                ->setAdditionalInformation('paypal_plus_payment_state', $payment->getState())
                ->save();
        } catch (Exception $e) {
            $helper->logException(__FILE__, __CLASS__, __FUNCTION__, __LINE__, self::LOG_FILENAME, $e);
        }

        return $payment;
    }

    /**
     * Create and return Amount
     *
     * @param Mage_Sales_Model_Quote $quote Quote object
     *
     * @return PayPal\Api\Amount
     */
    protected function createAmount(Mage_Sales_Model_Quote $quote)
    {
        /** @var PayPal\Api\Amount $amount */
        $amount = new PayPal\Api\Amount();

        $amount->setCurrency($quote->getBaseCurrencyCode())
            ->setTotal($quote->getGrandTotal())
            ->setDetails($this->createDetails($quote));

        $data = array(
            'Base Currency' => $quote->getBaseCurrencyCode(),
            'Total'         => $quote->getGrandTotal(),
        );
        Esmart_PayPalBrasil_Model_Debug::appendContent('[CREATE AMOUNT]', 'createPayment', $data);

        return $amount;
    }

    /**
     * Create and return Details
     *
     * @param Mage_Sales_Model_Quote $quote Quote object
     *
     * @return PayPal\Api\Details
     */
    protected function createDetails(Mage_Sales_Model_Quote $quote)
    {
        /** @var PayPal\Api\Details $details */
        $details = new PayPal\Api\Details();

        $totals   = $quote->getTotals();

        $shipping = isset($totals['shipping']) ? $totals['shipping'] : null;
        if ($shipping instanceof Mage_Sales_Model_Quote_Address_Total) {
            $details->setShipping($shipping->getValue());
        }

        $tax = isset($totals['tax']) ? $totals['tax'] : null;
        if ($tax instanceof Mage_Sales_Model_Quote_Address_Total) {
            $details->setTax($tax->getValue());
        }

        $discount = 0;
        if (isset($totals['discount'])) {
            $discount = $totals['discount']->getValue();
        }

        $details->setSubtotal($totals['subtotal']->getValue() + $discount);

        $data = array(
            'Shipping' => $details->getShipping(),
            'Tax'      => $details->getTax(),
            'Subtotal' => $details->getSubtotal(),
        );
        Esmart_PayPalBrasil_Model_Debug::appendContent('[CREATE AMOUNT - DETAILS]', 'createPayment', $data);

        return $details;
    }

    /**
     * Create and return Payment Options
     *
     * @return PayPal\Api\PaymentOptions
     */
    protected function createPaymentOptions()
    {
        /** @var PayPal\Api\PaymentOptions $paymentOptions */
        $paymentOptions = new PayPal\Api\PaymentOptions();

        $paymentOptions->setAllowedPaymentMethod(self::ALLOWED_PAYMENT_METHOD);

        $data = array(
            'Allowed Payment Method' => $paymentOptions->getAllowedPaymentMethod(),
        );
        Esmart_PayPalBrasil_Model_Debug::appendContent('[PAYMENT OPTIONS]', 'createPayment', $data);

        return $paymentOptions;
    }

    /**
     * Create and return ItemList
     *
     * @param Mage_Sales_Model_Quote $quote Quote object
     *
     * @return PayPal\Api\ItemList
     */
    protected function createItemList(Mage_Sales_Model_Quote $quote)
    {
        $itemList = new PayPal\Api\ItemList();

        $quoteItems = $quote->getAllVisibleItems();

        $data = array();

        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($quoteItems as $item) {
            $objItem = new PayPal\Api\Item();

            $objItem->setName($item->getName())
                ->setCurrency($quote->getBaseCurrencyCode())
                ->setQuantity($item->getQty())
                ->setPrice($item->getPrice());

            $itemList->addItem($objItem);

            $data[] = $objItem->toJSON();
        }

        $totals   = $quote->getTotals();
        if (isset($totals['discount'])) {
            $objItem = new PayPal\Api\Item();

            $objItem->setName('Descontos')
                ->setCurrency($quote->getBaseCurrencyCode())
                ->setQuantity(1)
                ->setPrice($totals['discount']->getValue());

            $itemList->addItem($objItem);

            $data[] = $objItem->toJSON();
        }

        Esmart_PayPalBrasil_Model_Debug::appendContent('[CREATE ITEM LIST]', 'createPayment', $data);

        // append shipping information
        $itemList->setShippingAddress($this->createShippingAddress($quote));

        return $itemList;
    }

    /**
     * Create and return Payer
     *
     * @return PayPal\Api\Payer
     */
    protected function createPayer()
    {
        $payer = new \PayPal\Api\Payer();

        $payer->setPaymentMethod(self::PAYMENT_METHOD);

        $data = array('payment_method' => self::PAYMENT_METHOD);
        Esmart_PayPalBrasil_Model_Debug::appendContent('[PAYMENT_METHOD]', 'createPayment', $data);

        return $payer;
    }

    /**
     * Create and return RedirectUrls
     *
     * @return PayPal\Api\RedirectUrls
     */
    protected function createRedirectUrls()
    {
        $redirectUrls = new PayPal\Api\RedirectUrls();
        $baseUrl      = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

        $redirectUrls->setReturnUrl("$baseUrl/ExecutePayment.php?success=true")
                     ->setCancelUrl("$baseUrl/ExecutePayment.php?success=false");

        return $redirectUrls;
    }

    /**
     * Get customer information to use in JS
     *
     * @param Mage_Sales_Model_Quote $quote (Quote object (Mage_Sales_Model_Quote) | Quote ID | null)
     *
     * @return array
     */
    public function getCustomerInformation($quote = null)
    {
        /** @var Esmart_PayPalBrasil_Helper_Data $helper */
        $helper = Mage::helper('esmart_paypalbrasil');

        $quote  = $helper->getQuote($quote);

        $customer = $quote->getCustomer();

        $addressBilling = $customer->getDefaultBillingAddress();

        if (!$addressBilling) {
            $addressBilling = $quote->getBillingAddress();
        }

        $firstname = Mage::getStoreConfig('payment/paypal_plus/firstname');
        $lastname  = Mage::getStoreConfig('payment/paypal_plus/lastname');
        $email     = Mage::getStoreConfig('payment/paypal_plus/email');
        $phone     = Mage::getStoreConfig('payment/paypal_plus/phone');

        $payerTaxId = $helper->getCpfCnpjOrTaxvat($customer, $this->nonPersistedData);

        $return = array(
            'payerFirstName' => $helper->getDataFromObject($customer, $this->nonPersistedData, $firstname),
            'payerLastName'  => $helper->getDataFromObject($customer, $this->nonPersistedData, $lastname),
            'payerEmail'     => $helper->getDataFromObject($customer, $this->nonPersistedData, $email),
            'payerTaxIdType' => $helper->checkIsCpfOrCnpj($payerTaxId),
            'payerTaxId'     => $payerTaxId,
            'payerPhone'     => $helper->getDataFromObject($addressBilling, $this->nonPersistedData, $phone),
            'rememberedCards'=> $customer->getPpalRememberedCards(),
        );

        Esmart_PayPalBrasil_Model_Debug::appendContent('[RETURN getCustomerInformation()]', 'createPayment', $return);
        Esmart_PayPalBrasil_Model_Debug::appendContent('[MAGENTO CUSTOMER DATA]', 'createPayment', $customer->toArray());

        return $return;
    }

    /**
     * Get mode SANDBOX | LIVE
     *
     * @return string
     */
    public function getMode()
    {
        $sandboxWork = Mage::getStoreConfig('payment/paypal_plus/sandbox_flag');

        if ($sandboxWork) {
            return self::MODE_SANDBOX;
        }

        return self::MODE_LIVE;
    }

    /**
     * Create and return WebProfiler
     *
     * @return void
     */
    public function createWebProfiler()
    {
        $config = new Mage_Core_Model_Config();

        $helper = Mage::helper('esmart_paypalbrasil');

        $profilerName = Mage::getStoreConfig('payment/paypal_plus/profiler_name');

        if (empty($profilerName)) {
            $profilerName = $helper->getProfilerNameSuggestion();
            $config->saveConfig('payment/paypal_plus/profiler_name', $profilerName);
        }

        $webProfile = new \PayPal\Api\WebProfile();
        $webProfile->setName($profilerName)
            ->setFlowConfig($this->createFlowConfig())
            ->setPresentation($this->createPresentation($profilerName))
            ->setInputFields($this->createInputFields());

        try {
            $profiler = $webProfile->create($this->getApiContext());

            $profilerId = $profiler->getId();
            $config->saveConfig('payment/paypal_plus/profiler_id', $profilerId);
        } catch (Exception $e) {
            $config->saveConfig('payment/paypal_plus/profiler_id', null);

            $helper->logException(__FILE__, __CLASS__, __FUNCTION__, __LINE__, self::LOG_FILENAME, $e);
        }
    }

    /**
     * Create and return FlowConfig
     *
     * @return PayPal\Api\FlowConfig
     */
    protected function createFlowConfig()
    {
        $flowConfig = new \PayPal\Api\FlowConfig();

        return $flowConfig;
    }

    /**
     * Create and return Presentation
     *
     * @param string $name
     *
     * @return PayPal\Api\Presentation
     */
    protected function createPresentation($name)
    {
        $presentation = new \PayPal\Api\Presentation();

        $presentation->setBrandName($name)
            ->setLocaleCode("BR");

        return $presentation;
    }

    /**
     * Create and return InputFields
     *
     * @return PayPal\Api\InputFields
     */
    protected function createInputFields()
    {
        $inputFields = new \PayPal\Api\InputFields();

        $inputFields->setNoShipping(1)
            ->setAddressOverride(1);

        return $inputFields;
    }

    /**
     * Save return Paypal
     *
     * @param array
     */
    public function saveReturnPaypal(array $data)
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        if ($customer && $customer->getId()) {
            $rememberedCards = $data['rememberedCards'];

            if ($rememberedCards) {
                $customer->setPpalRememberedCards($rememberedCards)->save();
            }
        }

        $helper = Mage::helper('esmart_paypalbrasil');

        /** @var Mage_Sales_Model_Quote_Payment $quotePayment */
        $quotePayment = $helper->getQuote()->getPayment();

        $quotePayment->setAdditionalInformation('paypal_plus_payer_id', $data['payerId'])
            ->setAdditionalInformation('paypal_plus_payer_status', $data['payerStatus'])
            ->setAdditionalInformation('paypal_plus_checkout_token', $data['checkoutId'])
            ->setAdditionalInformation('paypal_plus_checkout_state', $data['checkoutState'])
            ->setAdditionalInformation('paypal_plus_cards', $data['cards']);

        $quotePayment->save();
    }

    /**
     * Get shipping information to use in JS
     *
     * @param Mage_Sales_Model_Quote $quote (Quote object (Mage_Sales_Model_Quote) | Quote ID | null)
     *
     * @return \PayPal\Api\ShippingAddress
     */
    public function createShippingAddress($quote = null)
    {
        /** @var Esmart_PayPalBrasil_Helper_Data $helper */
        $helper = Mage::helper('esmart_paypalbrasil');

        $quote  = $helper->getQuote($quote);

        $addressShipping = $quote->getShippingAddress();

        $shipping = new \PayPal\Api\ShippingAddress();

        $firstname = Mage::getStoreConfig('payment/paypal_plus/recipient_firstname');
        $firstname = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $firstname);

        $lastname = Mage::getStoreConfig('payment/paypal_plus/recipient_lastname');
        $lastname = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $lastname);

        $city = Mage::getStoreConfig('payment/paypal_plus/city');
        $city = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $city);

        $countryCode = Mage::getStoreConfig('payment/paypal_plus/country_code');
        $countryCode = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $countryCode);

        $postalCode = Mage::getStoreConfig('payment/paypal_plus/postal_code');
        $postalCode = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $postalCode);

        $state = Mage::getStoreConfig('payment/paypal_plus/state');
        $state = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $state);

        if (is_numeric($state)) {
            /** @var Mage_Directory_Model_Region $directoryRegion */
            $directoryRegion = Mage::getModel('directory/region')->load($state);
            $state = $directoryRegion->getName();
        }

        $line1_p1 = Mage::getStoreConfig('payment/paypal_plus/address_line_1_p1');
        $line1_p1 = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $line1_p1);

        $line1_p2 = Mage::getStoreConfig('payment/paypal_plus/address_line_1_p2');
        $line1_p2 = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $line1_p2);

        $line1_p3 = Mage::getStoreConfig('payment/paypal_plus/address_line_1_p3');
        $line1_p3 = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $line1_p3);

        $line1    = "{$line1_p1}, {$line1_p2}, {$line1_p3}";

        $line2 = Mage::getStoreConfig('payment/paypal_plus/address_line_2');
        $line2 = $helper->getDataFromObject($addressShipping, $this->nonPersistedData, $line2);

        $shipping->setRecipientName("{$firstname} {$lastname}")
            ->setCity($city)
            ->setCountryCode($countryCode)
            ->setPostalCode($postalCode)
            ->setLine1($line1)
            ->setLine2($line2)
            ->setState($state);

        $data = array(
            'Recipient Name' => $shipping->getRecipientName(),
            'Line 1'         => $shipping->getLine1(),
            'Line 2'         => $shipping->getLine2(),
            'City'           => $shipping->getCity(),
            'Postal Code'    => $shipping->getPostalCode(),
            'Country Code'   => $shipping->getCountryCode(),
            'State'          => $shipping->getState(),
        );
        Esmart_PayPalBrasil_Model_Debug::appendContent('[SHIPPING ADDRESS]', 'createPayment', $data);

        Esmart_PayPalBrasil_Model_Debug::appendContent('[MAGENTO ADDRESS DATA]', 'createPayment', $addressShipping->toArray());

        return $shipping;
    }

    /**
     * Autoload method
     *
     * @param string $className
     *
     * @return void
     */
    public function autoload($className)
    {
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }

        $dirs = explode(':', get_include_path());

        foreach ($dirs as $dir) {
            $fullPathFile = $dir . DS . $fileName . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

            if (file_exists($fullPathFile)) {
                require_once $fullPathFile;
                break;
            }
        }
    }

    /**
     * Order payment abstract method (executePayment)
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function order(Varien_Object $payment, $amount)
    {
        parent::order($payment, $amount);

        /** @var Esmart_PayPalBrasil_Helper_Data $helper */
        $helper = Mage::helper('esmart_paypalbrasil');

        try {
            $order = $payment->getOrder();

            $orderPayment = $order->getPayment();

            $apiContext = $this->getApiContext();

            $paypalPayment = \PayPal\Api\Payment::get($orderPayment->getAdditionalInformation('paypal_plus_payment_id'), $apiContext);

            $paymentExecution = new \PayPal\Api\PaymentExecution();
            $paymentExecution->setPayerId($orderPayment->getAdditionalInformation('paypal_plus_payer_id'));

            // Execute the payment
            $paypalPayment->execute($paymentExecution, $apiContext);

            $transactions = $paypalPayment->getTransactions();

            $saleId       = null;

            if ($transactions) {
                /** @var \PayPal\Api\Transaction $transaction */
                $transaction     = $transactions[0];

                $relatedResources = $transaction->getRelatedResources();

                /** @var \PayPal\Api\RelatedResources $relatedResource */
                $relatedResource = $relatedResources[0];

                /** @var \PayPal\Api\Sale $sale */
                $sale = $relatedResource->getSale();

                $saleId = $sale->getId();
            }

            $orderPayment->setAdditionalInformation('paypal_plus_payment_state', $paypalPayment->getState())
                ->setAdditionalInformation('paypal_plus_sale_id', $saleId)
                ->save();

            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

            if (!$invoice->getTotalQty()) {
                Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
            }

            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
            $invoice->register();

            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());

            $transactionSave->save();

        } catch(Exception $e) {
            $helper->logException(__FILE__, __CLASS__, __FUNCTION__, __LINE__, self::LOG_FILENAME, $e);

            Mage::throwException('Sua transação não pode ser concluida devido a problemas com seu meio de pagamento, tente novamente com outro cartão.');
        }

        return $this;
    }

    /**
     * Set non-persisted data
     *
     * @param array $postData
     *
     * @return $this
     */
    public function setNonPersistedData(array $postData, $firstCall = true)
    {
        foreach ($postData as $key => $value) {
            if (is_array($value)) {
                $this->setNonPersistedData($value, false);
                continue;
            }

            if (is_numeric($key)) {
                $key = $key + 1;
            }

            if (!empty($value)) {
                $this->nonPersistedData->setData($key, $value);
            }
        }

        if ($firstCall) {
            $data = $this->nonPersistedData->toArray();
            Esmart_PayPalBrasil_Model_Debug::appendContent('[FRONTEND FORM DATA]', 'createPayment', $data);
        }

        return $this;
    }
}
