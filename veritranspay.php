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

		$this->config_keys = array(
			'VERITRANS_MERCHANT_ID', 
			'VERITRANS_MERCHANT_HASH',
			'VERITRANS_CLIENT_KEY',
			'VERITRANS_SERVER_KEY',
			'VERITRANS_API_VERSION',
			'VERITRANS_PAYMENT_TYPE',
			'VERITRANS_3D_SECURE',
			'VERITRANS_KURS',
			'VERITRANS_CONVENIENCE_FEE',
			'VERITRANS_PAYMENT_SUCCESS_STATUS_MAPPING',
			'VERITRANS_PAYMENT_FAILURE_STATUS_MAPPING',
			'VERITRANS_PAYMENT_CHALLENGE_STATUS_MAPPING',
			'VERITRANS_ENVIRONMENT'
			);

		foreach (array('BNI', 'MANDIRI', 'CIMB') as $bank) {
			foreach (array(3, 6, 9, 12, 18, 24) as $months) {
				array_push($this->config_keys, 'VERITRANS_INSTALLMENTS_' . $bank . '_' . $months);
			}
		}

		$config = Configuration::getMultiple($this->config_keys);

		foreach ($this->config_keys as $key) {
			if (isset($config[$key]))
				$this->{strtolower($key)} = $config[$key];
		}
		
		// if (isset($config['VERITRANS_MERCHANT_HASH']))
		// 	$this->veritrans_merchant_hash = $config['VERITRANS_MERCHANT_HASH'];
		// if (isset($config['VERITRANS_KURS']))
		// 	$this->veritrans_kurs = $config['VERITRANS_KURS'];
		// else Configuration::set('VERITRANS_KURS',1);
		// if (isset($config['VERITRANS_CONVENIENCE_FEE']))
		// 	$this->veritrans_convenience_fee = $config['VERITRANS_CONVENIENCE_FEE'];
		// else Configuration::set('VERITRANS_CONVENIENCE_FEE',0);
		
		parent::__construct();

		$this->displayName = $this->l('Veritrans Pay');
		$this->description = $this->l('Accept payments for your products via Veritrans.');
		$this->confirmUninstall = $this->l('Are you sure about uninstalling Veritrans pay?');
		
		if (!isset($this->veritrans_merchant_id) || !isset($this->veritrans_merchant_hash))
			$this->warning = $this->l('Merchant ID and Merchant Hash must be configured before using this module.');
		
		if (!count(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency has been set for this module.');

		$this->extra_mail_vars = array(
			'{veritrans_merchant_id}' => Configuration::get('VERITRANS_MERCHANT_ID'),
			'{veritrans_merchant_hash}' => nl2br(Configuration::get('VERITRANS_MERCHANT_HASH'))
			);
	}

	public function install()
	{
		if (!parent::install() || 
			!$this->registerHook('payment') ||
			!$this->registerHook('header') ||
			!$this->registerHook('displayBackOfficeHeader'))
			return false;

		include_once(_PS_MODULE_DIR_ . '/' . $this->name . '/vtpay_install.php');
		$vtpay_install = new VeritransPayInstall();
		$vtpay_install->createTable();

		return true;
	}

	public function uninstall()
	{
		$status = true;
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
			if (!Tools::getValue('VERITRANS_MERCHANT_HASH'))
				$this->_postErrors[] = $this->l('Merchant Hash are required.');
			else if (!Tools::getValue('VERITRANS_MERCHANT_ID'))
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
			// Configuration::updateValue('VERITRANS_MERCHANT_ID', Tools::getValue('veritrans_merchant_id'));
			// Configuration::updateValue('VERITRANS_MERCHANT_HASH', Tools::getValue('veritrans_merchant_hash'));
			// Configuration::updateValue('VERITRANS_KURS', Tools::getValue('veritrans_kurs'));
			// Configuration::updateValue('VERITRANS_CONVENIENCE_FEE', Tools::getValue('veritrans_convenience_fee'));
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

		// var_dump(OrderState::getOrderStates($this->context->language->id));

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
						'name' => 'VERITRANS_API_VERSION',
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
						'name' => 'VERITRANS_ENVIRONMENT',
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
						'name' => 'VERITRANS_MERCHANT_ID',
						'required' => true,
						'desc' => 'Consult to your Merchant Administration Portal for the value of this field.',
						'class' => 'v1_settings vtweb_settings'
						),
					array(
						'type' => 'text',
						'label' => 'Merchant Hash Key',
						'name' => 'VERITRANS_MERCHANT_HASH',
						'required' => true,
						'desc' => 'Consult to your Merchant Administration Portal for the value of this field.',
						'class' => 'v1_settings vtweb_settings'
						),
					array(
						'type' => 'text',
						'label' => 'VT-Direct Client Key',
						'name' => 'VERITRANS_CLIENT_KEY',
						'required' => true,
						'desc' => 'Consult to your Merchant Administration Portal for the value of this field.',
						'class' => 'vtdirect_settings'
						),
					array(
						'type' => 'text',
						'label' => 'VT-Direct Server Key',
						'name' => 'VERITRANS_SERVER_KEY',
						'required' => true,
						'desc' => 'Consult to your Merchant Administration Portal for the value of this field.',
						'class' => 'vtdirect_settings'
						),
					array(
						'type' => 'select',
						'label' => 'Payment Type',
						'name' => 'VERITRANS_PAYMENT_TYPE',
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
						'name' => 'VERITRANS_INSTALLMENTS',
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
						'name' => 'VERITRANS_INSTALLMENTS',
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
						'name' => 'VERITRANS_INSTALLMENTS',
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
						'name' => 'VERITRANS_3D_SECURE',
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
						'name' => 'VERITRANS_PAYMENT_SUCCESS_STATUS_MAPPING',
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
						'name' => 'VERITRANS_PAYMENT_FAILURE_STATUS_MAPPING',
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
						'name' => 'VERITRANS_PAYMENT_CHALLENGE_STATUS_MAPPING',
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
						'name' => 'VERITRANS_KURS',
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

	public function getConfigFieldsValues()
	{
		$result = array();
		foreach ($this->config_keys as $key) {
			$result[$key] = Tools::getValue($key, Configuration::get($key));
		}
		return $result;
		// return array(
		// 	'VERITRANS_MERCHANT_ID' => Tools::getValue('VERITRANS_MERCHANT_ID', Configuration::get('VERITRANS_MERCHANT_ID'))
		// );
	}

	private function _displayFormOld()
	{
		$this->_html .=
		'<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Contact details').'</legend>
				<table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
					<tr><td colspan="2">'.$this->l('Please specify Merchant ID.').'.<br /><br /></td></tr>
					<tr>
						<td width="130" style="vertical-align: top;">'.$this->l('Merchant ID*').'</td>
						<td><input type="text" name="veritrans_merchant_id" value="'.htmlentities(Tools::getValue('veritrans_merchant_id', $this->veritrans_merchant_id), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td>
					</tr>
					<tr>
						<td width="130" style="vertical-align: top;">'.$this->l('Merchant Hash*').'</td>
						<td><input type="text" name="veritrans_merchant_hash" value="'.htmlentities(Tools::getValue('veritrans_merchant_hash', $this->veritrans_merchant_hash), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td>
					</tr>
					<tr>
						<td width="130" style="vertical-align: top;">'.$this->l('Kurs').'</td>
						<td><input type="text" name="veritrans_kurs" value="'.htmlentities(Tools::getValue('veritrans_kurs', $this->veritrans_kurs), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td>
					</tr>
					<tr>
						<td width="130" style="vertical-align: top;">'.$this->l('Convenience Fee (%)').'</td>
						<td><input type="text" name="veritrans_convenience_fee" value="'.htmlentities(Tools::getValue('veritrans_convenience_fee', $this->veritrans_convenience_fee), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td>
					</tr>

				</table>
				<br/>
				<input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" />
			</fieldset>
		</form>';
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
		// is this supposed to work in 1.5 and 1.4?
	}

	public function hookDisplayBackOfficeHeader($params)
	{
		$this->context->controller->addJS($this->_path . 'js/veritrans_admin.js', 'all');
	}

	public function hookPayment($params)
	{
		if (!$this->active)
			return;
		if (!$this->checkCurrency($params['cart']))
			return;

		$cart = $this->context->cart;

		$this->smarty->assign(array(
			'cart' => $cart,
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		return $this->display(__FILE__, 'payment.tpl');
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
}
