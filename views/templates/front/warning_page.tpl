<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Redirecting to Veritrans...</title>
</head>
<body>
  <div class="box cheque-box">
    <h3 class="page-subheading">Warning</h3>
    {if $smarty.get.message == 1}
      <p> Sorry, we are unable to proceed your transaction with installment.<br>
        Transaction with installment is only allowed for one product type on your cart.<br><br>
      </p>
    {/if}
    
    {if $smarty.get.message == 2}
      <p> Sorry, we are unable to proceed your transaction with installment.<br>
        Transaction with installment is only allowed for transaction amount above Rp 500.000 <br><br>
      </p>
    {/if}

    <p><a href="{$smarty.get.redirlink}">Click here to continue with full payment</a></p>

  </div>
</body>
</html>

  