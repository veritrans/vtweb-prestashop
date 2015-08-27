<?php

session_start();

class VeritransPayBackModuleFrontController extends ModuleFrontController
{
	public $ssl = true;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		// if customer press "back" from VTweb, they'll be redirected to re-order link in order to put back their order into shopping cart (normally, their shopping cart is emptied before redirected to VTWeb, so re-order is needed to make sure they have their order back in shopping cart). 
		
		$this->display_column_left = false;
		$this->display_column_right = false;
		parent::initContent();
		global $smarty;

		if (null !==Tools::getValue('order_id') && '' !==Tools::getValue('order_id') ){
			$order_id = Tools::getValue('order_id');
		}

		// set order status in backend to be failure
		$history = new OrderHistory();
		$history->id_order = $order_id;
		$history->changeIdOrderState(Configuration::get('VT_PAYMENT_FAILURE_STATUS_MAP'), $order_id);
		$history->add(true);
		//
		
		$cart = $this->context->cart;
		$status = 'back';

		$this->context->smarty->assign(array(
			'status' => $status,
			'order_id' => $order_id,
			'this_path' => $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
		));

		$this->setTemplate('notification.tpl');
	}

}


