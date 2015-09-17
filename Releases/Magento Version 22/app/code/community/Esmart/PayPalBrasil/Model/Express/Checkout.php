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
 * @category      Esmart
 * @package       Esmart_PayPalBrasil
 * @copyright     Copyright (c) 2013 Smart E-commerce do Brasil Tecnologia LTDA. (http://www.e-smart.com.br)
 * @license       http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @author        Tiago Sampaio <tiago.sampaio@e-smart.com.br>
 */

/**
 * Class Esmart_PayPalBrasil_Model_Express_Checkout defines checkout type
 */
class Esmart_PayPalBrasil_Model_Express_Checkout extends Mage_Paypal_Model_Express_Checkout
{

    /**
     * Api Model Type
     *
     * @var string
     */
    protected $_apiType = 'paypal/api_nvp';


    /**
     * Try to find whether the code provided by PayPal corresponds to any of possible shipping rates
     * This method was created only because PayPal has issues with returning the selected code.
     * If in future the issue is fixed, we don't need to attempt to match it. It would be enough to set the method code
     * before collecting shipping rates
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param string $selectedCode
     *
     * @return string
     */
    public function matchShippingMethodCode(Mage_Sales_Model_Quote_Address $address, $selectedCode)
    {
        return $this->_matchShippingMethodCode($address, $selectedCode);
    }


    /**
     * Gets the API Model
     *
     * @return Mage_Paypal_Model_Api_Nvp
     */
    public function getApi()
    {
        return $this->_getApi();
    }


    /**
     * Checks if the avoid_review_page config field is enabled
     *
     * @return string|null
     */
    public function isAvoidReviewPageEnabled()
    {
        return $this->_helper()->getExpressConfig('avoid_review_page');
    }


    /**
     * Gets the helper singleton object
     *
     * @return Esmart_PayPalBrasil_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('esmart_paypalbrasil');
    }

}
