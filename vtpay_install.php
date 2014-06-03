<?php

if (!defined('_PS_VERSION_'))
	exit;

class VeritransPayInstall
{
	public function createTable()
	{
		/* Set database */
		if (!Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'vt_transaction` (
			`id_transaction` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`id_customer` int(10) NOT NULL,
			`id_cart` int(10) NOT NULL,
			`id_currency` int(10) NOT NULL,
			`request_id` varchar(30) DEFAULT NULL,
			`token_merchant` varchar(50) NOT NULL,
			`transaction_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id_transaction`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1'))
			return false;

		if (!Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'vt_validation` (
			`id_validation` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`id_transaction` int(10) NOT NULL,
			`id_order` int(10) NOT NULL,
			`order_status` varchar(20) DEFAULT NULL,
			`validation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id_validation`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1'))
			return false;
	}

}
