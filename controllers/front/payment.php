<?php

require_once(dirname(__FILE__).'/../../library/veritrans/Veritrans.php');

class VeritransPayPaymentModuleFrontController extends ModuleFrontController
{
  public $ssl = true;
  public $display_column_left = false;

  /**
   * @see FrontController::initContent()
   */

  public function initContent()
  {
    $this->display_column_left = false;
    
    $link = new Link();
    parent::initContent();

    $cart = $this->context->cart;
    if (!$this->module->checkCurrency($cart))
      Tools::redirect('index.php?controller=order');
    
    $this->context->smarty->assign(array(
      'payment_type' => Configuration::get('VT_PAYMENT_TYPE'),
      'api_version' => Configuration::get('VT_API_VERSION'),
      'error_message' => '',
      'nbProducts' => $cart->nbProducts(),
      'cust_currency' => $cart->id_currency,
      'currencies' => $this->module->getCurrency((int)$cart->id_currency),
      'total' => $cart->getOrderTotal(true, Cart::BOTH),
      'this_path' => $this->module->getPathUri(),
      'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
    ));
    $this->setTemplate('payment_execution.tpl');
    
  }  
}

