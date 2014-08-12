 $(function() {  

  function sensitiveOptions() {
    var api_version = '2' ;//$('#veritransApiVersion').val();
    var payment_type = $('#veritransPaymentType').val();
    var api_string = 'v' + api_version + '_settings';
    var payment_type_string = payment_type;
    var api_payment_type_string = 'v' + api_version + '_' + payment_type + '_settings';
    
    // 1.6 default back-office theme
    $('.sensitive').closest('.form-group').hide();
    // 1.5 default back-office theme
    $('.sensitive').closest('.margin-form').hide();
    $('.sensitive').closest('.margin-form').prev().hide();

    // 1.6 default back-office theme
    $('.' + api_string).closest('.form-group').show();
    $('.' + payment_type_string).closest('.form-group').show();
    $('.' + api_payment_type_string).closest('.form-group').show();

    // 1.5 default back-office theme
    $('.' + api_string).closest('.margin-form').show();
    $('.' + payment_type_string).closest('.margin-form').show();
    $('.' + api_payment_type_string).closest('.margin-form').show();
    $('.' + api_string).closest('.margin-form').prev().show();
    $('.' + payment_type_string).closest('.margin-form').prev().show();
    $('.' + api_payment_type_string).closest('.margin-form').prev().show();
  }

  $("#veritransPaymentType").on('change', function(e, data) {
    sensitiveOptions();
  });
  
  function setCheckedAttribute(name){
    var $radios = $('input:radio[name='+ name+']');
    $radios.filter('[value=' + 0 +']').attr('checked', true);
  }

  function hideOptions(nameClass, type){
    if(type=="class"){
      $("."+nameClass).closest('.form-group').hide();    
      $("."+nameClass).closest('.margin-form').hide(); 
      $("."+nameClass).closest('.margin-form').prev().hide();    
    } else {
      $("#"+nameClass).closest('.form-group').hide();    
      $("#"+nameClass).closest('.margin-form').hide();
      $("#"+nameClass).closest('.margin-form').prev().hide();    
    }
  }

  function showOptions(nameClass, type){
    if (type=="class"){
      $("."+nameClass).closest('.form-group').show();
      $("."+nameClass).closest('.margin-form').show();
      $("."+nameClass).closest('.margin-form').prev().show();
    }else{
      $("#"+nameClass).closest('.form-group').show();
      $("#"+nameClass).closest('.margin-form').show();
      $("#"+nameClass).closest('.margin-form').prev().show();
    }  
  } 

  function setUpVisibility(){    
    var a = $("#VT_ENABLE_INSTALLMENT").val();      
    switch(a){
      case "off":            
            //alert("off " + a + " " + EnableMandiri + " " + EnableBni);
            setCheckedAttribute('ENABLED_BNI_INSTALLMENT');
            setCheckedAttribute('ENABLED_MANDIRI_INSTALLMENT');
            hideOptions('VT_INSTALLMENTS_MANDIRI', "class");
            hideOptions('ENABLED_MANDIRI_INSTALLMENT_off', "div");                                    
            hideOptions('VT_INSTALLMENTS_BNI', "class");
            hideOptions('ENABLED_BNI_INSTALLMENT_off', "div");                                                                
            break;
      case "certain_product":          
            //alert("cp " + a + " " + EnableMandiri + " " + EnableBni);
            hideOptions('VT_INSTALLMENTS_MANDIRI', "class");
            showOptions('ENABLED_MANDIRI_INSTALLMENT_off', "div");
            hideOptions('VT_INSTALLMENTS_BNI', "class");
            showOptions('ENABLED_BNI_INSTALLMENT_off', "div");            
            break;
      case "all_product":                                
            showOptions('ENABLED_MANDIRI_INSTALLMENT_off', "div");            
            showOptions('ENABLED_BNI_INSTALLMENT_off', "div");    
            var EnableBni = $('input:radio[name=ENABLED_BNI_INSTALLMENT]:checked').val();
            var EnableMandiri = $('input:radio[name=ENABLED_MANDIRI_INSTALLMENT]:checked').val();                     
            
            if (EnableMandiri == 1){              
              showOptions('VT_INSTALLMENTS_MANDIRI', "class");
            } else {
              hideOptions('VT_INSTALLMENTS_MANDIRI', "class");
            }

            if (EnableBni == 1){           
              showOptions('VT_INSTALLMENTS_BNI', "class");
            } else {
              hideOptions('VT_INSTALLMENTS_BNI', "class");
            }
            break;
    }
  }

  $("#VT_ENABLE_INSTALLMENT").on('change', function(e, data) {       
    setUpVisibility();
  });
  
  $("#ENABLED_BNI_INSTALLMENT_off").on('change', function(e, data) {    
     hideOptions('VT_INSTALLMENTS_BNI', "class");   
  });

  $("#ENABLED_BNI_INSTALLMENT_on").on('change', function(e, data) {    
     var a = $("#VT_ENABLE_INSTALLMENT").val();
     if (a == "all_product")
      showOptions('VT_INSTALLMENTS_BNI', "class");   
  });

  $("#ENABLED_MANDIRI_INSTALLMENT_off").on('change', function(e, data) {    
     hideOptions('VT_INSTALLMENTS_MANDIRI', "class");   
  });

  $("#ENABLED_MANDIRI_INSTALLMENT_on").on('change', function(e, data) {    
      var a = $("#VT_ENABLE_INSTALLMENT").val();
      if (a == "all_product")
        showOptions('VT_INSTALLMENTS_MANDIRI', "class");   
  });
  
  setUpVisibility();
  sensitiveOptions();
});