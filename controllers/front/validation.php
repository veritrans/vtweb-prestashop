<?php

class VeritransPayValidationModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{	
		require_once 'library/veritrans.php';
		require_once 'library/veritrans_notification.php';
		$veritrans_notification = new VeritransNotification();
		
		$transaction = $this->getTransaction($veritrans_notification->orderId);

		$token_merchant = $transaction['token_merchant'];
		$customer = new Customer($transaction['id_customer']); print_r($customer);echo '<br/>';
		$cart = new Cart($transaction['id_cart']); print_r($cart); echo '<br/>';
		$currency = new Currency($transaction['id_currency']); print_r($currency);echo '<br/>';
		$total = (float)$cart->getOrderTotal(true, Cart::BOTH); echo $total;echo '<br/>';

		
		$mailVars = array(
			'{merchant_id}' => Configuration::get('MERCHANT_ID'),
			'{merchant_hash}' => nl2br(Configuration::get('MERCHANT_HASH'))
		);

		
		/** Validating order*/
		if($token_merchant == $veritrans_notification->TOKEN_MERCHANT)
		{
			if ($veritrans_notification->mStatus == 'success')
			{	
				$this->module->validateOrder($cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);			
				$status = "Payment Success";
				$this->validate($this->module->currentOrder, $veritrans_notification->orderId, $status);
		
			}
			elseif ($veritrans_notification->mStatus == 'failure')
			{
				$this->module->validateOrder($cart->id, Configuration::get('PS_OS_ERROR'), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
				$status = "Payment Error";
				$this->validate($this->module->currentOrder, $veritrans_notification->orderId, $status);
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
		exit;
	}

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
}

