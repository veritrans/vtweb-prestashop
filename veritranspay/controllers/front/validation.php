<?php

class VeritransPayValidationModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
		$cart = $this->context->cart;
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');

		// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'veritranspay')
			{
				$authorized = true;
				break;
			}
		if (!$authorized)
			die($this->module->l('This payment method is not available.', 'validation'));

		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');

		$currency = $this->context->currency;
		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
		$mailVars = array(
			'{merchant_id}' => Configuration::get('MERCHANT_ID'),
			'{merchant_hash}' => nl2br(Configuration::get('MERCHANT_HASH'))
		);

		require_once 'library/veritrans_notification.php';
		$veritrans_notification = new VeritransNotification($_POST);

		// Get the order detail stored by the merchant
		$id_customer = $this->context->cookie->id_customer.'<br/>';
		$recentTransaction = $this->getRecentOrder($id_customer);
		$data = $this->getData($id_customer, $recentTransaction);

		$current_request = $data['request_id'];
		$token_merchant = $data['token_merchant'];		

		/** Validating order*/
		if( ($token_merchant == $veritrans_notification->TOKEN_MERCHANT)
			&& ($current_request == $veritrans_notification->orderId) )
		{
			/** case success ???????*/
			if ($veritrans_notification->mStatus == "success")
			{
				$this->module->validateOrder($cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);			
				$status = "Payment Success";
				$this->validate($this->module->currentOrder, $recentTransaction, $status);
			}
			elseif ($veritrans_notification->mStatus == "failure")
			{
				$this->module->validateOrder($cart->id, Configuration::get('PS_OS_ERROR'), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
				$status = "Payment Error";
				$this->validate($this->module->currentOrder, $recentTransaction, $status);
			}		
		}
	}

	function getRecentOrder($id_customer)
	{
		$sql = 'SELECT MAX(`id_transaction`)
				FROM `'._DB_PREFIX_.'vt_transaction`
				WHERE `id_customer` = '.(int)$id_customer.'';
		$result = Db::getInstance()->getRow($sql);
  		return $result['MAX(`id_transaction`)'];
	}

	function getData($id_customer, $id_transaction)
	{
		$sql = 'SELECT `request_id`, `token_merchant`
				FROM `'._DB_PREFIX_.'vt_transaction`
				WHERE `id_customer` = '.(int)$id_customer.'
				AND `id_transaction` = '.(int)$id_transaction.'';
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
