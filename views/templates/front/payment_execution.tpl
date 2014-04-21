{capture name=path}{l s='Veritrans payment.' mod='veritranspay'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='veritranspay'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='veritranspay'}</p>
{elseif $error_message != ""}
	<p class="warning">{$error_message}</p><br/>
{else}
	<h3>{l s='Payment via Veritrans.' mod='veritranspay'}</h3>
	<form action="{$url}" method="post" name="payment_form">
	{* <form action="{$link->getModuleLink('veritranspay', 'validation', [], true)}" method="post">  *}
	<p>
		<img src="{$this_path}veritrans.jpg" alt="{l s='Veritrans' mod='veritranspay'}" width="86" height="49" style="float:left; margin: 0px 10px 5px 0px;" />
		<br/><b>{l s='You have chosen to pay via Veritrans.' mod='veritranspay'}</b><br/>
	</p>
	<p style="margin-top:20px;">
		- {l s='The total amount of your order is' mod='veritranspay'}
		<span id="amount" class="price">{displayPrice price=$total}</span>
		{if $use_taxes == 1}
    	{l s='(tax incl.)' mod='veritranspay'}
    {/if}<br/>
		-
		{if $currencies|@count > 1}
			{l s='We allow several currencies to be sent via Veritrans.' mod='veritranspay'}
			<br /><br />
			{l s='Choose one of the following:' mod='veritranspay'}
			<select id="currency_payement" name="currency_payement" onchange="setCurrency($('#currency_payement').val());">
				{foreach from=$currencies item=currency}
					<option value="{$currency.id_currency}" {if $currency.id_currency == $cust_currency}selected="selected"{/if}>{$currency.name}</option>
				{/foreach}
			</select>
		{else}
			{l s='We allow the following currency to be sent via Veritrans:' mod='veritranspay'}&nbsp;<b>{$currencies.0.name}</b>
			<input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
		{/if}
	</p>

	<p><b>
		{l s='Please confirm your order by clicking "Place my order".' mod='veritranspay'}
	</b></p>

	<p class="cart_navigation">
		<input type="hidden" size="30" name="MERCHANT_ID" value="{$merchant_id}" /><br/>
		<input type="hidden" name="ORDER_ID" value="{$order_id}" /><br/>
		<input type="hidden" size="70" name="TOKEN_BROWSER" value="{$token_browser}" /><br/>
		
		<input type="submit" name="submit" value="{l s='Place my order' mod='veritranspay'}" class="exclusive_large" />
		<a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_large">{l s='Other payment methods' mod='veritranspay'}</a>
	</p>
	</form>
{/if}