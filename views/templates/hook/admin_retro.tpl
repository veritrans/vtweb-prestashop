<form action="{$form_url}" method="post">
  <fieldset>
  <legend><img src="../img/admin/contact.gif" />Basic Information</legend>
    <table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
      
      <tr>
        <td width="130" style="vertical-align: top;">API Version</td>
        <td>
          <select name="VT_API_VERSION" id="veritransApiVersion">
            {foreach from=$api_versions item=version key=k}
              <option value="{$k}" {if $k == $api_version}selected{/if}>{$version}</option>
            {/foreach}
          </select>
        </td>
      </tr>
      <!-- API VERSION -->

      <tr>
        <td width="130" style="vertical-align: top;">Payment Type</td>
        <td>
          <select name="VT_PAYMENT_TYPE" id="veritransPaymentType">
            {foreach from=$payment_types item=v key=k}
              <option value="{$k}" {if $k == $payment_type}selected{/if}>{$v}</option>
            {/foreach}
          </select>
        </td>
      </tr>
      <!-- PAYMENT_TYPE -->

      <tr class="v1_vtweb_settings sensitive">
        <td width="130" style="vertical-align: top;">Merchant ID</td>
        <td><input type="text" name="VT_MERCHANT_ID" value="{$merchant_id}" style="width: 300px;" /></td>
      </tr>
      <!-- MERCHANT_ID -->

      <tr class="v1_vtweb_settings sensitive">
        <td width="130" style="vertical-align: top;">Merchant Hash</td>
        <td><input type="text" name="VT_MERCHANT_HASH" value="{$merchant_hash_key}" style="width: 300px;" /></td>
      </tr>
      <!-- MERCHANT_HASH -->

      <tr class="v1_vtdirect_settings v2_settings sensitive">
        <td width="130" style="vertical-align: top;">Client Key</td>
        <td><input type="text" name="VT_CLIENT_KEY" value="{$client_key}" style="width: 300px;" /></td>
      </tr>
      <!-- CLIENT_KEY -->

      <tr class="v1_vtdirect_settings v2_settings sensitive">
        <td width="130" style="vertical-align: top;">Server Key</td>
        <td><input type="text" name="VT_SERVER_KEY" value="{$server_key}" style="width: 300px;" /></td>
      </tr>
      <!-- SERVER_KEY -->

      <tr>
        <td width="130" style="vertical-align: top;">Kurs</td>
        <td><input type="text" name="VT_KURS" value="{$kurs}" style="width: 300px;" /></td>
      </tr>
      <!-- KURS -->

    </table>
    <br/>
    <input class="button" name="btnSubmit" value="Update Settings" type="submit" />
  </fieldset>
</form>
<script>
  $(function() {
    function sensitiveOptions() {
      var api_version = $('#veritransApiVersion').val();
      var payment_type = $('#veritransPaymentType').val();
      var api_string = 'v' + api_version + '_settings';
      var payment_type_string = payment_type;
      var api_payment_type_string = 'v' + api_version + '_' + payment_type + '_settings';
      $('.sensitive').hide();
      $('.' + api_string).show();
      $('.' + payment_type_string).show();
      $('.' + api_payment_type_string).show();
    }

    $("#veritransApiVersion").on('change', function(e, data) {
      sensitiveOptions();
    });
    $("#veritransPaymentType").on('change', function(e, data) {
      sensitiveOptions();
    });

    sensitiveOptions();
    
  });
</script>
  
</script>