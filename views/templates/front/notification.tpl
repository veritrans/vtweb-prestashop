{capture name=path}{l s='Veritrans payment.' mod='veritranspay'}{/capture}
<h2>{l s='Order summary' mod='veritranspay'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3 class="page-subheading">{l s='Payment via Veritrans.' mod='veritranspay'}
<!-- <form action="{$link->getModuleLink('veritranspay', 'validation', [], true)}" method="post"> -->
<img src="{$this_path}Veritrans.png" alt="{l s='veritrans' mod='veritranspay'}" width="120" height="21" style=" float:left; margin: 0px 10px 5px 0px;" /></h3> <br/>
<div class="text-center">
{if $status == 'success'}
	<p>
		<b><h3>{l s='Your payment of an order on %s is complete.' sprintf=$shop_name mod='veritranspay'}</h3></b>
	</p>
	<p class="warning">
		{l s='If you have questions, comments or concerns, please contact our' mod='veritranspay'} <a href="{$link->getPageLink('contact', true)}">{l s='expert customer support team. ' mod='veritranspay'}</a>.
	</p>
	<a href="{$link->getPageLink('history', true)}" title="{l s='Back to orders'}" class="button-exclusive btn btn-primary">{l s='view order history'}</a>
{else}
	<p>
		<b><h3>{l s='Payment Error.' mod='veritranspay'}</h3></b>
	</p>
	<p class="warning">
		{l s='We noticed a problem with your order. Please do re-checkout.
		If you think this is an error, feel free to contact our' mod='veritranspay'} <a href="{$link->getPageLink('contact', true)}">{l s='expert customer support team. ' mod='veritranspay'}</a> <br/>
		<a href="{$recheckout}" title="{l s='re-checkout'}" class="button-exclusive btn btn-primary">{l s='Re-Checkout'}</a>
	</p>
{/if}
</div>
<br/><br/><br/>
