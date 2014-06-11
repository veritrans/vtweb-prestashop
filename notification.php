<?php 

$useSSL = true;

$root_dir = str_replace('modules/veritranspay', '', dirname($_SERVER['SCRIPT_FILENAME']));

include_once($root_dir.'/config/config.inc.php');
include_once($root_dir.'/modules/veritranspay/veritranspay.php');

function getTransaction($request_id)
{
  $sql = 'SELECT *
      FROM `'._DB_PREFIX_.'vt_transaction`
      WHERE `request_id` = \''.$request_id.'\'';
  $result = Db::getInstance()->getRow($sql);
  return $result; 
}

function validate($id_transaction, $id_order, $order_status)
{
  $sql = 'INSERT INTO `'._DB_PREFIX_.'vt_validation`
      (`id_order`, `id_transaction`, `order_status`)
      VALUES ('.(int)$id_transaction.',
          '.(int)$id_order.',
          \''.$order_status.'\')';
  Db::getInstance()->Execute($sql);
}

$veritransPay = new VeritransPay();
$veritransPay->execNotification();