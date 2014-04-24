<form action="{$form_url}" method="post">
  <fieldset>
  <legend><img src="../img/admin/contact.gif" />Basic Information</legend>
    <table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
      <tr><td colspan="2"><p>Merchant Information</p></td></tr>
      <tr>
        <td width="130" style="vertical-align: top;">Merchant ID</td>
        <td><input type="text" name="VT_MERCHANT_ID" value="{$merchant_id}" style="width: 300px;" /></td>
      </tr>
      <tr>
        <td width="130" style="vertical-align: top;">Merchant Hash</td>
        <td><input type="text" name="VT_MERCHANT_HASH" value="{$merchant_hash_key}" style="width: 300px;" /></td>
      </tr>
      <tr>
        <td width="130" style="vertical-align: top;">Kurs</td>
        <td><input type="text" name="VT_KURS" value="{$kurs}" style="width: 300px;" /></td>
      </tr>
      <tr>
        <td width="130" style="vertical-align: top;">Convenience Fee</td>
        <td><input type="text" name="VT_CONVENIENCE_FEE" value="{$convenience_fee}" style="width: 300px;" /></td>
      </tr>
    </table>
    <br/>
    <input class="button" name="btnSubmit" value="Update Settings" type="submit" />
  </fieldset>
</form>