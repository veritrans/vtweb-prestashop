<?php

if (!defined('_PS_VERSION_'))
	exit;

class VeritransPay extends PaymentModule
{
	private $_html = '';
	private $_postErrors = array();

	public $veritrans_merchant_id;
	public $veritrans_merchant_hash;
	public $veritrans_kurs;
	public $veritrans_convenience_fee;
	public $veritrans_client_key;
	public $veritrans_server_key;
	public $veritrans_api_version;
	public $veritrans_installments;
	public $veritrans_3d_secure;
	public $veritrans_payment_type;
	public $veritrans_payment_success_status_mapping;
	public $veritrans_payment_failure_status_mapping;
	public $veritrans_payment_challenge_status_mapping;
	public $veritrans_environment;

	public $config_keys;

	public function __construct()
	{
		$this->name = 'veritranspay';
		$this->tab = 'payments_gateways';
		$this->version = '0.6';
		$this->author = 'Veritrans';
		$this->bootstrap = true;
		
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';
		$this->veritrans_convenience_fee = 0;

		// key length must be between 0-32 chars to maintain compatibility with <= 1.5
		$this->config_keys = array(
			'VT_MERCHANT_ID', 
			'VT_MERCHANT_HASH',
			'VT_CLIENT_KEY',
			'VT_SERVER_KEY',
			'VT_API_VERSION',
			'VT_PAYMENT_TYPE',
			'VT_3D_SECURE',
			'VT_KURS',
			'VT_CONVENIENCE_FEE',
			'VT_PAYMENT_SUCCESS_STATUS_MAP',
			'VT_PAYMENT_FAILURE_STATUS_MAP',
			'VT_PAYMENT_CHALLENGE_STATUS_MAP',
			'VT_ENVIRONMENT'
			);

		foreach (array('BNI', 'MANDIRI', 'CIMB') as $bank) {
			foreach (array(3, 6, 9, 12, 18, 24) as $months) {
				array_push($this->config_keys, 'VT_INSTALLMENTS_' . $bank . '_' . $months);
			}
		}

		$config = Configuration::getMultiple($this->config_keys);

		foreach ($this->config_keys as $key) {
			if (isset($config[$key]))
				$this->{strtolower($key)} = $config[$key];
		}
		
		// if (isset($config['VT_MERCHANT_HASH']))
		// 	$this->veritrans_merchant_hash = $config['VT_MERCHANT_HASH'];
		// if (isset($config['VT_KURS']))
		// 	$this->veritrans_kurs = $config['VT_KURS'];
		// else Configuration::set('VT_KURS',1);
		// if (isset($config['VT_CONVENIENCE_FEE']))
		// 	$this->veritrans_convenience_fee = $config['VT_CONVENIENCE_FEE'];
		// else Configuration::set('VT_CONVENIENCE_FEE',0);
		
		parent::__construct();

		$this->displayName = $this->l('Veritrans Pay');
		$this->description = $this->l('Accept payments for your products via Veritrans.');
		$this->confirmUninstall = $this->l('Are you sure about uninstalling Veritrans pay?');
		
		if (!isset($this->veritrans_merchant_id) || !isset($this->veritrans_merchant_hash))
			$this->warning = $this->l('Merchant ID and Merchant Hash must be configured before using this module.');
		
		if (!count(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency has been set for this module.');

		$this->extra_mail_vars = array(
			'{veritrans_merchant_id}' => Configuration::get('VT_MERCHANT_ID'),
			'{veritrans_merchant_hash}' => nl2br(Configuration::get('VT_MERCHANT_HASH'))
			);

		// Retrocompatibility
		$this->initContext();
	}

	public function install()
	{
		// create a new order state for Veritrans, since Prestashop won't assign order ID unless it is validated,
		// and no default order states matches the state we want. Assigning order_id with uniqid() will confuse
		// users in the future
		$order_state = new OrderStateCore();
		$order_state->name = array((int)Configuration::get('PS_LANG_DEFAULT') => 'Awaiting Veritrans payment');;
		$order_state->module_name = 'veritranspay';
		$order_state->unremovable = false;
		$order_state->add();

		Configuration::updateValue('VT_ORDER_STATE_ID', $order_state->id);

		if (!parent::install() || 
			!$this->registerHook('payment') ||
			!$this->registerHook('header') ||
			!$this->registerHook('backOfficeHeader'))
			return false;
		
		include_once(_PS_MODULE_DIR_ . '/' . $this->name . '/vtpay_install.php');
		$vtpay_install = new VeritransPayInstall();
		$vtpay_install->createTable();

		return true;
	}

	public function uninstall()
	{
		$status = true;

		$veritrans_payment_waiting_order_state_id = Configuration::get('VT_ORDER_STATE_ID');
		if ($veritrans_payment_waiting_order_state_id)
		{
			$order_state = new OrderStateCore($veritrans_payment_waiting_order_state_id);
			$order_state->delete();
		}
		
		// foreach ($this->config_keys as $key) {
		// 	if (!Configuration::deleteByName($key))
		// 		$status = false;
		// }
		if (!parent::uninstall())
			$status = false;
		return $status;
	}

	private function _postValidation()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			if (!Tools::getValue('VT_MERCHANT_HASH'))
				$this->_postErrors[] = $this->l('Merchant Hash are required.');
			else if (!Tools::getValue('VT_MERCHANT_ID'))
				$this->_postErrors[] = $this->l('Merchant ID is required.');
		}
	}

	private function _postProcess()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			foreach ($this->config_keys as $key) {
				Configuration::updateValue($key, Tools::getValue($key));
			}	
		}
		$this->_html .= '<div class="alert alert-success conf confirm"> '.$this->l('Settings updated').'</div>';
	}

	private function _displayVeritransPay()
	{
		$this->_html .= $this->display(__FILE__, 'infos.tpl');
	}

	private function _displayVeritransPayOld()
	{
		$this->_html .= '<img src="../modules/veritranspay/veritrans.jpg" style="float:left; margin-right:15px;"><b>'.$this->l('This module allows payment via veritrans.').'</b><br/><br/>
		'.$this->l('Payment via veritrans.').'<br /><br /><br />';
	}

	private function _displayForm()
	{
		if (version_compare(Configuration::get('PS_INSTALL_VERSION'), '1.5') == -1) {
			// retrocompatibility with Prestashop 1.4
			$this->_displayFormOld();
		} else
		{
			$this->_displayFormNew();
		}
		
	}

	public function getConfigFieldsValues()
	{
		$result = array();
		foreach ($this->config_keys as $key) {
			$result[$key] = Tools::getValue($key, Configuration::get($key));
		}
		return $result;
	}

	private function _displayFormNew()
	{
		$installments_options = array();
		foreach (array('BNI', 'MANDIRI', 'CIMB') as $bank) {
			$installments_options[$bank] = array();
			foreach (array(3, 6, 9, 12, 18, 24) as $months) {
				array_push($installments_options[$bank], array(
					'id_option' => $bank . '_' . $months,
					'name' => $months . ' Months'
					));
			}
		}

		$order_states = array();
		foreach (OrderState::getOrderStates($this->context->language->id) as $state) {
			array_push($order_states, array(
				'id_option' => $state['id_order_state'],
				'name' => $state['name']
				)
			);
		}

		$environments = array(
			array(
				'id_option' => 'development',
				'name' => 'Development'
				),
			array(
				'id_option' => 'production',
				'name' => 'Production'
				)
			);

		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => 'Basic Information',
					'icon' => 'icon-cogs'
					),
				'input' => array(
					array(
						'type' => 'select',
						'label' => 'API Version',
						'required' => true,
						'name' => 'VT_API_VERSION',
						'is_bool' => false,
						'options' => array(
							'query' => array(
								array(
									'id_option' => 1,
									'name' => 'v1'
									),
								array(
									'id_option' => 2,
									'name' => 'v2'
									)
								),
							'id' => 'id_option',
							'name' => 'name'
							),
						'id' => 'veritransApiVersion'
						),
					array(
						'type' => 'select',
						'label' => 'Environment',
						'name' => 'VT_ENVIRONMENT',
						'required' => true,
						'options' => array(
							'query' => $environments,
							'id' => 'id_option',
							'name' => 'name'
							),
						'class' => 'v2_settings'
						),
					array(
						'type' => 'text',
						'label' => 'Merchant ID',
						'name' => 'VT_MERCHANT_ID',
						'required' => true,
						'desc' => 'Consult to your Merchant Administration Portal for the value of this field.',
						'class' => 'v1_settings vtweb_settings'
						),
					array(
						'type' => 'text',
						'label' => 'Merchant Hash Key',
						'name' => 'VT_MERCHANT_HASH',
						'required' => true,
						'desc' => 'Consult to your Merchant Administration Portal for the value of this field.',
						'class' => 'v1_settings vtweb_settings'
						),
					array(
						'type' => 'text',
						'label' => 'VT-Direct Client Key',
						'name' => 'VT_CLIENT_KEY',
						'required' => true,
						'desc' => 'Consult to your Merchant Administration Portal for the value of this field.',
						'class' => 'vtdirect_settings'
						),
					array(
						'type' => 'text',
						'label' => 'VT-Direct Server Key',
						'name' => 'VT_SERVER_KEY',
						'required' => true,
						'desc' => 'Consult to your Merchant Administration Portal for the value of this field.',
						'class' => 'vtdirect_settings'
						),
					array(
						'type' => 'select',
						'label' => 'Payment Type',
						'name' => 'VT_PAYMENT_TYPE',
						'required' => true,
						'is_bool' => false,
						'options' => array(
							'query' => array(
								array(
									'id_option' => 'vtweb',
									'name' => 'VT-Web'
									),
								array(
									'id_option' => 'vtdirect',
									'name' => 'VT-Direct'
									)
								),
							'id' => 'id_option',
							'name' => 'name'
							),
						'id' => 'veritransPaymentType'
						),
					array(
						'type' => 'checkbox',
						'label' => 'Enable BNI Installments?',
						'name' => 'VT_INSTALLMENTS',
						'values' => array(
							'query' => $installments_options['BNI'],
							'id' => 'id_option',
							'name' => 'name'
							),
						'class' => 'vtweb_settings'
						),
					array(
						'type' => 'checkbox',
						'label' => 'Enable Mandiri Installments?',
						'name' => 'VT_INSTALLMENTS',
						'values' => array(
							'query' => $installments_options['MANDIRI'],
							'id' => 'id_option',
							'name' => 'name'
							),
						'class' => 'vtweb_settings'
						),
					array(
						'type' => 'checkbox',
						'label' => 'Enable CIMB Installments?',
						'name' => 'VT_INSTALLMENTS',
						'values' => array(
							'query' => $installments_options['CIMB'],
							'id' => 'id_option',
							'name' => 'name'
							),
						'class' => 'vtweb_settings'
						),
					array(
						'type' => 'radio',
						'label' => 'Enable 3D Secure?',
						'name' => 'VT_3D_SECURE',
						'required' => true,
						'is_bool' => true,
						'values' => array(
							array(
								'id' => '3d_secure_yes',
								'value' => 1,
								'label' => 'Yes'
								),
							array(
								'id' => '3d_secure_no',
								'value' => 0,
								'label' => 'No'
								)
							),
						'class' => 't'
						),
					array(
						'type' => 'select',
						'label' => 'Map payment SUCCESS status to:',
						'name' => 'VT_PAYMENT_SUCCESS_STATUS_MAP',
						'required' => true,	
						'options' => array(
							'query' => $order_states,
							'id' => 'id_option',
							'name' => 'name'
							),
						'class' => 'vtweb_settings'
						),
					array(
						'type' => 'select',
						'label' => 'Map payment FAILURE status to:',
						'name' => 'VT_PAYMENT_FAILURE_STATUS_MAP',
						'required' => true,
						'options' => array(
							'query' => $order_states,
							'id' => 'id_option',
							'name' => 'name'
							),
						'class' => 'vtweb_settings'
						),
					array(
						'type' => 'select',
						'label' => 'Map payment CHALLENGE status to:',
						'name' => 'VT_PAYMENT_CHALLENGE_STATUS_MAP',
						'required' => true,
						'options' => array(
							'query' => $order_states,
							'id' => 'id_option',
							'name' => 'name'
							),
						'class' => 'vtweb_settings'
						),
					array(
						'type' => 'text',
						'label' => 'IDR Conversion Rate',
						'name' => 'VT_KURS',
						'desc' => 'Veritrans will use this rate to convert prices to IDR when there are no default conversion system.'
						),
					),
				'submit' => array(
					'title' => $this->l('Save'),
					)
				)
			);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table =  $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->id = (int)Tools::getValue('id_carrier');
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'btnSubmit';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		$this->_html .= $helper->generateForm(array($fields_form));
	}

	private function _displayFormOld()
	{
		$this->context->smarty->assign(array(
			'form_url' => Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']),
			'merchant_id' => htmlentities(Tools::getValue('VT_MERCHANT_ID', $this->veritrans_merchant_id), ENT_COMPAT, 'UTF-8'),
			'merchant_hash_key' => htmlentities(Tools::getValue('VT_MERCHANT_HASH', $this->veritrans_merchant_hash), ENT_COMPAT, 'UTF-8'),
			'kurs' => htmlentities(Tools::getValue('VT_KURS', $this->veritrans_kurs), ENT_COMPAT, 'UTF-8'),
			'convenience_fee' => htmlentities(Tools::getValue('VT_CONVENIENCE_FEE', $this->veritrans_convenience_fee), ENT_COMPAT, 'UTF-8'),
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
			));
		$output = $this->context->smarty->fetch(__DIR__ . '/views/templates/hook/admin_retro.tpl');
		$this->_html .= $output;
	}

	public function getContent()
	{
		// $this->_html = '<h2>'.$this->displayName.'</h2>';

		if (Tools::isSubmit('btnSubmit'))
		{
			$this->_postValidation();
			if (!count($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors as $err)
					$this->_html .= '<div class="alert alert-danger error">'. $err . '</div>';
		}
		else
			$this->_html .= '<br />';

		$this->_displayVeritransPay();
		$this->_displayForm();

		return $this->_html;
	}

	public function hookDisplayHeader($params)
	{
		// $this->context->controller->addJS($this->_path . 'js/veritrans_admin.js', 'all');
		// exit;
		// is this supposed to work in 1.4?
	}

	// working in 1.5 and 1.6
	public function hookDisplayBackOfficeHeader($params)
	{
		$this->context->controller->addJS($this->_path . 'js/veritrans_admin.js', 'all');
	}

	public function hookPayment($params)
	{
		if (version_compare(Configuration::get('PS_INSTALL_VERSION'), '1.5') == -1) {
			return $this->hookDisplayPayment($params);
		} else
		{
			$this->hookDisplayPayment($params);	
		}		
	}

	public function hookDisplayPayment($params)
	{
		if (!$this->active)
			return;

		if (!$this->checkCurrency($params['cart']))
			return;

		$cart = $this->context->cart;

		$this->context->smarty->assign(array(
			'payment_type' => Configuration::get('VT_PAYMENT_TYPE'),
			'cart' => $cart,
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));

		// 1.4 compatibility
		if (version_compare(Configuration::get('PS_INSTALL_VERSION'), '1.5') == -1) {
			$result = $this->display(__FILE__, 'views/templates/hook/payment.tpl');
		} else
		{
			$result = $this->display(__FILE__, 'payment.tpl');	
		}
		return $result;
	}
	
	public function checkCurrency($cart)
	{
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}

	// Retrocompatibility 1.4/1.5
	private function initContext()
	{
	  if (class_exists('Context'))
	    $this->context = Context::getContext();
	  else
	  {
	    global $smarty, $cookie;
	    $this->context = new StdClass();
	    $this->context->smarty = $smarty;
	    $this->context->cookie = $cookie;
	    if (array_key_exists('cart', $GLOBALS))
	    {
	    	global $cart;
	    	$this->context->cart = $cart;
	    }
	    if (array_key_exists('link', $GLOBALS))
	    {
	    	global $link;
	    	$this->context->link = $link;
	    }
	  }
	}

	// Retrocompatibility 1.4
	public function execPayment($cart)
	{
		if (!$this->active)
			return ;
		if (!$this->checkCurrency($cart))
			Tools::redirectLink(__PS_BASE_URI__.'order.php');

		global $cookie, $smarty;

		$smarty->assign(array(
			'nbProducts' => $cart->nbProducts(),
			'cust_currency' => $cart->id_currency,
			'currencies' => $this->getCurrency((int)$cart->id_currency),
			'total' => $cart->getOrderTotal(true, Cart::BOTH),
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.((int)Configuration::get('PS_REWRITING_SETTINGS') && count(Language::getLanguages()) > 1 && isset($smarty->ps_language) && !empty($smarty->ps_language) ? $smarty->ps_language->iso_code.'/' : '').'modules/'.$this->name.'/'
		));

		return $this->display(__FILE__, 'payment_execution.tpl');
	}		
}
