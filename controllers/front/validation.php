<?php

class VeritransPayValidationModuleFrontController extends ModuleFrontController
{
  public $display_header = false;
  public $display_footer = false;
  public $display_column_left = false;
  public $display_column_right = false;

  /**
   * @see FrontController::postProcess()
   */
  public function postProcess()
  { 
    $cart = $this->context->cart;
    $veritranspay = new VeritransPay();
    $keys = $veritranspay->execValidation($cart);

    // if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
    //  Tools::redirect('index.php?controller=order&step=1');

    // // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
    // $authorized = false;
    // foreach (Module::getPaymentModules() as $module)
    //  if ($module['name'] == 'veritranspay')
    //  {
    //    $authorized = true;
    //    break;
    //  }
    // if (!$authorized)
    //  die($this->module->l('This payment method is not available.', 'validation'));

    // $customer = new Customer($cart->id_customer);
  //   if (!Validate::isLoadedObject($customer))
  //    Tools::redirect('index.php?controller=order&step=1');

  //    $usd = Configuration::get('VT_KURS');
  //   $cf = Configuration::get('VT_CONVENIENCE_FEE') * 0.01;
  //   $veritrans = new Veritrans();
  //   $url = Veritrans_Config::PAYMENT_REDIRECT_URL;

  //   $shipping_cost = $cart->getTotalShippingCost();

  //   $currency = $this->context->currency;
  //   $total = $cart->getOrderTotal(true, Cart::BOTH);
  //   $mailVars = array(
  //    '{merchant_id}' => Configuration::get('MERCHANT_ID'),
  //    '{merchant_hash}' => nl2br(Configuration::get('MERCHANT_HASH'))
  //   );

  //   $billing_address = new Address($cart->id_address_invoice);
  //   $delivery_address = new Address($cart->id_address_delivery);

  //   $veritrans->version = Configuration::get('VT_API_VERSION');
  //   $veritrans->environment = Configuration::get('VT_ENVIRONMENT');
  //   $veritrans->payment_type = Configuration::get('VT_PAYMENT_TYPE') == 'vtdirect' ? Veritrans_Config::VT_DIRECT : Veritrans_Config::VT_WEB;
  //   $veritrans->merchant_id = Configuration::get('VT_MERCHANT_ID');
  //   $veritrans->merchant_hash_key = Configuration::get('VT_MERCHANT_HASH');
  //   $veritrans->client_key = Configuration::get('VT_CLIENT_KEY');
  //   $veritrans->server_key = Configuration::get('VT_SERVER_KEY');
  //   $veritrans->enable_3d_secure = Configuration::get('VT_3D_SECURE');
  //   $veritrans->force_sanitization = true;
    
  //   // Billing Address
  //   $veritrans->first_name = $billing_address->firstname;
  //   $veritrans->last_name = $billing_address->lastname;
  //   $veritrans->address1 = $billing_address->address1;
  //   $veritrans->address2 = $billing_address->address2;
  //   $veritrans->city = $billing_address->city;
  //   $veritrans->country_code = $billing_address->id_country;
  //   $veritrans->postal_code = $billing_address->postcode;
  //   $veritrans->phone = $billing_address->phone_mobile;
  //   $veritrans->email = $customer->email;

    
  //   if($this->context->cart->isVirtualCart()) {
  //    $veritrans->required_shipping_address = 0;
  //    $veritrans->billing_different_with_shipping = 0;
  //   } else {
  //    $veritrans->required_shipping_address = 1;
  //    if ($cart->id_address_delivery != $cart->id_address_invoice)
  //    {
  //      $veritrans->billing_different_with_shipping = 1;
  //      $veritrans->shipping_first_name = $delivery_address->firstname;
  //      $veritrans->shipping_last_name = $delivery_address->lastname;
  //      $veritrans->shipping_address1 = $delivery_address->address1;
  //      $veritrans->shipping_address2 = $delivery_address->address2;
  //      $veritrans->shipping_city = $delivery_address->city;
  //      $veritrans->shipping_country_code = $delivery_address->id_country;
  //      $veritrans->shipping_postal_code = $delivery_address->postcode;
  //      $veritrans->shipping_phone = $delivery_address->phone_mobile;
  //    } else
  //    {
  //      $veritrans->billing_different_with_shipping = 0;
  //    }
  //   }  
    
  //   $items = $this->addCommodities($cart, $shipping_cost, $usd);
    
  //   // convert the currency
  //   $cart_currency = new Currency($cart->id_currency);
  //   if ($cart_currency->iso_code != 'IDR')
  //   {
  //    // check whether if the IDR is installed or not
  //    if (Currency::exists('IDR', null))
  //    {
  //      // use default rate
  //      $conversion_func = function($input) use($cart_currency) { return Tools::convertPriceFull($input, $cart_currency, new Currency(Currency::getIdByIsoCode('IDR'))); };
  //    } else
  //    {
  //      // use rate
  //      $conversion_func = function($input) { return $input * intval(Configuration::get('VT_KURS')); };
  //    }
  //    foreach ($items as &$item) {
  //      $item['price'] = intval(round(call_user_func($conversion_func, $item['price'])));
  //    }
  //   }
  //   $veritrans->items = $items;

  //   $this->module->validateOrder($cart->id, Configuration::get('VT_ORDER_STATE_ID'), $cart->getOrderTotal(true, Cart::BOTH), $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
  //   $veritrans->order_id = $this->module->currentOrder;  

  $veritrans_api_version = Configuration::get('VT_API_VERSION');
  $veritrans_payment_method = Configuration::get('VT_PAYMENT_TYPE');

  if ($keys['errors'])
  {
    var_dump($keys['errors']);
    exit;
  }
  if ($veritrans_api_version == 2 && $veritrans_payment_method == 'vtweb')
  {
      if ($keys['isWarning']){          

          Tools::redirectLink('index.php?fc=module&module=veritranspay&controller=warning&redirlink='.$keys['redirect_url'].'&message='.$keys['message']);
      }      
      Tools::redirectLink($keys['redirect_url']);
  } else if ($veritrans_api_version == 2 && $veritrans_payment_method == 'vtdirect')
  {

  }
  }

  public function setMedia()
  {
    Tools::addJs('function onloadEvent() { document.form_auto_post.submit(); }');
  }

  public function addCommodities($cart, $shipping_cost, $usd)
  {
    
    $products = $cart->getProducts();
    $commodities = array();
    $price = 0;

    foreach ($products as $aProduct) {
      $commodities[] = array(
        "item_id" => $aProduct['id_product'],
        // "price" =>  number_format($aProduct['price_wt']*$usd, 0, '', ''),
        "price" =>  $aProduct['price_wt'],
        "quantity" => $aProduct['cart_quantity'],
        "item_name1" => $aProduct['name'],
        "item_name2" => $aProduct['name']
      );
    }

    if($shipping_cost != 0){
      $commodities[] = array(
        "item_id" => 'SHIPPING_FEE',
        // "COMMODITY_PRICE" => $shipping_cost*$usd,
        "price" => $shipping_cost, // defer currency conversion until the very last time
        "quantity" => '1',
        "item_name1" => 'Shipping Cost',
        "item_name2" => 'Biaya Pengiriman'
      );      
    }
    
    // convenience fee is disabled for the time being...
    // if($convenience_fee!=0){
    //  $commodities[] = array(
    //    "COMMODITY_ID" => '00',
    //    "COMMODITY_PRICE" => $convenience_fee,
    //    "COMMODITY_QTY" => '1',
    //    "COMMODITY_NAME1" => 'Convenience Fee',
    //    "COMMODITY_NAME2" => 'Convenience Fee'
    //  );
    // }
      
    return $commodities;
  }

  function insertTransaction($customer_id, $id_cart, $id_currency, $request_id, $token_merchant)
  {
    $sql = 'INSERT INTO `'._DB_PREFIX_.'vt_transaction`
        (`id_customer`, `id_cart`, `id_currency`, `request_id`, `token_merchant`)
        VALUES ('.(int)$customer_id.',
          '.(int)$id_cart.',
          '.(int)$id_currency.',
            \''.$request_id.'\',
            \''.$token_merchant.'\')';
    Db::getInstance()->Execute($sql);
  }

  // function getTransaction($request_id)
  // {
  //  $sql = 'SELECT *
  //      FROM `'._DB_PREFIX_.'vt_transaction`
  //      WHERE `request_id` = \''.$request_id.'\'';
  //  $result = Db::getInstance()->getRow($sql);
  //  return $result; 
  // }

  // function validate($id_transaction, $id_order, $order_status)
 //   {
 //     $sql = 'INSERT INTO `'._DB_PREFIX_.'vt_validation`
 //         (`id_order`, `id_transaction`, `order_status`)
 //         VALUES ('.(int)$id_transaction.',
 //             '.(int)$id_order.',
 //             \''.$order_status.'\')';
  //  Db::getInstance()->Execute($sql);
 //   }
}

