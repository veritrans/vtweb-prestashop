<?php 

class VeritransPayDefaultModuleFrontController extends ModuleFrontController
{
  function initContent()
  {
    $this->display_column_left = false;
    $this->display_column_right = false;

    $link = new Link();
    parent::initContent();

    $cart = $this->context->cart;
    if (!$this->module->checkCurrency($cart))
      Tools::redirect('index.php?controller=order');
    
    // $usd = Configuration::get('KURS');
    // $cf = Configuration::get('CONVENIENCE_FEE') * 0.01;
    // $veritrans = new Veritrans();
    // $url = Veritrans::PAYMENT_REDIRECT_URL;

    // $shipping_cost = $cart->getTotalShippingCost();

    // $customer = new Customer($cart->id_customer);
    
    // if (!Validate::isLoadedObject($customer))
    //  Tools::redirect('index.php?controller=order&step=1');

    // $currency = $this->context->currency;
    // $total = $cart->getOrderTotal(true, Cart::BOTH);
    // $mailVars = array(
    //  '{merchant_id}' => Configuration::get('MERCHANT_ID'),
    //  '{merchant_hash}' => nl2br(Configuration::get('MERCHANT_HASH'))
    // );

    // $billing_address = new Address($cart->id_address_invoice);
    // $delivery_address = new Address($cart->id_address_delivery);

    // $veritrans->version = Configuration::get('VERITRANS_API_VERSION');
    // $veritrans->environment = Configuration::get('VERITRANS_ENVIRONMENT');
    // $veritrans->payment_type = Configuration::get('VERITRANS_PAYMENT_TYPE') == 'vtdirect' ? Veritrans::VT_DIRECT : Veritrans::VT_WEB;
    // $veritrans->merchant_id = Configuration::get('VERITRANS_MERCHANT_ID');
    // $veritrans->merchant_hash_key = Configuration::get('VERITRANS_MERCHANT_HASH');
    // $veritrans->client_key = Configuration::get('VERITRANS_CLIENT_KEY');
    // $veritrans->server_key = Configuration::get('VERITRANS_SERVER_KEY');
    // $veritrans->enable_3d_secure = Configuration::get('VERITRANS_3D_SECURE');
    // $veritrans->force_sanitization = true;
    
    // // Billing Address
    // $veritrans->first_name = $billing_address->firstname;
    // $veritrans->last_name = $billing_address->lastname;
    // $veritrans->address1 = $billing_address->address1;
    // $veritrans->address2 = $billing_address->address2;
    // $veritrans->city = $billing_address->city;
    // $veritrans->country_code = $billing_address->id_country;
    // $veritrans->postal_code = $billing_address->postcode;
    // $veritrans->phone = $billing_address->phone_mobile;
    // $veritrans->email = $customer->email;

    
    // if($this->context->cart->isVirtualCart()) {
    //  $veritrans->required_shipping_address = 0;
    //  $veritrans->billing_different_with_shipping = 0;
    // } else {
    //  $veritrans->required_shipping_address = 1;
    //  if ($cart->id_address_delivery != $cart->id_address_invoice)
    //  {
    //    $veritrans->billing_different_with_shipping = 1;
    //    $veritrans->shipping_first_name = $delivery_address->firstname;
    //    $veritrans->shipping_last_name = $delivery_address->lastname;
    //    $veritrans->shipping_address1 = $delivery_address->address1;
    //    $veritrans->shipping_address2 = $delivery_address->address2;
    //    $veritrans->shipping_city = $delivery_address->city;
    //    $veritrans->shipping_country_code = $delivery_address->id_country;
    //    $veritrans->shipping_postal_code = $delivery_address->postcode;
    //    $veritrans->shipping_phone = $delivery_address->phone_mobile;
    //  } else
    //  {
    //    $veritrans->billing_different_with_shipping = 0;
    //  }
    // }  
    
    // $items = $this->addCommodities($cart, $shipping_cost, $usd);
    
    // // convert the currency
    // $cart_currency = new Currency($cart->id_currency);
    // if ($cart_currency->iso_code != 'IDR')
    // {
    //  // check whether if the IDR is installed or not
    //  if (Currency::exists('IDR', null))
    //  {
    //    // use default rate
    //    $conversion_func = function($input) use($cart_currency) { return Tools::convertPriceFull($input, $cart_currency, new Currency(Currency::getIdByIsoCode('IDR'))); };
    //  } else
    //  {
    //    // use rate
    //    $conversion_func = function($input) { return $input * intval(Configuration::get('VERITRANS_KURS')); };
    //  }
    //  foreach ($items as &$item) {
    //    $item['price'] = intval(round(call_user_func($conversion_func, $item['price'])));
    //  }
    // }
    // $veritrans->items = $items;

    // $this->module->validateOrder($cart->id, Configuration::get('VERITRANS_ORDER_STATE_ID'), $cart->getOrderTotal(true, Cart::BOTH), $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
    // $veritrans->order_id = $this->module->currentOrder;  

    // if ($veritrans->version == 1 && $veritrans->payment_type == Veritrans::VT_WEB)
    // {

    //  $keys = $veritrans->getTokens();
      
    //  if ($keys)
    //  { 
    //    $token_browser = $keys['token_browser'];
    //    $token_merchant = $keys['token_merchant'];
    //    $error_message = '';
    //    $this->insertTransaction($cart->id_customer, $cart->id, $currency->id, $veritrans->order_id, $token_merchant);

    //  } else
    //  {
    //    $token_browser = '';
    //    $token_merchant = '';
    //    $error_message = $veritrans->errors;

    //  }
      
      
    // } else
    // {
    //  // handle v1's VTDirect, v2's VTWEB, and v2's VTDIRECT here
    // }
    
    $this->context->smarty->assign(array(
      'payment_type' => Configuration::get('VERITRANS_PAYMENT_TYPE');
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
    //  'error_message' => $error_message,

    //  'nbProducts' => $cart->nbProducts(),
    //  'cust_currency' => $cart->id_currency,
    //  'currencies' => $this->module->getCurrency((int)$cart->id_currency),
    //  'total' => $cart->getOrderTotal(true, Cart::BOTH),
    //  'this_path' => $this->module->getPathUri(),
    //  'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
    ));
    $this->setTemplate('payment_execution.tpl');
    
  }
}