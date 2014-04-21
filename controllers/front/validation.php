<?php

require_once 'library/veritrans_notification.php';

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

   	$usd = Configuration::get('KURS');
    $cf = Configuration::get('CONVENIENCE_FEE') * 0.01;
    $veritrans = new Veritrans();
    $url = Veritrans::PAYMENT_REDIRECT_URL;

    $shipping_cost = $cart->getTotalShippingCost();

    $currency = $this->context->currency;
    $total = $cart->getOrderTotal(true, Cart::BOTH);
    $mailVars = array(
     '{merchant_id}' => Configuration::get('MERCHANT_ID'),
     '{merchant_hash}' => nl2br(Configuration::get('MERCHANT_HASH'))
    );

    $billing_address = new Address($cart->id_address_invoice);
    $delivery_address = new Address($cart->id_address_delivery);

    $veritrans->version = Configuration::get('VERITRANS_API_VERSION');
    $veritrans->environment = Configuration::get('VERITRANS_ENVIRONMENT');
    $veritrans->payment_type = Configuration::get('VERITRANS_PAYMENT_TYPE') == 'vtdirect' ? Veritrans::VT_DIRECT : Veritrans::VT_WEB;
    $veritrans->merchant_id = Configuration::get('VERITRANS_MERCHANT_ID');
    $veritrans->merchant_hash_key = Configuration::get('VERITRANS_MERCHANT_HASH');
    $veritrans->client_key = Configuration::get('VERITRANS_CLIENT_KEY');
    $veritrans->server_key = Configuration::get('VERITRANS_SERVER_KEY');
    $veritrans->enable_3d_secure = Configuration::get('VERITRANS_3D_SECURE');
    $veritrans->force_sanitization = true;
    
    // Billing Address
    $veritrans->first_name = $billing_address->firstname;
    $veritrans->last_name = $billing_address->lastname;
    $veritrans->address1 = $billing_address->address1;
    $veritrans->address2 = $billing_address->address2;
    $veritrans->city = $billing_address->city;
    $veritrans->country_code = $billing_address->id_country;
    $veritrans->postal_code = $billing_address->postcode;
    $veritrans->phone = $billing_address->phone_mobile;
    $veritrans->email = $customer->email;

    
    if($this->context->cart->isVirtualCart()) {
     $veritrans->required_shipping_address = 0;
     $veritrans->billing_different_with_shipping = 0;
    } else {
     $veritrans->required_shipping_address = 1;
     if ($cart->id_address_delivery != $cart->id_address_invoice)
     {
       $veritrans->billing_different_with_shipping = 1;
       $veritrans->shipping_first_name = $delivery_address->firstname;
       $veritrans->shipping_last_name = $delivery_address->lastname;
       $veritrans->shipping_address1 = $delivery_address->address1;
       $veritrans->shipping_address2 = $delivery_address->address2;
       $veritrans->shipping_city = $delivery_address->city;
       $veritrans->shipping_country_code = $delivery_address->id_country;
       $veritrans->shipping_postal_code = $delivery_address->postcode;
       $veritrans->shipping_phone = $delivery_address->phone_mobile;
     } else
     {
       $veritrans->billing_different_with_shipping = 0;
     }
    }  
    
    $items = $this->addCommodities($cart, $shipping_cost, $usd);
    
    // convert the currency
    $cart_currency = new Currency($cart->id_currency);
    if ($cart_currency->iso_code != 'IDR')
    {
     // check whether if the IDR is installed or not
     if (Currency::exists('IDR', null))
     {
       // use default rate
       $conversion_func = function($input) use($cart_currency) { return Tools::convertPriceFull($input, $cart_currency, new Currency(Currency::getIdByIsoCode('IDR'))); };
     } else
     {
       // use rate
       $conversion_func = function($input) { return $input * intval(Configuration::get('VERITRANS_KURS')); };
     }
     foreach ($items as &$item) {
       $item['price'] = intval(round(call_user_func($conversion_func, $item['price'])));
     }
    }
    $veritrans->items = $items;

    $this->module->validateOrder($cart->id, Configuration::get('VERITRANS_ORDER_STATE_ID'), $cart->getOrderTotal(true, Cart::BOTH), $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
    $veritrans->order_id = $this->module->currentOrder;  

    if ($veritrans->version == 1 && $veritrans->payment_type == Veritrans::VT_WEB)
    {

     $keys = $veritrans->getTokens();
      
     if ($keys)
     { 
       $token_browser = $keys['token_browser'];
       $token_merchant = $keys['token_merchant'];
       $error_message = '';
       $this->insertTransaction($cart->id_customer, $cart->id, $currency->id, $veritrans->order_id, $token_merchant);

     } else
     {
       $token_browser = '';
       $token_merchant = '';
       $error_message = $veritrans->errors;

     }      
      
    } else
    {
     // handle v1's VTDirect, v2's VTWEB, and v2's VTDIRECT here
    }
	}

	// function getTransaction($request_id)
	// {
	// 	$sql = 'SELECT *
	// 			FROM `'._DB_PREFIX_.'vt_transaction`
	// 			WHERE `request_id` = \''.$request_id.'\'';
	// 	$result = Db::getInstance()->getRow($sql);
	// 	return $result;	
	// }

	// function validate($id_transaction, $id_order, $order_status)
 //  	{
 //  		$sql = 'INSERT INTO `'._DB_PREFIX_.'vt_validation`
 //  				(`id_order`, `id_transaction`, `order_status`)
 //  				VALUES ('.(int)$id_transaction.',
 //  						'.(int)$id_order.',
 //  						\''.$order_status.'\')';
	// 	Db::getInstance()->Execute($sql);
 //  	}
}

