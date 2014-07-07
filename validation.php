<?php 

$useSSL = true;

$root_dir = str_replace('modules/veritranspay', '', dirname($_SERVER['SCRIPT_FILENAME']));

include_once($root_dir.'/config/config.inc.php');

$controller = new FrontController();

if (Tools::usingSecureMode())
  $useSSL = $controller->ssl = true;

$controller->init();

include_once($root_dir.'/modules/veritranspay/veritranspay.php');

if (!$cookie->isLogged(true))
  Tools::redirect('authentication.php?back=order.php');
elseif (!Customer::getAddressesTotalById((int)($cookie->id_customer)))
  Tools::redirect('address.php?back=order.php?step=1');

$veritransPay = new VeritransPay();
$keys = $veritransPay->execValidation($cart);

$veritrans_api_version = Configuration::get('VT_API_VERSION');
$veritrans_payment_method = Configuration::get('VT_PAYMENT_TYPE');

if ($keys['errors'])
{
	var_dump($keys['errors']);
	exit;
} else
{
	if ($veritrans_api_version == 2 && $veritrans_payment_method == 'vtweb')
	{
		Tools::redirectLink($keys['redirect_url']);
	} else if ($veritrans_api_version == 2 && $veritrans_payment_method == 'vtdirect')
	{

	}
}