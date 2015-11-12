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
 * @category    Esmart
 * @package     Esmart_PayPalBrasil
 * @copyright   Copyright (c) 2013 Smart E-commerce do Brasil Tecnologia LTDA. (http://www.e-smart.com.br)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @author     Tiago Sampaio <tiago.sampaio@e-smart.com.br>
 * @author     Thiago H Oliveira <thiago.oliveira@e-smart.com.br>
 */

class Esmart_PayPalBrasil_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Base Script content
     * @const
     */
    const JS_BASE = '<script type="text/javascript">jQuery.getScript("%s"); EsmartPaypalBrasilPPPlus.base_url = "%s";</script>';

    /**
     * JS events default
     * @const string
     */
    const JS_EVENTS_DEFAULT = 'esmart/paypalbrasil/Esmart_PaypalBrasil.events.default.js';

    /**
     * JS events MOIP
     * @const string
     */
    const JS_EVENTS_MOIP = 'esmart/paypalbrasil/Esmart_PaypalBrasil.events.moip.js';

    /**
     * @var string
     */
    protected $_ppbUrl = 'https://www.paypal-brasil.com.br';

    /**
     * Returns PayPal Brasil URL
     *
     * @return string
     */
    public function getPPBUrl()
    {
        return $this->_ppbUrl;
    }

    /**
     * Returns PayPal's logo center URL
     *
     * @return string
     */
    public function getLogoCenterUrl()
    {
        return implode('/', array($this->getPPBUrl(), 'logocenter', 'util', 'img'));
    }

    /**
     * Returns the image URL in PayPal Logo Center
     *
     * @param string $imageName
     * @param string $extension
     *
     * @return string
     */
    public function getLogoCenterImageUrl($imageName = null, $extension = null)
    {
        if(!is_null($imageName)) {
            $_imageFullName = is_null($extension) ? $imageName : implode('.', array($imageName, $extension));

            return implode('/', array($this->getLogoCenterUrl(), $_imageFullName));
        }

        return null;
    }

    /**
     * Get General Config
     *
     * @param string $group
     * @param string $field
     *
     * @return string|null
     */
    public function getConfig($group = null, $field = null)
    {
        if(!is_null($group) && !is_null($field)) {
            return Mage::getStoreConfig("payment/{$group}/{$field}");
        }

        return null;
    }

    /**
     * Get PayPal Express Config
     *
     * @param string $field
     *
     * @return string|null
     */
    public function getExpressConfig($field = null)
    {
        if(!is_null($field)) {
            return $this->getConfig('paypal_express', $field);
        }

        return null;
    }

    /**
     * Get PayPal Standard Config
     *
     * @param string $field
     *
     * @return string|null
     */
    public function getStandardConfig($field = null)
    {
        if(!is_null($field)) {
            return $this->getConfig('paypal_standard', $field);
        }

        return null;
    }

    /**
     * Get Quote
     *
     * @param mixed $quote (Mage_Sales_Model_Quote | Quote ID | null)
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote($quote = null)
    {
        if ($quote instanceof Mage_Sales_Model_Quote) {
            return $quote;
        }

        if (is_numeric($quote)) {
            return Mage::getModel('sales/quote')->load($quote);
        }

        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Get profiler name suggestion
     *
     * @return string
     */
    public function getProfilerNameSuggestion()
    {
        return vsprintf(Esmart_PayPalBrasil_Model_Plus::PROFILER_BASE_NAME, array('Store Name', time()));
    }

    /**
     * Get CPF, CNPJ, or TaxVAT
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param Varien_Object $nonPersistedData
     *
     * @return string
     */
    public function getCpfCnpjOrTaxvat(Mage_Customer_Model_Customer $customer, Varien_Object $nonPersistedData)
    {
        $cpf     = Mage::getStoreConfig('payment/paypal_plus/cpf');
        $cpfData = $this->getDataFromObject($customer, $nonPersistedData, $cpf);

        if (!empty($cpfData)) {
            return $cpfData;
        }

        $cnpj     = Mage::getStoreConfig('payment/paypal_plus/cnpj');
        $cnpjData = $this->getDataFromObject($customer, $nonPersistedData, $cnpj);

        if (!empty($cnpjData)) {
            return $cnpjData;
        }

        return $this->getDataFromObject($customer, $nonPersistedData, 'taxvat');
    }

    /**
     * Get events Script
     *
     * @return string
     */
    public function getEventsScriptBlock()
    {
        return sprintf(self::JS_BASE, $this->getCheckoutType(), Mage::getBaseUrl());
    }

    /**
     * Get Checkout Type
     *
     * This method return:
     * - JS PATH
     * OR
     * - true  = OSC is enable
     * - false = checkout default in use
     *
     * @param bool $returnJSEvent
     *
     * @return string|bool
     */
    public function getCheckoutType($returnJSEvent = true)
    {
        $modules      = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array) $modules;

        $oscSolutions = array(
            'MOIP_Onestepcheckout' => self::JS_EVENTS_MOIP,
        );

        foreach ($oscSolutions as $solution => $fileEvent) {
            if(isset($modulesArray[$solution])) {

                /** @var Mage_Core_Model_Config_Element $moduleCfg */
                $moduleCfg = $modulesArray[$solution];
                if ((string) $moduleCfg->active === 'true') {
                    return ($returnJSEvent ? $this->getFullJsUrl($fileEvent) : true);
                }
            }
        }

        return ($returnJSEvent ? $this->getFullJsUrl(self::JS_EVENTS_DEFAULT) : false);
    }

    /**
     * Is OCS
     *
     * return bool
     */
    public function isOneStepCheckout()
    {
        return $this->getCheckoutType(false);
    }

    /**
     * Get FULL url JS
     *
     * @param string $path
     *
     * @return string
     */
    public function getFullJsUrl($path)
    {
        return Mage::getBaseUrl('js') . $path;
    }

    /**
     * Log exception
     *
     * @param string $file
     * @param string $class
     * @param string $method
     * @param int $line
     * @param string $file
     * @param Exception $exception
     * @param array $data
     *
     * @return void
     */
    public function logException($fileOrigin, $class, $method, $line, $file, $exception, $data = array())
    {
        $message    = array();
        $message[]  = '';

        if ($exception) {

            $message[]  = "Exception Message : {$exception->getMessage()}";
            
            if (@$exception->getData()) {
                $data = json_decode($exception->getData());
            }
        }

        $message[]  = "Origin error :";
        $message[]  = " - Filename : {$fileOrigin}";
        $message[]  = " - Class : {$class}";
        $message[]  = " - Method : {$method}";
        $message[]  = " - Line : {$line}";

        if ($data) {
            $message[] = "Details :";
        }

        foreach ($data as $key => $value) {
            $key   = ucwords(str_replace('_', ' ', $key));
            $value = (is_array($value) ? json_encode($value) : $value);
            $message[] = " - {$key} : {$value}";
        }

        $message[] = PHP_EOL . PHP_EOL;

        Mage::log(implode(PHP_EOL, $message), Zend_Log::ERR, $file, true);
    }

    /**
     * Check is CPF or CNPJ
     *
     * @return string
     */
    public function checkIsCpfOrCnpj($value)
    {
        $value = preg_replace('/\D/', '', trim($value));

        if (preg_match('/^\d{11}$/', $value)) {
            return Esmart_PayPalBrasil_Model_Plus::PAYER_TAX_ID_TYPE_CPF;
        }

        return Esmart_PayPalBrasil_Model_Plus::PAYER_TAX_ID_TYPE_CNPJ;
    }

    /**
     * Get Data From Object
     *
     * @var mixed $object
     * @var Varien_Object $nonPersistedData
     * @var mixed $index
     *
     * @return mixed
     */
    public function getDataFromObject($object, $nonPersistedData, $index)
    {
        if ($object->getData($index)) {
            return $object->getData($index);
        }

        // assume this element is address
        if ($object instanceof Mage_Customer_Model_Address && is_numeric($index)) {
            $object = new Varien_Object(explode(PHP_EOL, $object->getStreet()));
            return $this->getDataFromObject($object, $nonPersistedData, $index);
        }

        return $nonPersistedData->getData($index);
    }
}
