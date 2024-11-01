<?php
add_action( 'admin_init', 'TSCjQueryUi' );
function TSCjQueryUi() {
    wp_enqueue_style( 'baseCSS', TSC_PLUGIN_URL . '/assets/css/style.css' );
    wp_enqueue_style( 'bootsrapCSS', TSC_PLUGIN_URL . '/assets/css/bootstrap.css' );
    wp_register_script('bootstrapJs',  TSC_PLUGIN_URL . '/assets/js/bootstrap.js', false, null);
    wp_enqueue_style( 'fontAwesome', TSC_PLUGIN_URL . '/assets/font-awesome-4.2.0/css/font-awesome.css' );
    wp_enqueue_script('bootstrapJs');
}
function TSC_admin_menu() {
        global $menu;
        $exist = false;
        foreach( $menu as $k => $item ){
      	     if($item[2] == 'transmit-sms'){$exist = true; break;}
    	}
        if(!$exist){
             add_menu_page('Transmit SMS', 'Transmit SMS', 'manage_options','transmit-sms',NULL,TSC_PLUGIN_URL.'/assets/images/sms-wp-favicon.png');
          //add_menu_page('Transmit SMS', 'Transmit SMS', 'manage_options','transmit-sms');
         
              wp_register_script('admin-js',  TSC_PLUGIN_URL . '/admin/admin.js', false, null);
              wp_enqueue_script('admin-js');
        }
        add_action( 'wp_enqueue_scripts', 'TSC_removeSubmenu' );
        add_submenu_page('transmit-sms', 'Transmit SMS - Share as Text Message/SMS', ' Transmit SMS Share', 'manage_options', 'transmitSMSSC', 'TSCSMSC_options' );
}
function TSCSMSC_options() {
    if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	if(isset($_POST['TSC_hidden']) && $_POST['TSC_hidden'] == "Y"){
            TSCSMSC_handleSubmit();
        }
        echo TSCSMSC_settingForm();
}
function TSCSMSC_settingForm(){
    require_once TSC_PLUGIN_DIR .'/APIClient2.php';
    global $TSC_ShortCodeShare;
    $TSCSms = unserialize(stripslashes(get_option(TSCWpOptionApi)));
    if(!empty($TSCSms['apikey'])){
        $TSCapikey  = base64_decode($TSCSms['apikey']);
        $TSCapiSecret  = base64_decode($TSCSms['secret']);
    }else {
        $TSCapikey = '';
        $TSCapiSecret = '';
    }
    $api=new TSCtransmitsmsAPI($TSCapikey,$TSCapiSecret);
    $TSCOption = unserialize(@get_option(TSCWpOption));
    ob_start();  
?>

<script type="text/javascript">
						
 var selectedInput = null;
    jQuery(document).ready(function(){
        //menu icon 
        jQuery('#TSC_pageafter').addClass('form-control');
        jQuery('#TSC_pageafter').css('width', '400');
        TSCapiConnect();
        jQuery('#TSC_optionForm').submit(function(){
            jQuery.ajax({
               url: '<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>',
               type:'POST',
               data:jQuery(this).serialize(),
               beforeSend:function(){
                jQuery("#submitLoader").fadeIn('fast');
                jQuery('#submitOptionResult').removeClass('updated');
                jQuery('#submitOptionResult').removeClass('error');
               },
               success: function(result){
                    jQuery("#submitLoader").fadeOut('fast');
                    if(result == 'SUCCESS'){
                        jQuery('#submitOptionResult').addClass('updated');
                        jQuery('#submitOptionResult').html('<?=TSC_successSubmit?>');
                        TSCrederShortcCode();
                    }else {
                        jQuery('#submitOptionResult').addClass('error');
                        jQuery('#submitOptionResult').html('<?= TSC_failSubmit?>');
                        TSCrederShortcCode();
                    }
                }
    		});
            return false;
        })
        
         jQuery('#TSC_optionFormEdit').submit(function(){
            jQuery.ajax({
               url: '<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>',
               type:'POST',
               data:jQuery(this).serialize() + '&TSCEditCard=Y',
               beforeSend:function(){
                jQuery("#submitLoaderEdit").fadeIn('fast');
                jQuery('#TSCMsgBoxEdit').fadeOut('fast');
                jQuery('#TSCMsgBoxEdit').removeClass('alert');
                jQuery('#TSCMsgBoxEdit').removeClass('alert-success');
                jQuery('#TSCMsgBoxEdit').removeClass('alert-danger');
               },
               success: function(result){
                    jQuery("#submitLoaderEdit").fadeOut('fast');
                    jQuery('#TSCMsgBoxEdit').addClass('alert');
                    if(result == 'SUCCESS'){
                        jQuery('#TSCMsgBoxEdit').addClass('alert-success');
                        jQuery('#TSCMsgBoxEdit').html('<i class="fa fa-check-circle"></i> <?= TSC_successSubmit?>');
                        TSCrederShortcCode();
                    }else {
                        jQuery('#TSCMsgBoxEdit').addClass('alert-danger');
                        jQuery('#TSCMsgBoxEdit').html('<i class="fa fa-times-circle"></i> <?= TSC_failSubmit?>');
                    }
                    jQuery('#TSCMsgBoxEdit').fadeIn('fast');
                }
    		});
            return false;
        })
        
        
        jQuery('#TSC_formPreview').click(function(){
            //var modalFooter = '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>';
            var modalContent = jQuery('#TSC_FormStyle').val();
            jQuery('#TSC_modalPreviewContent').html(modalContent);
            jQuery('#TSC_modalPreview').modal('show');
            return false;
      
        });
        jQuery('#TSC_formPreviewEdit').click(function(){
            // var modalFooter = '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>';
            var modalContent = jQuery('#TSC_FormStyleEdit').val();
            jQuery('#TSCMsgBoxEdit').removeClass('alert');
             jQuery('#TSCMsgBoxEdit').removeClass('alert-success');
              jQuery('#TSCMsgBoxEdit').removeClass('alert-danger');
            jQuery('#TSCMsgBoxEdit').fadeOut('fast');
            jQuery('#TSC_modalPreviewContent').html(modalContent);
            jQuery('#TSC_modalPreview').modal('show');
            return false;
        })
      
     });
     function TSCrederShortcCode(){
        jQuery.ajax({
               url: '<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>',
               type:'POST',
               data:'TSCrenderShortCode=Y',
               beforeSend:function(){
                jQuery('#TSCPanelBody').slideUp('fast');
                jQuery('#TSCPanelBody').html('<img   style="margin-left:10px !important;display:none" src="<?php echo TSC_PLUGIN_URL ?>/assets/images/loading.gif" title="still loading" >');
               },
               success: function(result){
                    jQuery('#TSCPanelBody').html(result);
                    jQuery('#TSCPanelBody').slideDown('fast');
                }
            })
     }
     function TSCeditShortCode(shordCodeIndex,cardMessage,shortcodeForm,shortCodeButton,captcha,applyToPost){
        jQuery('#TSC_cardMessageEdit').val(cardMessage);
        jQuery('#TSC_FormStyleEdit').val(stripslashes(decode_base64(shortcodeForm)));
        jQuery('#TSC_urlCodeEdit').val(stripslashes(decode_base64(shortCodeButton)));
        jQuery('#TSC_ShortCodeId').val(shordCodeIndex);
        if(captcha == 'on'){
           jQuery('#TSC_captchaEdit').attr( "checked",'');
        }else {
            jQuery('#TSC_captchaEdit').removeAttr( "checked");
            
        }
        if(applyToPost == 'on'){
           jQuery('#TSC_applyPostEdit').attr( "checked",'');
        }else {
            jQuery('#TSC_applyPostEdit').removeAttr( "checked");
         }
        jQuery('#TSC_modalEdit').modal('show');
      }
      function TSCdeleteShortCode(shordCodeIndex,label){
        var r = window.confirm('Are you sure to remove '+ label);
        if(r === true){
             jQuery.ajax({
               url: '<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>',
               type:'POST',
               data:'shortCodeIndex=' + shordCodeIndex + '&TSCDeleteCard=Y',
               success: function(result){
                    if(result == 'SUCCESS'){
                        jQuery('#TSCmessageBoxListShortCode').addClass('alert alert-success');
                        jQuery('#TSCmessageBoxListShortCode').html(' <a href="#" class="close" data-dismiss="alert">&times;</a><i class="fa fa-check-circle"></i> Shortcode has been deleted');
                        jQuery('#TSCmessageBoxListShortCode').fadeIn('fast');
                        TSCrederShortcCode();
                      }
                   jQuery('TSCmessageBoxListShortCode').fadeIn('fast');
                }
    		});
        }else{
            return false;
        }
      }
     function TSCapiConnect(){
        var apikey = jQuery('#TSC_apikey').val();
        var apisecret = jQuery('#TSC_apisecret').val();
        if(apikey != "" && apisecret != ""){
            jQuery.ajax({
               url: '<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>',
               type:'POST',
               data:'TSCgetConnect=Y&apikey=' + apikey + '&secret=' + apisecret,
               beforeSend:function(){
                jQuery("#verifyLoader").fadeIn('fast');
                jQuery('#TSC_optionPanel').fadeOut('fast');
                jQuery('#connectionResult').removeClass('updated');
                jQuery('#connectionResult').removeClass('error');
               },
               success: function(result){
                    jQuery("#verifyLoader").fadeOut('fast');
                    if(result == 'SUCCESS'){
                        jQuery('#connectionResult').addClass('updated');
                        jQuery('#connectionResult').html('<?=TSC_successVerify?>');
                        jQuery('#TSC_optionPanel').fadeIn('fast');
                    }else {
                        jQuery('#connectionResult').addClass('error');
                        jQuery('#connectionResult').html(result);
                    }
                }
		});
               }
      return false;
    }
 function decode_base64 (s){
                var e = {}, i, k, v = [], r = '', w = String.fromCharCode;
                var n = [[65, 91], [97, 123], [48, 58], [43, 44], [47, 48]];
                for (z in n){
                for (i = n[z][0]; i < n[z][1]; i++){
                    v.push(w(i));
                    }
                }
                for (i = 0; i < 64; i++){
                    e[v[i]] = i;
                }
                for (i = 0; i < s.length; i+=72){
                var b = 0, c, x, l = 0, o = s.substring(i, i+72);
                for (x = 0; x < o.length; x++)
                {
                    c = e[o.charAt(x)];
                    b = (b << 6) + c;
                    l += 6;
                    while (l >= 8)
                    {
                        r += w((b >>> (l -= 8)) % 256);
                    }
                 }
                }
            return r;
        }
        function stripslashes(str) {
            return (str + '')
            .replace(/\\(.?)/g, function(s, n1) {
              switch (n1) {
                case '\\':
                  return '\\';
                case '0':
                  return '\u0000';
                case '':
                  return '';
                default:
                  return n1;
              }
            });
        }
    
 </script>

<div class="row"> 
    <div id="icon-options-general" class="icon32 col-lg-12">
        <br> </div>
        <div class="col-lg-12">
<h2> <?php echo  __( 'Transmit SMS - Share as Text Message/SMS', 'TSC_trdom' );?> </h2><br>
</div>
<div class="col-lg-6">
    <div class="col-lg-12">
        <div id="postbox-container-2" class="panel panel-info">
            <div id="revisionsdiv" class="panel-heading">
                <span style="float:left; margin:3px 7px 20px 0;" class="fa fa-gear"></span> <span>
                        <?php    echo "<span>" . __( 'Settings', 'TSC_trdom' ) . "</span>"; ?>
            </div>
            <div style="padding:10px" class="panel-body">
                <form name="TSCform" id="TSCform" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
                    <input type="hidden" name="TSC_hidden" value="Y">  
                    <table class="form-table" style="col-lg-12" cellpadding='1'>
                        <tr>
                            <td style="width: 20%;vertical-align: top;"><label for="TSC_apikey"><?php _e("API Key : " ); ?> </label></td>
                            <td><input required="" class="form-control" type="text" name="TSC_apikey" style="width: 100%;" id="TSC_apikey" value="<?php echo $TSCapikey; ?>" ></td>
                        </tr>
                        <tr>
                            <td style="width: 20%;vertical-align: top;"><label for="TSC_apisecret"><?php _e("API Secret : " ); ?></label></td>
                            <td><input required="" class="form-control" type="text" name="TSC_apisecret"  style="width: 100%;" id="TSC_apisecret" value="<?php echo $TSCapiSecret; ?>" >
                            <span style="font-style: italic;"> Get these details from the API settings section of your account.</span></td>
                        </tr>
                        <tr>
                            <td style="width: 20%;vertical-align: top;">&nbsp;</td>
                            <td>
                             <button id="verify" class="button button-primary button-large" type="button" onclick="TSCapiConnect();" accesskey="p" name="verify"><i class="fa fa-plug"></i> VERIFY KEY</button>
                                <img id="verifyLoader"  style="margin-left:10px !important;display:none" src="<?php echo TSC_PLUGIN_URL ?>/assets/images/loading.gif" title="still loading" > 
                            <div id="connectionResult"></div>
                            </td>
                        </tr>
                    
                    
                    </table>
                      
                        
                </form>  
                </div>
                </div>
        </div>
<!----- row 2-->   
   <div class="col-lg-12" id="TSC_optionPanel" style="display: none;">
   <div class="panel panel-info">
        <div class="panel panel-heading">
            <i class="fa fa-gear"></i> Message & Form Style
        </div>
        <div class="panel panel-body" style="padding-top: 0px;">
        <form id="TSC_optionForm" name="TSC_optionForm" />
         <table class="form-table" style="col-lg-12" cellpadding='1'>
            <tr>
                <td style="width: 20%;vertical-align: top;"><label for="TSC_" title="card message">Message</label></td>
                <td>
                    <textarea required="" class="form-control" name="TSC_cardMessage" id="TSC_cardMessage" style="height:110px" ></textarea>
                    <em>Short code :  <?PHP 
                        foreach($TSC_ShortCodeShare as $keyShortCode=>$valShortCode){
                            echo '<kbd style="background-color: #121518 !important;color:#FFF !important">'.$valShortCode.'</kbd> ';
                        }
                     ?></em>
                </td>
                
              </tr>
              <tr>
                <td style="width: 20%;vertical-align: top;"><label for="TSC_" title="Form Code">Form Panel</label></td>
                <td>
                    <textarea class="form-control" id="TSC_FormStyle"  name="TSC_FormStyle" style="height:300px;" >
                    <div class="modal-header alert alert-info">
                        <i class="fa fa-share"></i> Message will be delivered as SMS to:
                    </div>
                    <div class="modal-body" style="padding-bottom:85px">
                         <div class="col-lg-2" style="margin-bottom: 10px;">
                           <select name="TSC_countryCode" id="TSC_countryCode" class="form-control">
                                <option value="AU">+61 AU</option>
                                <option value="NZ">+64 NZ</option>
                                <option value="SG">+65 SG</option>
                                <option value="UK">+44 UK</option>
                                <option value="US">+1 US</option>
                           </select>
                         </div>
                         <div class="col-lg-6" style="margin-bottom: 10px;">
                          <input type="text" placeholder="mobile number" style="width:100%"  name="TSC_mobile" id="TSC_mobile" required="" class="form-control">
                        </div>
                        <div class="col-lg-2" style="margin-bottom: 10px;">
                            <button type="button" name="TSC_sentMessage" id="TSC_sentMessage" class="btn btn-primary" onclick="TSCsendMessage();">Send SMS</button>
                        </div>
                        <div class="col-lg-12" id="TSC_CaptchaContainer">
                        </div>
                    </div>
                    </textarea> <br /> <br />
                    <em>Please keep element ID, element Name and onclick function above </em> &nbsp;&nbsp;&nbsp;&nbsp;
                   <button name="TSC_formPreview" id="TSC_formPreview" style="margin-top: 10px;" class="btn btn-default"><i class="fa fa-binoculars"></i> Preview</button>
                </td>
                
              </tr>
             <tr>
                <td  style="width: 20%;vertical-align: top;"><label for="TSC_urlCode" title="card message">Button Share</label></td>
                <td>
                    <textarea class="form-control" name="TSC_urlCode" id="TSC_urlCode" ><p><button class="btn btn-default" id="TSC_ShareMessage"><i class="fa fa-share"></i> Send to Mobile</button></p></textarea>
                </td>
                
              </tr>
                <tr>
                <td  style="width: 20%;vertical-align: top;">&nbsp;</td>
                <td>
                    <input type="checkbox" class="form-control" name="TSC_captcha" id="TSC_captcha" checked="" />&nbsp;&nbsp;<b>Enable Captcha</b>
                    &nbsp;&nbsp;<input type="checkbox" class="form-control" name="TSC_applyPost" id="TSC_applyPost" checked="" />&nbsp;&nbsp;<b>Show on all pages</b>
                    </td>
              </tr>
                <td style="width: 20%;vertical-align: top;">&nbsp;</td>
                <td><input type="hidden" name="TSC_submitOption" id="TSC_submitOption" value="Y" />
                <img id="submitLoader"  style="margin-left:10px !important;display:none" src="<?php echo TSC_PLUGIN_URL ?>/assets/images/loading.gif" title="still loading" > 
                 <button id="TSC_submitUpdateOption" type="submit" class="button button-large button-primary" style="height:50px"> ADD SHARE BUTTON</button>
                </td>
            </tr>
         </table>
           </form> 
            <div id="submitOptionResult" class="col-lg-10"></div>
        </div>
   </div>
   
             
             </div>
    </div>
<div class="col-lg-5">
    <div id="postbox-container-2" class="panel panel-info">
        <div id="revisionsdiv" class="panel-heading">
            <span style="float:left; margin:3px 7px 20px 0;" class="fa fa-group"></span> <span>
                    <?php    echo "<span>" . __( 'Share Button List', 'TSC_trdom' ) . "</span>"; ?>
        </div>
        <div style="padding:10px" class="panel-body">
        <?PHP
       if(is_array($TSCOption) && sizeof($TSCOption) > 0){
           ?>
           <div id="TSCmessageBoxListShortCode" style="display: none;" /></div>
           <div id="TSCPanelBody">
           <table style="font-size: 12px;" class="table table-striped table-responsive table-hover">
            <thead>
                <tr>
                    <td>Short Code</td>
                    <td>Message</td>
                    <td>Captcha</td>
                    <td>All POST</td>
                    <td>Action</td>
                </tr>
             </thead>
            <tbody>
           <?PHP
            $i = 0;
            foreach($TSCOption as $TSCkey => $TSCVal){
            $i++;
            $messageClearLine = $string = preg_replace('/\s+/', ' ', trim($TSCVal['cardMessage']));
                ?>
                <tr>
                    <td><?=$TSCVal['shortcodelabel'];?></td>
                    <td><?=$TSCVal['cardMessage']?></td>
                    <td><?=$TSCVal['captcha']=='on'?'Yes':'No'?></td>
                       <td><?=$TSCVal['applyToPost']=='on'?'Yes':'No'?></td>
                    <td><i class="fa fa-edit" style="cursor: pointer;" onclick="TSCeditShortCode(<?=$TSCkey?>,'<?=$messageClearLine?>','<?= $TSCVal['formStyle']?>','<?= base64_encode($TSCVal['urlCode'])?>','<?=$TSCVal['captcha']?>','<?=$TSCVal['applyToPost']?>')" /></i>
                                <i onclick="TSCdeleteShortCode(<?=$TSCkey?>,'<?=$TSCVal['shortcodelabel'];?>')" style="cursor: pointer;" class="fa fa-times"></i></td>
                </tr>
                <?PHP
             }
             ?>
             </tbody>
            </table>
             <?PHP
             
        }

   ?>
        </div>
        
        </div>
    </div>
</div>
<!--modal--Edit-->
<div class="modal fade" id="TSC_modalEdit">
  <div class="modal-dialog">
   <form id="TSC_optionFormEdit" name="TSC_optionFormEdit" />
    <div class="modal-content" id="TSC_modalEditContent">
  <div class="modal-header alert alert-info">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title">Edit card</h4>
      </div>
        <div class="modal-body" id="TSCModalBodyEdit">
        <div id="TSCMsgBoxEdit" style="display: none;" /></div>
         <table class="form-table" style="col-lg-12" cellpadding='1'>
            <tr>
                <td style="width: 20%;vertical-align: top;"><label for="TSC_" title="card message">Message</label></td>
                <td>
                    <textarea required="" class="form-control" name="TSC_cardMessageEdit" id="TSC_cardMessageEdit" ></textarea>
                </td>
                
              </tr>
              <tr>
                <td style="vertical-align: top;"><label for="TSC_" title="Form Code">Form Panel</label></td>
                <td>
                    <textarea class="form-control" id="TSC_FormStyleEdit"  name="TSC_FormStyleEdit" style="height:200px;" >
                    </textarea> <br /> <br />
                   <button name="TSC_formPreviewEdit" id="TSC_formPreviewEdit" class="btn btn-default"><i class="fa fa-binoculars"></i> Preview</button>
                </td>
                
              </tr>
            <tr>
             
             <tr>
                <td  style="vertical-align: top;"><label for="TSC_urlCode" title="card message">Button Share</label></td>
                <td>
                    <textarea class="form-control" name="TSC_urlCodeEdit" id="TSC_urlCodeEdit" ></textarea>
                    <input type="hidden" name="TSC_ShortCodeId" id="TSC_ShortCodeId" />
                </td>
              </tr>
              <tr>
                <td  style="vertical-align: top;"></td>
                <td>
                    <input type="checkbox" class="form-control" name="TSC_captchaEdit" id="TSC_captchaEdit" />&nbsp;&nbsp;<b>Enable Captcha</b>
                    &nbsp;&nbsp; <input type="checkbox" class="form-control" name="TSC_applyPostEdit" id="TSC_applyPostEdit" checked="" />&nbsp;&nbsp;<b>Apply to all POST</b>
                    
                    </td>
              </tr>
              
              
         </table>
         </div>
    <div class="modal-footer">
    <img id="submitLoaderEdit"  style="margin-left:10px !important;display:none" src="<?php echo TSC_PLUGIN_URL ?>/assets/images/loading.gif" title="still loading" > 
                
        <button type="button" class="btn btn-warning" onclick="jQuery('#TSCMsgBoxEdit').fadeOut('fast');" data-dismiss="modal"><i class="fa fa-times"></i> CLOSE</button>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> SAVE CHANGES</button>
      </div>
      
    </div><!-- /.modal-content -->
           </form> 
     
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!--modal--preview-->
<div class="modal fade" id="TSC_modalPreview">
  <div class="modal-dialog">
    <div class="modal-content" id="TSC_modalPreviewContent">
     
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
    <?php
    $FORM = ob_get_contents();
    ob_end_clean();
    
   return $FORM;
  
}
?>