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
 * @author        Thiago H Oliveira <thiago.oliveira@e-smart.com.br>
 */
class Esmart_PayPalBrasil_Model_Adminhtml_CustomerAttributes
{
    /**
     * Return array with customer attributes
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('esmart_paypalbrasil');

        $attributes = array();

        $resource = Mage::getResourceSingleton('customer/customer');
        $attrArray = Mage::getSingleton('eav/config')->getEntityAttributeCodes('customer', null);

        foreach ($attrArray as $attrCode) {

            $attr = $resource->getAttribute($attrCode);

            if (!$attr->getStoreLabel()) {
                continue;
            }

            $attributes[$attrCode] = array(
                'value' => $attr->getData('attribute_code'),
                'label' => $helper->__($attr->getData('frontend_label'))
            );
        }

        ksort($attributes);

        return $attributes;
    }


    /**
     * Return array with customer attributes
     *
     * @return array
     */
    public function toArray()
    {
        $helper = Mage::helper('esmart_paypalbrasil');

        $attributes = array();

        $resource = Mage::getResourceSingleton('customer/customer');
        $attrArray = Mage::getSingleton('eav/config')->getEntityAttributeCodes('customer', null);

        foreach($attrArray as $attrCode) {

            $attr = $resource->getAttribute($attrCode);

            if(!$attr->getStoreLabel()) {
                continue;
            }
            
            $attributes[] = array(
                $attr->getData('attribute_code') => "[{$helper->__("Customer")}] {$helper->__($attr->getData('frontend_label'))}"
            );
        }

        return $attributes;
    }

}