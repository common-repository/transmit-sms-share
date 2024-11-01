<?PHP
class TransmitSareCard{
    public $TSCOption; 
    public function __construct() {
        $this->register_session();
       // indicates we are running the admin
        if ( is_admin() ) {
          require_once TSC_PLUGIN_DIR . '/admin/admin-controller.php';  
          require_once TSC_PLUGIN_DIR . '/admin/admin.php'; 
        }else{
            if($arrOption = get_option(TSCWpOption)){
                $this->TSCOption = unserialize($arrOption);
                //render to apply all post
                unset($_SESSION['TSC_SHORTCODEIDTEMPFORALLPOST']);
                $setAllPost = false;
                foreach($this->TSCOption as $keyOption =>$valOption){
                     if(isset($this->TSCOption[$keyOption]['applyToPost']) && $this->TSCOption[$keyOption]['applyToPost'] == 'on'){
                        $_SESSION['TSC_SHORTCODEIDTEMPFORALLPOST'][] = $keyOption;
                        $setAllPost = true;
                    }
                }
                if($setAllPost){
                        add_filter( 'the_content', array(&$this,'addShortCodeToPost'));
                }
                
                add_shortcode(TSCShortCode,array(&$this,'TSC_shortCodeHandle'));
                add_filter('wp_head', array(&$this,'addHeadScript'));
                if(isset($_POST['TSCsentCard']) && $_POST['TSCsentCard']=='Y'){
                    $this->TSCsendcard();
                    exit();
                }elseif(isset($_POST['TSCsentGenereteCaptcha']) && $_POST['TSCsentGenereteCaptcha'] =='Y'){
                    echo $this->genereteCaptcha();
                    exit();
                }
                
            }
        }
  }
  
    public function addShortCodeToPost($content){
        $shortCodeIdArr = $_SESSION['TSC_SHORTCODEIDTEMPFORALLPOST'];
        foreach($shortCodeIdArr as $keySh =>$valSh){
            $content .= "  [TSCSHARE code=$valSh]"; 
        }
        return $content;
    }
    public function register_session(){
        if( !session_id() )
            session_start();
    }
    public function TSCsendcard(){
        global $TSC_ShortCodeShare;
        require_once TSC_PLUGIN_DIR . '/APIClient2.php';
        $mobilePhone = trim($_POST['mobile']);  
        $shortCodeId =  trim($_POST['shortCodeId']);  
        $countryCode = trim($_POST['country']);
        $captchaCode = trim($_POST['captchaCode']);   
        $postId = (int)$_POST['postId'];  
        if($captchaCode !== 'no'){
            //checking captcha
            if(strtolower($_SESSION['TSC_captcha']['code']) !== strtolower($captchaCode) ){
                echo 'WRONG_CAPTCHA';
                exit();
            }
        }
        $WBSms = unserialize(stripslashes(get_option(TSCWpOptionApi)));
        $transmitSmsApiKey = base64_decode($WBSms['apikey']);
        $transmitSmsApiSecret = base64_decode($WBSms['secret']);
        //sent sms
        $WBmsAPI = new TSCtransmitsmsAPI($transmitSmsApiKey, $transmitSmsApiSecret);
        //reformating number
        $formatTonumber = $WBmsAPI->formatNumber($mobilePhone,$countryCode);
        $newResult = array();
        if(@$formatTonumber->error->code == 'SUCCESS') {
           $mobilePhone =  $formatTonumber->number->international;
         }else {
            $newResult['error'] = 'WRONG_FORMAT';
            $newResult['mobile'] =  $mobilePhone;
        }  
        
        $textMessage = $this->TSCOption[$shortCodeId]['cardMessage'] ;
       //replace with shortcode
        //$postTitle =  get_the_title($postId) ;
        //$postUrl = get_site_url($postId); 
		$page =  get_page($postId);
        $postTitle =  $page->post_title;
		$postUrl = $page->guid;
        
        $textMessage = str_replace($TSC_ShortCodeShare,array($postTitle,$postUrl),$textMessage);
        $result=$WBmsAPI->sendSms($textMessage, trim($mobilePhone));  
        $newResult['mobile'] =  $mobilePhone;
        if(@$result->error->code=='SUCCESS'){ 
            $newResult['error'] = 'SUCCESS';
        }else{
           $newResult['error'] = $result->error->description;  
        } 
        $newResult['mobile'] =  $mobilePhone;
        echo json_encode($newResult);
        exit();
    }
     
  
   public function TSC_shortCodeHandle($attr) {
        global $post;
	   $shortCodeId = (int)$attr['code'];
       $shortCodeForm = $this->TSCOption[$shortCodeId]['formStyle'];
       //replace captcha
       $shortCodeForm =  stripcslashes(base64_decode($shortCodeForm));
       if(isset($this->TSCOption[$shortCodeId]['captcha']) && $this->TSCOption[$shortCodeId]['captcha'] == 'on'){
         $withCaptcha = 'yes';
       }else $withCaptcha = 'no';
       $shortCodeForm = base64_encode($shortCodeForm);
       $buttonShareContennt = stripcslashes($this->TSCOption[$shortCodeId]['urlCode']);
       $buttonShare = str_replace('id="TSC_ShareMessage"','id="TSC_ShareMessage'.$shortCodeId.'"  onclick="TSCShareCardAction('.$shortCodeId.',\''.$shortCodeForm.'\',\''.$post->ID.'\',\''.$withCaptcha.'\')"',$buttonShareContennt);
       add_filter('wp_footer', array(&$this,'addFooterScript'));
       return $buttonShare;
    }
    
    public function genereteCaptcha(){
        include_once TSC_PLUGIN_DIR . '/assets/simple-php-captcha-master/simple-php-captcha.php';  
        $_SESSION['TSC_captcha'] = simple_php_captcha(); 
        $captchaImg = '<div class="col-lg-4"><img src="' . $_SESSION['TSC_captcha']['image_src'] . '" alt="CAPTCHA code"></div>';
        $captchaImgInputText = '<div class="col-lg-6" style="margin-top:15px"> Enter Code*:<input required="" type="text" name="TSC_captchaCode" id="TSC_captchaCode" class="form-control" style="width:120px" /><input type="hidden" id="TSC_useCaptcha" value="Y" name="TSC_useCaptcha" /> </div>';
        return $captchaImg.$captchaImgInputText;
   }
    public function addHeadScript(){
        wp_enqueue_style( 'fontAwesome', TSC_PLUGIN_URL . '/assets/font-awesome-4.2.0/css/font-awesome.css' );
        wp_register_script('bootstrapJs',  TSC_PLUGIN_URL . '/assets/js/bootstrap.js', false, null);
        wp_enqueue_script('bootstrapJs');
        return;
    }
    public function addFooterScript(){
        $panelTitle = 'Share via SMS';
        
        wp_enqueue_style( 'bootsrapCSS', TSC_PLUGIN_URL . '/assets/css/bootstrap.css' );
        require_once TSC_PLUGIN_DIR . '/assets/simple-php-captcha-master/simple-php-captcha.php';
        ob_start();
        ?>
        <script type="text/javascript">
            function TSCsendMessage(){
                    var mobileNumber = jQuery('#TSC_mobile').val();
                    var shortCodeId = jQuery('#TSCShortCodeId').val(); 
                    var countryCode = jQuery('#TSC_countryCode').val();
                    var useCaptcha = jQuery('#TSC_useCaptcha').val();
                    captchaCode = 'no';
                    if(useCaptcha == 'Y'){
                       captchaCode = jQuery('#TSC_captchaCode').val();
                    }
                     var postId= jQuery('#TSCPostId').val();
                     jQuery.ajax({
                           url: '<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>',
                           type:'POST',
                           data:'TSCsentCard=Y&shortCodeId=' + shortCodeId + '&mobile=' + mobileNumber +'&country='+countryCode + '&postId='+postId+'&captchaCode='+captchaCode,
                           beforeSend:function(){
                               jQuery('#TSCShareCardForm').html('<div class="modal-header alert alert-info"><i class="fa fa-share"></i> <?= $panelTitle ?> </div><div class="modal-body"><img tyle="margin-left:10px !important" src="<?php echo TSC_PLUGIN_URL ?>/assets/images/loading.gif" title="still loading" ></div>');
                            },
                           success: function(result){
                            var arrResult = jQuery.parseJSON(result);
                                //jQuery("#TSCSendMEssageLoader").fadeOut('fast');
                                if(arrResult.error == 'SUCCESS'){
                                    jQuery('#TSCShareCardForm').html('<div class="modal-header alert alert-info"><i class="fa fa-share"></i> <?= $panelTitle ?> </div><div class="modal-body"><div class="alert alert-success"> <a href="#" onclick="jQuery(\'#TSCModal\').modal(\'hide\');" class="close" data-dismiss="alert">&times;</a><?=TSC_successcardSent?> '+ arrResult.mobile +'</div></div>');
                                }else if(arrResult.error == 'WRONG_FORMAT'){
                                    jQuery('#TSCShareCardForm').html('<div class="modal-header alert alert-info"><i class="fa fa-share"></i> <?= $panelTitle ?> </div><div class="modal-body"><div class="alert alert-danger"> <a href="#" onclick="jQuery(\'#TSCModal\').modal(\'hide\');" class="close" data-dismiss="alert">&times;</a><?= TSC_phoneFormatWrong ?></div></div>');
                                }else if(arrResult.error == 'WRONG_CAPTCHA'){
                                    jQuery('#TSCShareCardForm').html('<div class="modal-header alert alert-info"><i class="fa fa-share"></i> <?= $panelTitle ?> </div><div class="modal-body"><div class="alert alert-danger"> <a href="#" onclick="jQuery(\'#TSCModal\').modal(\'hide\');" class="close" data-dismiss="alert">&times;</a><?= TSC_WrongCaptcha ?></div></div>');
                               }else {
                                    jQuery('#TSCShareCardForm').html('<div class="modal-header alert alert-info"><i class="fa fa-share"></i> <?= $panelTitle ?> </div><div class="modal-body"><div class="alert alert-danger"> <a href="#" onclick="jQuery(\'#TSCModal\').modal(\'hide\');" class="close" data-dismiss="alert">&times;</a>'+arrResult.error+'</div></div>');
                                }
                            }
		              });
                      return false;
               }
            function TSCShareCardAction(shortCodeId,shortCodeForm,postID,captcha){
                var formContent = stripslashes(decode_base64(shortCodeForm)) + '<input type="hidden" name="TSCShortCodeId" id="TSCShortCodeId" value="'+shortCodeId+'" /><input type="hidden" name="TSCPostId" id="TSCPostId" value="'+postID+'" />';
                //generete captcha
                if(captcha == 'yes'){
                    formContent = formContent.replace('padding-bottom:85px','padding-bottom:165px');
                     jQuery.ajax({
                       url: '<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>',
                       type:'POST',
                       data:'TSCsentGenereteCaptcha=Y',
                       beforeSend:function(){
                              jQuery('#TSC_CaptchaContainer').html('<img id="genereteCaptchaLoader"  style="margin-left:10px !important;" src="<?php echo TSC_PLUGIN_URL ?>/assets/images/loading.gif" title="still loading" >');
                         },
                       success: function(result){
                            jQuery('#TSC_CaptchaContainer').html(result);
                        },
                    });
                }
                   jQuery('#TSCShareCardForm').html(formContent);
                   jQuery('#TSCModal').modal('show');
                
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
        <div class="modal fade"  id="TSCModal">
        <div class="modal-dialog">
        <div class="modal-content" id="TSCShareCardForm" >
        </div>
        </div>
        </div>
        <?PHP
        $content = ob_get_contents();
        ob_end_clean();
        echo $content;
    }
  
}
