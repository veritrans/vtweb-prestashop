{if $transaction_status == Configuration::get('VT_PAYMENT_FAILURE_STATUS_MAP')}
	<p>We are sorry, but there is an error when we are processing your payment.</p>
{elseif $transaction_status == Configuration::get('VT_PAYMENT_CHALLENGE_STATUS_MAP')}
	<p>Your payment is due to the authorization by our system.</p>
{elseif $transaction_status == Configuration::get('VT_PAYMENT_SUCCESS_STATUS_MAP')}
	<p>Your payment is successfully processed.</p>
{/if}
