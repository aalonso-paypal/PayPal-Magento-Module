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
 * @package     Esmart_AddressNormalization
 * @copyright   Copyright (c) 2013 Smart E-commerce do Brasil Tecnologia LTDA. (http://www.e-smart.com.br)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @author     	Tiago Sampaio <tiago.sampaio@e-smart.com.br>
 */

class Esmart_AddressNormalization_Model_Resource_Setup extends Mage_Customer_Model_Resource_Setup
{

	/**
	 * Delete all updatable attributes from customer_form_attribute table
	 *
	 * @return void
	 */
	protected function _resetAttributesInForms()
	{
        $entities = $this->getDefaultEntities();

        if (!isset($entities['customer_address']['attributes'])) {
            return;
        }

		$customerAddressAttributes		= $entities['customer_address']['attributes'];

		$attributesCodes = array();

		foreach($customerAddressAttributes as $key => $data) {
			$attributesCodes[] = $key;
		}

		/**
		 * Retrieve Entity Type Ids
		 */
		$entityTypeIds = array();
		$select = $this->getConnection()->select();
		$select->from(array('et' => $this->getTable('eav/entity_type')), array('entity_type_id'))
			   ->where('et.entity_type_code IN (?)', array('customer_address'));

		foreach($this->getConnection()->fetchAll($select) as $row) {
			$entityTypeIds[] = $row['entity_type_id'];
		}

		/**
		 * Retrieve Attribute Codes
		 */
		$attributeIds  = array();
		$select = $this->getConnection()->select();
		$select->from(array('ea' => $this->getTable('eav/attribute')), array('attribute_id'))
			   ->where('ea.attribute_code IN(?)', $attributesCodes)
			   ->where('ea.entity_type_id IN(?)', $entityTypeIds);

		foreach ($this->getConnection()->fetchAll($select) as $row) {
			$attributeIds[] = $row['attribute_id'];
		}

		$where = array('attribute_id IN(?)' => $attributeIds);
		$this->getConnection()->delete($this->getTable('customer/form_attribute'), $where);
	}


	/**
	 * Add customer attributes to customer forms
	 *
	 * @return void
	 */
	public function installCustomerForms()
	{
		$this->_resetAttributesInForms();
		parent::installCustomerForms();
	}


	/**
	 * Retreive default entities: customer, customer_address
	 *
	 * @return array
	 */
	public function getDefaultEntities()
	{
		$entities = array(
			'customer_address'               => array(
				'entity_model'                   => 'customer/address',
				'attribute_model'                => 'customer/attribute',
				'table'                          => 'customer/address_entity',
				'additional_attribute_table'     => 'customer/eav_attribute',
				'entity_attribute_collection'    => 'customer/address_attribute_collection',
				'attributes'                     => array(
					'address_number' 		=> array(
						'type' 					=> 'varchar',
						'label' 				=> 'Número',
						'input'					=> 'text',
						'sort_order'			=> 140,
						'required'				=> false,
						'position'				=> 140
					),
					'address_neighborhood' 	=> array(
						'type' 					=> 'varchar',
						'label' 				=> 'Bairro',
						'input'					=> 'text',
						'sort_order'			=> 150,
						'required'				=> false,
						'position'				=> 150
					),
					'address_complement' 	=> array(
						'type' 					=> 'varchar',
						'label' 				=> 'Complemento',
						'input'					=> 'text',
						'sort_order'			=> 160,
						'required'				=> false,
						'position'				=> 160
					),
					'telephone'          => array(
						'type'               	=> 'varchar',
						'label'              	=> 'Telephone',
						'input'              	=> 'text',
						'sort_order'         	=> 120,
						'validate_rules'     	=> 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
						'position'           	=> 120,
						'required'				=> false,
					),
				)
			)
		);
		return $entities;
	}


	/**
	 * @return Esmart_AddressNormalization_Helper_Data
	 */
	protected function _helper()
	{
		return Mage::helper('esmart_addressnormalization');
	}

}
