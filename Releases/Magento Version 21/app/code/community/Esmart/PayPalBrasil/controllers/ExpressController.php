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
 * @author      Tiago Sampaio <tiago.sampaio@e-smart.com.br>
 * @author      Thiago H Oliveira <thiago.oliveira@e-smart.com.br>
 */

/**
 * Express Checkout Controller
 */
class Esmart_PayPalBrasil_ExpressController extends Esmart_PayPalBrasil_Controller_Express
{
    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType = 'paypal/config';

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod = Mage_Paypal_Model_Config::METHOD_WPP_EXPRESS;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_checkoutType = 'esmart_paypalbrasil/express_checkout';

    /**
     * Generate URL PaypalPlus
     */
    public function generateUrlAction()
    {
        /** @var Esmart_PayPalBrasil_Model_Plus $model */
        $model = Mage::getModel('esmart_paypalbrasil/plus');

        try {
            $payerInfo   = $model->getCustomerInformation();
            $approvalUrl = $model->getApprovalUrlPaypalPlus();

            $data = array_merge($payerInfo, $approvalUrl);

            foreach ($data as $key => $value) {
                if (is_null($value) && $key !== 'rememberedCards') {
                    throw new Exception('incomplete_customer');
                }
            }

            $return     = array('success' => $data);

        } catch (Exception $exception) {
            $return = array('error' => $exception->getMessage());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($return));
    }

    /**
     * Save Paypal return information
     */
    public function savePaypalInformationAction()
    {
        /** @var Esmart_PayPalBrasil_Model_Plus $model */
        $model = Mage::getModel('esmart_paypalbrasil/plus');

        $postData = $this->getRequest()->getPost();

        $model->saveReturnPaypal($postData);

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array()));
    }
}
