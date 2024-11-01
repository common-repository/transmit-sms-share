function BurstSMSsubmitSMS(){
    if(jQuery('#TrSMSname').val().length == 0){
        jQuery('#TrSMSname').css('border','1px solid red');
         jQuery('#TrSMSname').css('color','red');
        jQuery('#TrSMSname').val('required');
        return false;
    }
    else if(jQuery('#TrSMSphone').val().length == 0){
        jQuery('#TrSMSphone').css('border','1px solid red');
        jQuery('#TrSMSphone').css('color','red');
        jQuery('#TrSMSphone').val('required');
        return false;
    }else {
        jQuery.ajax({
		url: jQuery('#burstSMSContact').attr('action'),
		type:'POST',
		data : jQuery('#burstSMSContact').serialize() + '&BurstSMSsendToken=Y',
		success: function(result){
				jQuery('#BusrtSMSResultContainer').fadeIn('fast',function(){
                                    jQuery(this).html(result);
                                }).delay(10000).fadeOut('slow');
			} 
		});
        }
	return false;
}
jQuery(document).ready(function(){
    jQuery('#TrSMSname').focus(function(){
        jQuery(this).val('');
        jQuery(this).css('border','1px solid #938F77');
        jQuery(this).css('color','#3C3C3C');
    });
     jQuery('#TrSMSphone').focus(function(){
        jQuery(this).val('');
        jQuery(this).css('border','1px solid #938F77');
          jQuery(this).css('color','#3C3C3C');
    });
    
})

      function countChar(val) {
        var len = val.value.length;
         jQuery('#charNum').css('color',"black");
        if (len > 300) {
          val.value = val.value.substring(0, 300);
        } 
        else {
           if(300-len < 10){
                jQuery('#charNum').css('color',"red");
            } 
          jQuery('#charNum').text(300 - len +  " chars left");
        }
      };