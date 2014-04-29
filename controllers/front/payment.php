<?php

require_once 'library/veritrans.php';

$root_dir = str_replace('modules/veritranspay/controllers/front', '', dirname($_SERVER['SCRIPT_FILENAME']));
include_once($root_dir . '/config/config.inc.php');
include_once($root_dir . '/init.php');

session_start();

var_dump(_PS_MODE_DEV_);
var_dump($_SERVER['REQUEST_URI']);
var_dump(__PS_BASE_URI__);


// 1.4 retrocompatibility
if (version_compare(Configuration::get('PS_VERSION_DB'), '1.5.0') == -1)
{
  
  //ControllerFactory::includeController('FrontController');
  class ModuleFrontController extends FrontController
  {
    /**
     * @var Module
     */
    public $module;

    public function __construct()
    {
      $this->controller_type = 'modulefront';

      $this->module = Module::getInstanceByName(Tools::getValue('module'));
      if (!$this->module->active)
        Tools::redirect('index');
      $this->page_name = 'module-'.$this->module->name.'-'.Dispatcher::getInstance()->getController();

      parent::__construct();

      $this->display_column_left = Context::getContext()->theme->hasLeftColumn($this->page_name);
      $this->display_column_right = Context::getContext()->theme->hasRightColumn($this->page_name);
    }

    /**
     * Assign module template
     *
     * @param string $template
     */
    public function setTemplate($template)
    {
      if (!$path = $this->getTemplatePath($template))
        throw new PrestaShopException("Template '$template' not found");

      $this->template = $path;
    }

    /**
     * Get path to front office templates for the module
     *
     * @return string
     */
    public function getTemplatePath($template)
    {
      if (Tools::file_exists_cache(_PS_THEME_DIR_.'modules/'.$this->module->name.'/'.$template))
        return _PS_THEME_DIR_.'modules/'.$this->module->name.'/'.$template;
      elseif (Tools::file_exists_cache(_PS_THEME_DIR_.'modules/'.$this->module->name.'/views/templates/front/'.$template))
        return _PS_THEME_DIR_.'modules/'.$this->module->name.'/views/templates/front/'.$template;
      elseif (Tools::file_exists_cache(_PS_MODULE_DIR_.$this->module->name.'/views/templates/front/'.$template))
        return _PS_MODULE_DIR_.$this->module->name.'/views/templates/front/'.$template;

      return false;
    }
  }

}

class VeritransPayPaymentModuleFrontController extends ModuleFrontController
{
  public $ssl = true;
  public $display_column_left = false;

  /**
   * @see FrontController::initContent()
   */

  public function initContent()
  {
    $this->display_column_left = false;
    
    $link = new Link();
    parent::initContent();

    $cart = $this->context->cart;
    if (!$this->module->checkCurrency($cart))
      Tools::redirect('index.php?controller=order');
    
    $this->context->smarty->assign(array(
      'payment_type' => Configuration::get('VT_PAYMENT_TYPE'),
      'api_version' => Configuration::get('VT_API_VERSION'),
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
      'error_message' => '',

      'nbProducts' => $cart->nbProducts(),
      'cust_currency' => $cart->id_currency,
      'currencies' => $this->module->getCurrency((int)$cart->id_currency),
      'total' => $cart->getOrderTotal(true, Cart::BOTH),
      'this_path' => $this->module->getPathUri(),
      'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
    ));
    $this->setTemplate('payment_execution.tpl');
    
  }  
}


if (version_compare(Configuration::get('PS_VERSION_DB'), '1.5.0'))
{
  // $controller = new VeritransPayPaymentModuleFrontController();
  // $controller->run();
}

