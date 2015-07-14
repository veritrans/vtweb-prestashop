<?php

session_start();

class VeritransPayFailureModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
	
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		$this->display_column_left = false;
		$this->display_column_right = false;
		parent::initContent();
		global $smarty;

		if (null !==Tools::getValue('order_id') && '' !==Tools::getValue('order_id') ){
			$recheckout = $smarty->tpl_vars['base_dir']->value."en/order?submitReorder=&id_order=".Tools::getValue('order_id');
		}
		$cart = $this->context->cart;
		$status = 'failure';

		$this->context->smarty->assign(array(
			'status' => $status,
			'recheckout' => $recheckout,
			'this_path' => $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
		));

		$this->setTemplate('notification.tpl');
	}

}


		