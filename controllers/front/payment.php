<?php

require_once '../../library/veritrans.php';

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
    //  'cart' => $cart,
    //  'shipping' => $shipping_cost,
    //  'session_id' => $veritrans->session_id,
    //  'url' => $url,
    //  'merchant_id' => $veritrans->merchant_id,
    //  'merchant_hash' => $veritrans->merchant_hash_key,
    //  'settlement_type' => $veritrans->settlement_type,
    //  'order_id' => $veritrans->order_id,
    //  'gross_ammount' => $veritrans->gross_amount,
    //  'customer_specification_flag' => $veritrans->billing_address_different_with_shipping_address,
    //  'shipping_flag' => $veritrans->required_shipping_address,

    //  'fname' => $veritrans->first_name,
    //  'lname' => $veritrans->last_name,
    //  'add1' => $veritrans->address1,
    //  'add2' => $veritrans->address2,
    //  'city' => $veritrans->city,
    //  'country_code' => $veritrans->country_code,
    //  'post_code' => $veritrans->postal_code,
    //  'phone' => $veritrans->phone,

    //  'shipping_fname' => $veritrans->shipping_first_name,
    //  'shipping_lname' => $veritrans->shipping_last_name,
    //  'shipping_add1' => $veritrans->shipping_address1,
    //  'shipping_add2' => $veritrans->shipping_address2,
    //  'shipping_city' => $veritrans->shipping_city,
    //  'shipping_country_code' => $veritrans->shipping_country_code,
    //  'shipping_post_code' => $veritrans->shipping_postal_code,
    //  'shipping_phone' => $veritrans->shipping_phone,

    //  'token_merchant' => $token_merchant,
    //  'token_browser' => $token_browser,
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

