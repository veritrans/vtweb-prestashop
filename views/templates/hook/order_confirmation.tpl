{if $transaction_status == 'deny'}
	<p>We are sorry, but there is an error when we are processing your payment.</p>
{elseif $transaction_status == 'challenge'}
	<p>Your payment is due to the authorization by our system.</p>
{elseif $transaction_status == 'capture'}
	<p>Your payment is successfully processed.</p>
{/if}