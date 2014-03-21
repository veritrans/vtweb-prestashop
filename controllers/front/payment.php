<?php

session_start();

class VeritransPayPaymentModuleFrontController extends ModuleFrontController
{
	public $ssl = true;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		$this->display_column_left = false;
		$this->display_column_right = false;
		$link = new Link();
		parent::initContent();

		$cart = $this->context->cart;
		if (!$this->module->checkCurrency($cart))
			Tools::redirect('index.php?controller=order');
		
		require_once 'library/veritrans.php';
		$usd = Configuration::get('KURS');
		$cf = Configuration::get('CONVENIENCE_FEE')*0.01;
		$veritrans = new Veritrans();
		$url = 'https://vtweb.veritrans.co.id/web1/paymentStart.action';

		$shipping_cost = number_format($cart->getTotalShippingCost(), 0, '', '');

		$veritrans->merchant_id = Configuration::get('MERCHANT_ID');
		$veritrans->merchant_hash_key = Configuration::get('MERCHANT_HASH');
				
		$customer = new Customer($cart->id_customer);
		
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');

		$currency = $this->context->currency;
		$total = $cart->getOrderTotal(true, Cart::BOTH);
		$mailVars = array(
			'{merchant_id}' => Configuration::get('MERCHANT_ID'),
			'{merchant_hash}' => nl2br(Configuration::get('MERCHANT_HASH'))
		);

		$veritrans->settlement_type = '01';
		$veritrans->order_id = uniqid();
		$veritrans->session_id = session_id();
				
		$veritrans->finish_payment_return_url = $link->getModuleLink('veritranspay', 'validation');
		$veritrans->unfinish_payment_return_url = $link->getPageLink('order');
		$veritrans->error_payment_return_url = $link->getModuleLink('veritranspay', 'error');
		
		$gross_1 = $usd * number_format($cart->getOrderTotal(true, Cart::BOTH), 0, '', '');
		$convenience_fee= number_format($cf * $gross_1, 0, '', '');
		
		if($convenience_fee!=0){
			$veritrans->gross_amount = ($gross_1 + $convenience_fee);
		}else{
			$veritrans->gross_amount = ($gross_1);
		}
		
		$billing_address = new Address($cart->id_address_invoice);
		$delivery_address = new Address($cart->id_address_delivery);

		require_once 'library/isocountry.php';
		$iso_A3 = new ISOCountry;

		// Billing Address
		$veritrans->billing_address_different_with_shipping_address = '1';
		$veritrans->first_name = $billing_address->firstname;
		$veritrans->last_name = $billing_address->lastname;
		$veritrans->address1 = $billing_address->address1;
		$veritrans->address2 = $billing_address->address2;
		$veritrans->city = $billing_address->city;
		$iso_code1 = Country::getIsoById($billing_address->id_country);		
		$veritrans->country_code = $iso_A3->isoA3[$iso_code1];
		$veritrans->postal_code = $billing_address->postcode;
		$veritrans->phone = $billing_address->phone_mobile;
		$veritrans->email = $customer->email;

		if($this->context->cart->isVirtualCart()){
			$veritrans->required_shipping_address = '0';
			$veritrans->billing_address_different_with_shipping_address = '1';
		} else {
			$veritrans->required_shipping_address = '1';
			$veritrans->shipping_first_name = $delivery_address->firstname;
			$veritrans->shipping_last_name = $delivery_address->lastname;
			$veritrans->shipping_address1 = $delivery_address->address1;
			$veritrans->shipping_address2 = $delivery_address->address2;
			$veritrans->shipping_city = $delivery_address->city;
			$iso_code2 = Country::getIsoById($delivery_address->id_country);
			$veritrans->shipping_country_code = $iso_A3->isoA3[$iso_code2];
			$veritrans->shipping_postal_code = $delivery_address->postcode;
			$veritrans->shipping_phone = $delivery_address->phone_mobile;

			if($billing_address == $delivery_address)
			{
				$veritrans->billing_address_different_with_shipping_address = '0';
			}
			
		}
	
		
		$commodities = $this->addCommodities($cart, $shipping_cost, $convenience_fee, $usd);
		$veritrans->commodity = $commodities;

		$keys = $veritrans->get_keys();

		$token_browser = $keys['token_browser'];
		$token_merchant = $keys['token_merchant'];
		$error_message = $keys['error_message'];

		$this->context->smarty->assign(array(
			'cart' => $cart,
			'shipping' => $shipping_cost,
			'session_id' => $veritrans->session_id,
			'url' => $url,
			'merchant_id' => $veritrans->merchant_id,
			'merchant_hash' => $veritrans->merchant_hash_key,
			'settlement_type' => $veritrans->settlement_type,
			'order_id' => $veritrans->order_id,
			'gross_ammount' => $veritrans->gross_amount,
			'customer_specification_flag' => $veritrans->billing_address_different_with_shipping_address,
			'shipping_flag' => $veritrans->required_shipping_address,

			'fname' => $veritrans->first_name,
			'lname' => $veritrans->last_name,
			'add1' => $veritrans->address1,
			'add2' => $veritrans->address2,
			'city' => $veritrans->city,
			'country_code' => $veritrans->country_code,
			'post_code' => $veritrans->postal_code,
			'phone' => $veritrans->phone,

			'shipping_fname' => $veritrans->shipping_first_name,
			'shipping_lname' => $veritrans->shipping_last_name,
			'shipping_add1' => $veritrans->shipping_address1,
			'shipping_add2' => $veritrans->shipping_address2,
			'shipping_city' => $veritrans->shipping_city,
			'shipping_country_code' => $veritrans->shipping_country_code,
			'shipping_post_code' => $veritrans->shipping_postal_code,
			'shipping_phone' => $veritrans->shipping_phone,

			'token_merchant' => $token_merchant,
			'token_browser' => $token_browser,
			'error_message' => $error_message,

			'nbProducts' => $cart->nbProducts(),
			'cust_currency' => $cart->id_currency,
			'currencies' => $this->module->getCurrency((int)$cart->id_currency),
			'total' => $cart->getOrderTotal(true, Cart::BOTH),
			'this_path' => $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
		));

		$this->insertTransaction($cart->id_customer, $cart->id, $currency->id, $veritrans->order_id, $token_merchant);
		$this->setTemplate('payment_execution.tpl');
	}

	public function addCommodities($cart, $shipping_cost, $convenience_fee, $usd)
	{
		
		$products = $cart->getProducts();
		$commodities = array();
		$price=0;
		foreach ($products as $aProduct) {
				
			$commodities[] = array(
				"COMMODITY_ID" => $aProduct['id_product'],
				"COMMODITY_PRICE" =>  number_format($aProduct['price_wt']*$usd, 0, '', ''),
				"COMMODITY_QTY" => $aProduct['cart_quantity'],
				"COMMODITY_NAME1" => $aProduct['name'],
				"COMMODITY_NAME2" => $aProduct['name']
			);
			
		}

		if($shipping_cost != 0){
			$commodities[] = array(
				"COMMODITY_ID" => '00',
				"COMMODITY_PRICE" => $shipping_cost*$usd,
				"COMMODITY_QTY" => '1',
				"COMMODITY_NAME1" => 'Shipping Cost',
				"COMMODITY_NAME2" => 'Biaya Pengiriman'
			);
			
		}

		
		if($convenience_fee!=0){
			$commodities[] = array(
				"COMMODITY_ID" => '00',
				"COMMODITY_PRICE" => $convenience_fee,
				"COMMODITY_QTY" => '1',
				"COMMODITY_NAME1" => 'Convenience Fee',
				"COMMODITY_NAME2" => 'Convenience Fee'
			);
		}
			
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
}
