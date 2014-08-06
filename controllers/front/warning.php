<?php

class VeritransPayWarningModuleFrontController extends ModuleFrontController
{
  /**
   * @see FrontController::initContent()
   */

  public function initContent()
  {
    $this->display_column_left = false;
    
    $link = new Link();
    parent::initContent();
    
    $this->setTemplate('warning_page.tpl');
    
  }  
}

