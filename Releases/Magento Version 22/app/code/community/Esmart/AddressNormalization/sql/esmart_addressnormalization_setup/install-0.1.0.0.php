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

/**
 * @var $installer Esmart_AddressNormalization_Model_Resource_Setup
 */

$installer = $this;
$installer->startSetup();

/**
 * Add columns to sales/order_address and sales/quote_address
 */
$tables = array(
	$installer->getTable('sales/quote_address'),
	$installer->getTable('sales/order_address')
);

foreach($tables as $table) {
	if(!$installer->getConnection()->tableColumnExists($table, 'address_number')) {
		$installer->run("ALTER TABLE `{$table}` ADD COLUMN `address_number` varchar(10) COMMENT 'Customer Address Number';");
	}

	if(!$installer->getConnection()->tableColumnExists($table, 'address_neighborhood')) {
		$installer->run("ALTER TABLE `{$table}` ADD COLUMN `address_neighborhood` varchar(255) COMMENT 'Customer Address Neighborhood';");
	}

	if(!$installer->getConnection()->tableColumnExists($table, 'address_complement')) {
		$installer->run("ALTER TABLE `{$table}` ADD COLUMN `address_complement` varchar(255) COMMENT 'Customer Address Neighborhood';");
	}
}

$installer->installEntities();
$installer->installCustomerForms();

$installer->endSetup();
