$(function() {
  function versionDependentOptions() {
    var api_version = $('#veritransApiVersion').val();

    if (api_version == 1)
    {
      // 1.6 default-theme back-office
      $('.v2_settings').closest('.form-group').hide();
      $('.v1_settings').closest('.form-group').show();
      
      // 1.5 default-theme back-office
      $('.v2_settings').closest('.margin-form').hide();
      $('.v2_settings').closest('.margin-form').prev().hide();
      $('.v1_settings').closest('.margin-form').show();
      $('.v1_settings').closest('.margin-form').prev().show();
    } else
    {
      // 1.6 default-theme back-office
      $('.v1_settings').closest('.form-group').hide();
      $('.v2_settings').closest('.form-group').show();

      // 1.5 default-theme back-office
      $('.v1_settings').closest('.margin-form').hide();
      $('.v1_settings').closest('.margin-form').prev().hide();
      $('.v2_settings').closest('.margin-form').show();
      $('.v2_settings').closest('.margin-form').prev().show();
    }
  }

  function paymentApiDependentOptions() {
    var payment_type = $('#veritransPaymentType').val();
    if (payment_type == 'vtweb')
    {
      // 1.6 default-theme back-office
      $('.vtweb_settings').closest('.form-group').show();
      $('.vtdirect_settings').closest('.form-group').hide();

      // 1.5 default-theme back-office
      $('.vtweb_settings').closest('.margin-form').show();
      $('.vtweb_settings').closest('.margin-form').prev().show();
      $('.vtdirect_settings').closest('.margin-form').hide();
      $('.vtdirect_settings').closest('.margin-form').prev().hide();
    } else
    {
      // 1.6 default-theme back-office
      $('.vtweb_settings').closest('.form-group').hide();
      $('.vtdirect_settings').closest('.form-group').show();

      // 1.5 default-theme back-office
      $('.vtweb_settings').closest('.margin-form').hide();
      $('.vtweb_settings').closest('.margin-form').prev().hide();
      $('.vtdirect_settings').closest('.margin-form').show();
      $('.vtdirect_settings').closest('.margin-form').prev().show();
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