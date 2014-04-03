<?php

if (!defined('_PS_VERSION_'))
	exit;

class VeritransPay extends PaymentModule
{
	private $_html = '';
	private $_postErrors = array();

	public $merchant_id;
	public $merchant_hash;
	public $kurs;
	public $convenience_fee;
	public $veritrans_client_key;
	public $veritrans_server_key;
	public $veritrans_api_version;
	public $veritrans_installments;
	public $veritrans_3d_secure;
	public $veritrans_payment_type;

	public function __construct()
	{
		$this->name = 'veritranspay';
		$this->tab = 'payments_gateways';
		$this->version = '0.6';
		$this->author = 'Veritrans';
		$this->bootstrap = true;
		
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';
		$this->convenience_fee = 0;

		$config_keys = array(
			'MERCHANT_ID', 
			'MERCHANT_HASH',
			'VERITRANS_CLIENT_KEY',
			'VERITRANS_SERVER_KEY',
			'VERITRANS_API_VERSION',
			'VERITRANS_PAYMENT_TYPE',
			'VERITRANS_3D_SECURE',
			'KURS',
			'CONVENIENCE_FEE');

		foreach (array('BNI', 'MANDIRI', 'CIMB') as $bank) {
			foreach (array(3, 6, 9, 12, 18, 24) as $months) {
				array_push($config_keys, 'VERITRANS_INSTALLMENTS_' . $bank . '_' . $months);
			}
		}

		$config = Configuration::getMultiple($config_keys);

		foreach ($config_keys as $key) {
			if (isset($config[$key]))
				$this->{strtolower($key)} = $config[$key];
		}
		
		// if (isset($config['MERCHANT_HASH']))
		// 	$this->merchant_hash = $config['MERCHANT_HASH'];
		// if (isset($config['KURS']))
		// 	$this->kurs = $config['KURS'];
		// else Configuration::set('KURS',1);
		// if (isset($config['CONVENIENCE_FEE']))
		// 	$this->convenience_fee = $config['CONVENIENCE_FEE'];
		// else Configuration::set('CONVENIENCE_FEE',0);
		
		parent::__construct();

		$this->displayName = $this->l('Veritrans Pay');
		$this->description = $this->l('Accept payments for your products via Veritrans.');
		$this->confirmUninstall = $this->l('Are you sure about uninstalling Veritrans pay?');
		
		if (!isset($this->merchant_id) || !isset($this->merchant_hash))
			$this->warning = $this->l('Merchant ID and Merchant Hash must be configured before using this module.');
		if (!count(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency has been set for this module.');

		$this->extra_mail_vars = array(
										'{merchant_id}' => Configuration::get('MERCHANT_ID'),
										'{merchant_hash}' => nl2br(Configuration::get('MERCHANT_HASH'))
										);
	}

	public function install()
	{
		if (!parent::install() || !$this->registerHook('payment'))
			return false;

		include_once(_PS_MODULE_DIR_.'/'.$this->name.'/vtpay_install.php');
		$vtpay_install = new VeritransPayInstall();
		$vtpay_install->createTable();

		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('MERCHANT_ID')
				|| !Configuration::deleteByName('MERCHANT_HASH')
				|| !Configuration::deleteByName('KURS')
				|| !Configuration::deleteByName('CONVENIENCE_FEE')
				|| !parent::uninstall())
			return false;
		return true;
	}

	private function _postValidation()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			if (!Tools::getValue('merchant_hash'))
				$this->_postErrors[] = $this->l('Merchant Hash are required.');
			else if (!Tools::getValue('merchant_id'))
				$this->_postErrors[] = $this->l('Merchant ID is required.');
		}
	}

	private function _postProcess()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			Configuration::updateValue('MERCHANT_ID', Tools::getValue('merchant_id'));
			Configuration::updateValue('MERCHANT_HASH', Tools::getValue('merchant_hash'));
			Configuration::updateValue('KURS', Tools::getValue('kurs'));
			Configuration::updateValue('CONVENIENCE_FEE', Tools::getValue('convenience_fee'));
		}
		$this->_html .= '<div class="conf confirm"> '.$this->l('Settings updated').'</div>';
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
							)
						),
					array(
						'type' => 'text',
						'label' => 'Merchant ID',
						'name' => 'MERCHANT_ID',
						'desc' => 'Consult to your Merchant Administration Portal for the value of this field.'
						),
					array(
						'type' => 'text',
						'label' => 'Merchant Hash Key',
						'name' => 'MERCHANT_HASH',
						'desc' => 'Consult to your Merchant Administration Portal for the value of this field.'
						),
					array(
						'type' => 'text',
						'label' => 'VT-Direct Client Key',
						'name' => 'VERITRANS_CLIENT_KEY',
						'desc' => 'Consult to your Merchant Administration Portal for the value of this field.'
						),
					array(
						'type' => 'text',
						'label' => 'VT-Direct Server Key',
						'name' => 'VERITRANS_SERVER_KEY',
						'desc' => 'Consult to your Merchant Administration Portal for the value of this field.'
						),
					array(
						'type' => 'select',
						'label' => 'Payment Type',
						'name' => 'VERITRANS_PAYMENT_TYPE',
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
							)
						),
					array(
						'type' => 'checkbox',
						'label' => 'Enable BNI Installments?',
						'name' => 'VERITRANS_INSTALLMENTS',
						'values' => array(
							'query' => $installments_options['BNI'],
							'id' => 'id_option',
							'name' => 'name'
							)
						),
					array(
						'type' => 'checkbox',
						'label' => 'Enable Mandiri Installments?',
						'name' => 'VERITRANS_INSTALLMENTS',
						'values' => array(
							'query' => $installments_options['MANDIRI'],
							'id' => 'id_option',
							'name' => 'name'
							)
						),
					array(
						'type' => 'checkbox',
						'label' => 'Enable CIMB Installments?',
						'name' => 'VERITRANS_INSTALLMENTS',
						'values' => array(
							'query' => $installments_options['CIMB'],
							'id' => 'id_option',
							'name' => 'name'
							)
						),
					array(
						'type' => 'text',
						'label' => 'IDR Conversion Rate',
						'name' => 'KURS',
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
		return array(
			'MERCHANT_ID' => Tools::getValue('MERCHANT_ID', Configuration::get('MERCHANT_ID'))
		);
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
						<td><input type="text" name="merchant_id" value="'.htmlentities(Tools::getValue('merchant_id', $this->merchant_id), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td>
					</tr>
					<tr>
						<td width="130" style="vertical-align: top;">'.$this->l('Merchant Hash*').'</td>
						<td><input type="text" name="merchant_hash" value="'.htmlentities(Tools::getValue('merchant_hash', $this->merchant_hash), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td>
					</tr>
					<tr>
						<td width="130" style="vertical-align: top;">'.$this->l('Kurs').'</td>
						<td><input type="text" name="kurs" value="'.htmlentities(Tools::getValue('kurs', $this->kurs), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td>
					</tr>
					<tr>
						<td width="130" style="vertical-align: top;">'.$this->l('Convenience Fee (%)').'</td>
						<td><input type="text" name="convenience_fee" value="'.htmlentities(Tools::getValue('convenience_fee', $this->convenience_fee), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td>
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
					$this->_html .= '<div class="alert error">'.$err.'</div>';
		}
		else
			$this->_html .= '<br />';

		$this->_displayVeritransPay();
		$this->_displayForm();

		return $this->_html;
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
