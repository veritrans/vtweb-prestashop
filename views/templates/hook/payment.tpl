<div class="row">
  <div class="col-xs-12 col-md-6">
    <p class="payment_module">
      {literal}
        <?php if (version_compare(Configuration::get('PS_INSTALL_VERSION'), '1.5') == -1): ?>
          <a class="bankwire" href="<?php echo $base_dir . 'modules/veritranspay/payment' ?>" title="Pay Via Veritrans">
        <?php else: ?>
          <a class="bankwire" href="<?php echo $link->getModuleLink('veritranspay', 'payment') ?>" title="Pay Via Veritrans">
        <?php endif ?>
      {/literal}
        <img src="{$this_path}veritrans.jpg" alt="{l s='Pay via Veritrans' mod='veritranspay'}" height="30px"/>
        {l s='Pay by Veritrans' mod='veritranspay'}
      </a>
    </p>  
  </div>
</div>
