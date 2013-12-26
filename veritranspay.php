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


	public function __construct()
	{
		$this->name = 'veritranspay';
		$this->tab = 'payments_gateways';
		$this->version = '0.5';
		$this->author = 'Veritrans';
		
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		$config = Configuration::getMultiple(array('MERCHANT_ID', 'MERCHANT_HASH','KURS','CONVENIENCE_FEE'));
		if (isset($config['MERCHANT_ID']))
			$this->merchant_id = $config['MERCHANT_ID'];
		if (isset($config['MERCHANT_HASH']))
			$this->merchant_hash = $config['MERCHANT_HASH'];
		if (isset($config['KURS']))
			$this->kurs = $config['KURS'];
		else Configuration::set('KURS',1);
		if (isset($config['CONVENIENCE_FEE']))
			$this->convenience_fee = $config['CONVENIENCE_FEE'];
		else Configuration::set('CONVENIENCE_FEE',0);
		
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
		$this->_html .= '<img src="../modules/veritranspay/veritrans.jpg" style="float:left; margin-right:15px;"><b>'.$this->l('This module allows payment via veritrans.').'</b><br/><br/>
		'.$this->l('Payment via veritrans.').'<br /><br /><br />';
	}

	private function _displayForm()
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
		$this->_html = '<h2>'.$this->displayName.'</h2>';

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
