<?php 

$useSSL = true;

$root_dir = str_replace('modules/veritranspay', '', dirname($_SERVER['SCRIPT_FILENAME']));

include_once($root_dir.'/config/config.inc.php');
require_once 'library/lib/veritrans_notification.php';
require_once 'library/veritrans.php';

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

$customer = new Customer($transaction['id_customer']); 

$mailVars = array(
  '{merchant_id}' => Configuration::get('VT_MERCHANT_ID'),
  '{merchant_hash}' => nl2br(Configuration::get('VT_MERCHANT_HASH'))
);

$history = new OrderHistory();

/** Validating order*/
if (Configuration::get('VT_API_VERSION') == 2)
{
  $history->id_order = (int)$veritrans_notification->order_id;

  // confirm back to Veritrans server
  $veritrans = new Veritrans();
  $veritrans->server_key = Configuration::get('VT_SERVER_KEY');
  $confirmation = $veritrans->confirm($veritrans_notification->order_id);
  
  if ($confirmation)
  {
    if ($confirmation['status_code'] == 200)
    {
      $history->changeIdOrderState(Configuration::get('VT_PAYMENT_SUCCESS_STATUS_MAP'), (int)$confirmation['order_id']);
    } else if ($confirmation['status_code'] == 201)
    {
      $history->changeIdOrderState(Configuration::get('VT_PAYMENT_CHALLENGE_STATUS_MAP'), (int)$confirmation['order_id']);
    } else
    {
      $history->changeIdOrderState(Configuration::get('VT_PAYMENT_FAILURE_STATUS_MAP'), (int)$confirmation['order_id']);
    }
    $history->add(true);
  }

} else if (Configuration::get('VT_API_VERSION') == 1)
{
  $history->id_order = (int)$veritrans_notification->orderId; 
  
  $token_merchant = $transaction['token_merchant'];
  
  if ($veritrans_notification->status != 'fatal')
  {
    if($token_merchant == $veritrans_notification->TOKEN_MERCHANT)
    {
      if ($veritrans_notification->mStatus == 'success')
      { 
        $history->changeIdOrderState(Configuration::get('VT_PAYMENT_SUCCESS_STATUS_MAP'), (int)$veritrans_notification->orderId);
      }
      elseif ($veritrans_notification->mStatus == 'failure')
      {
        $history->changeIdOrderState(Configuration::get('VT_PAYMENT_FAILURE_STATUS_MAP'), (int)$veritrans_notification->orderId);
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