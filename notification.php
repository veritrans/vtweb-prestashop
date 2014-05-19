<?php 

$useSSL = true;

$root_dir = str_replace('modules/veritranspay', '', dirname($_SERVER['SCRIPT_FILENAME']));

include_once($root_dir.'/config/config.inc.php');
require_once 'library/lib/veritrans_notification.php';

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

$veritrans_notification = new VeritransNotification();
$transaction = getTransaction($veritrans_notification->orderId);
$order_id = $veritrans_notification->orderId;

$customer = new Customer($transaction['id_customer']); 

$mailVars = array(
  '{merchant_id}' => Configuration::get('VT_MERCHANT_ID'),
  '{merchant_hash}' => nl2br(Configuration::get('VT_MERCHANT_HASH'))
);

/** Validating order*/
if (Configuration::get('VT_API_VERSION') == 2)
{
  if ($veritrans_notification->status == 200)
  {
    $history->changeIdOrderState(Configuration::get('VT_PAYMENT_SUCCESS_STATUS_MAP'), (int)$veritrans_notification->order_id);
  } else if ($veritrans_notification->status == 201)
  {
    $history->changeIdOrderState(Configuration::get('VT_PAYMENT_CHALLENGE_STATUS_MAP'), (int)$veritrans_notification->order_id);
  } else
  {
    $history->changeIdOrderState(Configuration::get('VT_PAYMENT_FAILURE_STATUS_MAP'), (int)$veritrans_notification->order_id);
  }
} else if (Configuration::get('VT_API_VERSION') == 1)
{
  $token_merchant = $transaction['token_merchant'];
  if ($veritrans_notification->status != 'fatal')
  {
    if($token_merchant == $veritrans_notification->TOKEN_MERCHANT)
    {
      $history = new OrderHistory();
      $history->id_order = (int)$veritrans_notification->orderId; 
      if ($veritrans_notification->mStatus == 'success')
      { 
        // $this->module->validateOrder($cart->id, Configuration::get('VT_PAYMENT_SUCCESS_STATUS_MAP'), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);     
        $history->changeIdOrderState(Configuration::get('VT_PAYMENT_SUCCESS_STATUS_MAP'), (int)$veritrans_notification->orderId);
        // $status = "Payment Success";
        // $this->validate($this->module->currentOrder, $veritrans_notification->orderId, $status);
        echo 'validation success';
    
      }
      elseif ($veritrans_notification->mStatus == 'failure')
      {
        // $this->module->validateOrder($cart->id, Configuration::get('VT_PAYMENT_FAILURE_STATUS_MAP'), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
        $history->changeIdOrderState(Configuration::get('VT_PAYMENT_FAILURE_STATUS_MAP'), (int)$veritrans_notification->orderId);
        // $status = "Payment Error";
        // $this->validate($this->module->currentOrder, $veritrans_notification->orderId, $status);
        echo 'validation failed';
      }
      else
      {
        echo 'other<br/>';
      }     
    }
    else
    {
      echo 'no transaction<br/>';
    }
  }
}
exit;