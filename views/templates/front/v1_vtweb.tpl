<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Redirecting to Veritrans...</title>
</head>
<body>
  <form action="{$payment_redirect_url}" method="post" name='form_auto_post'>
    <input type="hidden" name="MERCHANT_ID" value="{$merchant_id}" />
    <input type="hidden" name="ORDER_ID" value="{$order_id}" />
    <input type="hidden" name="TOKEN_BROWSER" value="{$token_browser}" />
    <span>Please wait. You are being redirected to Veritrans payment page...</span>
  </form>
  {literal}
  <script language="javascript" type="text/javascript">
    document.form_auto_post.submit();  
  </script>
  {/literal}
</body>
</html>

