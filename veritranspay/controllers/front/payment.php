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
		$veritrans = new Veritrans();
		$url = 'https://payments.veritrans.co.id/web1/paymentStart.action';

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

		// $rand = $this->generateRandStr(10);
		$veritrans->settlement_type = '01';
		$veritrans->order_id = uniqid();
		$veritrans->session_id = session_id();
				
		$veritrans->finish_payment_return_url = $link->getModuleLink('veritranspay', 'success');
		$veritrans->unfinish_payment_return_url = $link->getPageLink('order');
		$veritrans->error_payment_return_url = $link->getModuleLink('veritranspay', 'error');


		$veritrans->gross_amount = number_format($cart->getOrderTotal(true, Cart::BOTH), 0, '', '');
		$billing_address = new Address($cart->id_address_invoice);
		$delivery_address = new Address($cart->id_address_delivery);

		$veritrans->billing_address_different_with_shipping_address = '1';
		$veritrans->required_shipping_address = '0';

		$veritrans->first_name = $delivery_address->firstname;
		$veritrans->last_name = $delivery_address->lastname;
		$veritrans->address1 = $delivery_address->address1;
		$veritrans->address2 = $delivery_address->address2;
		$veritrans->city = $delivery_address->city;

		require_once 'library/isocountry.php';
		$iso_code = Country::getIsoById($delivery_address->id_country);
		$iso_A3 = new ISOCountry;
		$veritrans->country_code = $iso_A3->isoA3[$iso_code];

		$veritrans->postal_code = $delivery_address->postcode;
		$veritrans->phone = $delivery_address->phone_mobile;
		$veritrans->email = $customer->email;
		
		$commodities = $this->addCommodities($cart, $shipping_cost);
		$veritrans->commodity = $commodities;

		$keys = $veritrans->get_keys();

		$token_browser = $keys['token_browser'];
		$token_merchant = $keys['token_merchant'];
		$error_message = $keys['error_message'];


		$this->context->smarty->assign(array(
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
			'shipping_fname' => $veritrans->first_name,
			'shipping_lname' => $veritrans->last_name,
			'shipping_add1' => $veritrans->address1,
			'shipping_add2' => $veritrans->address2,
			'shipping_city' => $veritrans->city,
			'shipping_country_code' => $veritrans->country_code,
			'shipping_post_code' => $veritrans->postal_code,
			'shipping_phone' => $veritrans->phone,

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

		$this->insertTransaction($cart->id_customer, $veritrans->order_id, $token_merchant);
		$this->setTemplate('payment_execution.tpl');
	}

	public function addCommodities($cart, $shipping_cost)
	{
		$products = $cart->getProducts();
		$commodities = array();
		foreach ($products as $aProduct) {
			$commodities[] = array(
				"COMMODITY_ID" => $aProduct['id_product'],
				"COMMODITY_PRICE" => number_format($aProduct['price_wt'], 0, '', ''),
				"COMMODITY_QTY" => $aProduct['cart_quantity'],
				"COMMODITY_NAME1" => $aProduct['name'],
				"COMMODITY_NAME2" => $aProduct['name']
			);
		}
			$commodities[] = array(
				"COMMODITY_ID" => '00',
				"COMMODITY_PRICE" => $shipping_cost,
				"COMMODITY_QTY" => '1',
				"COMMODITY_NAME1" => 'Shipping Cost',
				"COMMODITY_NAME2" => 'Biaya Pengiriman'
			);
		return $commodities;
	}

  	function insertTransaction($customer_id, $request_id, $token_merchant)
  	{
  		$sql = 'INSERT INTO `'._DB_PREFIX_.'vt_transaction`
  				(`id_customer`, `request_id`, `token_merchant`)
  				VALUES ('.(int)$customer_id.',
  						\''.$request_id.'\',
  						\''.$token_merchant.'\')';
		Db::getInstance()->Execute($sql);
  	}
}

