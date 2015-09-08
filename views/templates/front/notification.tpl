{capture name=path}{l s='Veritrans payment.' mod='veritranspay'}{/capture}
<h2>{l s='Order summary' mod='veritranspay'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3 class="page-subheading">{l s='Payment via Veritrans.' mod='veritranspay'}
<!-- <form action="{$link->getModuleLink('veritranspay', 'validation', [], true)}" method="post"> -->
<img src="{$this_path}Veritrans.png" alt="{l s='veritrans' mod='veritranspay'}" width="120" height="21" style=" float:left; margin: 0px 10px 5px 0px;" /></h3> <br/>
<div class="text-center">
{if $smarty.get.status_code == '200' || $smarty.get.status_code == '201' || $status == 'success'}
	<p>
		<b><h3 class="alert alert-success">{l s='Your payment on %s is complete!' sprintf=$shop_name mod='veritranspay'}</h3></b>
	</p>
	<p class="warning">
		{l s='If you have questions, comments or concerns, please contact our' mod='veritranspay'} <a href="{$link->getPageLink('contact', true)}">{l s='expert customer support team. ' mod='veritranspay'}</a>.<br/><br/>
	</p>
	<a href="{$link->getPageLink('history', true)}" title="{l s='Back to orders'}" class="button-exclusive btn btn-primary">{l s='view order history'}</a>
{elseif $status == 'back'}
	<p>
		<b><h3 class="alert alert-warning">{l s='We havent received your payment' mod='veritranspay'}</h3></b>
	</p>
	<p class="warning">
		{l s='If you want to go to your cart, please do re-checkout.'}
	</p>
	<a class="button" href="{$link->getPageLink('order', true, NULL, "submitReorder&id_order={$order_id|intval}")|escape:'html':'UTF-8'}" title="{l s='Re-Checkout'}"> 
	<i class="icon-refresh"></i>&nbsp;{l s='Re-Checkout'}</a>
{else}
	<p>
		<b><h3 class="alert alert-danger">{l s='Payment Error!' mod='veritranspay'}</h3></b>
	</p>
	<p class="warning">
		{l s='We noticed a problem with your order. Please do re-checkout.
		If you think this is an error, feel free to contact our' mod='veritranspay'} <a href="{$link->getPageLink('contact', true)}">{l s='expert customer support team. ' mod='veritranspay'}</a> <br/><br/>
	</p>
	<a class="button" href="{$link->getPageLink('order', true, NULL, "submitReorder&id_order={$order_id|intval}")|escape:'html':'UTF-8'}" title="{l s='Re-Checkout'}"> 
	<i class="icon-refresh"></i>&nbsp;{l s='Re-Checkout'}</a>
{/if}
</div>
<br/><br/><br/>
