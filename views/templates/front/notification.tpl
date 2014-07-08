{capture name=path}{l s='Veritrans payment.' mod='veritranspay'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='veritranspay'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3>{l s='Payment via Veritrans.' mod='veritranspay'}</h3>
<!-- <form action="{$link->getModuleLink('veritranspay', 'validation', [], true)}" method="post"> -->
<img src="{$this_path}Veritrans.png" alt="{l s='Your payment is error' mod='veritranspay'}" width="86" height="49" style="float:left; margin: 0px 10px 5px 0px;" />

{if $status == 'success'}
	<p>
		<b><h3>{l s='Your order on %s is complete.' sprintf=$shop_name mod='veritranspay'}</h3></b>
	</p>
	<p class="warning">
		{l s='If you have questions, comments or concerns, please contact our' mod='veritranspay'} <a href="{$link->getPageLink('contact', true)}">{l s='expert customer support team. ' mod='veritranspay'}</a>.
	</p>
<a href="{$link->getPageLink('history', true)}" title="{l s='Back to orders'}"><img src="{$img_dir}icon/order.gif" alt="{l s='Back to orders'}" class="icon" /></a>
	<a href="{$link->getPageLink('history', true)}" title="{l s='Back to orders'}">{l s='Back to orders'}</a>
{else}
	<p>
		<b><h3>{l s='Payment Error.' mod='veritranspay'}</h3></b>
	</p>
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, feel free to contact our' mod='veritranspay'} 
		<a href="{$link->getPageLink('contact', true)}">{l s='expert customer support team' mod='veritranspay'}</a>.
	</p>
{/if}

<br/><br/><br/>
