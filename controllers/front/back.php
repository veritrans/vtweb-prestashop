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
		$order_id = Tools::getValue('order_id');
		// if customer press "back" from VTweb, they'll be redirected to re-order link in order to put back their order into shopping cart (normally, their shopping cart is emptied before redirected to VTWeb, so re-order is needed to make sure they have their order back in shopping cart). 
		Tools::redirectLink(__PS_BASE_URI__.'order.php?submitReorder=&id_order='.$order_id);
	}

}


