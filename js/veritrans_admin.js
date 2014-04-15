$(function() {
  function versionDependentOptions() {
    var api_version = $('#veritransApiVersion').val();
    if (api_version == 1)
    {
      $('.v2_settings').closest('.form-group').hide();
      $('.v1_settings').closest('.form-group').show();
    } else
    {
      $('.v1_settings').closest('.form-group').hide();
      $('.v2_settings').closest('.form-group').show();
    }
  }

  function paymentApiDependentOptions() {
    var payment_type = $('#veritransPaymentType').val();
    if (payment_type == 'vtweb')
    {
      $('.vtweb_settings').closest('.form-group').show();
      $('.vtdirect_settings').closest('.form-group').hide();
    } else
    {
      $('.vtweb_settings').closest('.form-group').hide();
      $('.vtdirect_settings').closest('.form-group').show();
    }
  }

  versionDependentOptions();
  paymentApiDependentOptions();

  $("#veritransApiVersion").on('change', function(e, data) {
    versionDependentOptions();
  });
  $("#veritransPaymentType").on('change', function(e, data) {
    paymentApiDependentOptions();
  });

});