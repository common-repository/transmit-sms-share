<?php
//ajax handle
if(isset($_POST['TSCgetConnect']) && $_POST['TSCgetConnect']== 'Y' && $_GET['page'] == 'transmitSMSSC'){
    require_once TSC_PLUGIN_DIR .'/APIClient2.php';
    $apikey = trim($_POST['apikey']);
    $secret = trim($_POST['secret']);
    $api=new TSCtransmitsmsAPI($apikey,$secret);
    $result=$api->getLists(1,100);
    if($result->error->code=='SUCCESS'){
        $opt = array('apikey'=>base64_encode($apikey),'secret'=>base64_encode($secret));
        update_option(TSCWpOptionApi, addslashes(serialize($opt)));
       echo 'SUCCESS';
    }else{
        delete_option(TSCWpOptionApi);
        echo $result->error->description;
    }
    
    exit();
 
}
if(isset($_POST['TSC_submitOption']) && $_POST['TSC_submitOption'] == 'Y' && $_GET['page'] == 'transmitSMSSC'){
    //delete_option(TSCWpOption);
    $TSC_cardMessage = trim($_POST['TSC_cardMessage']);
    $TSC_FormStyle = trim($_POST['TSC_FormStyle']);
    $TSC_urlCode = trim($_POST['TSC_urlCode']);
    if(isset($_POST['TSC_captcha'])){
        $TSC_captcha = $_POST['TSC_captcha'];
    }else  $TSC_captcha = '';
     if(isset($_POST['TSC_applyPost'])){
        $TSC_applyPost = $_POST['TSC_applyPost'];
    }else  $TSC_applyPost = '';
    
    $TSCOption = unserialize(@get_option(TSCWpOption));
    $arrOption = array();$arrNewOption= array();
    if(is_array($TSCOption )){
        $shortCode = TSCShortCode;
        //$newIndex = (int)sizeof($TSCOption);
        $newIndex = (int)max(array_keys($TSCOption)) + 1;
        $TSCOption[$newIndex] = array('cardMessage'=>$TSC_cardMessage,'formStyle'=>base64_encode($TSC_FormStyle),'urlCode'=>$TSC_urlCode,'captcha'=>$TSC_captcha,'applyToPost'=>$TSC_applyPost);
        $TSCOption[$newIndex]['shortcodelabel'] = '['.$shortCode.' code='.$newIndex.']';
        $TSCOption[$newIndex]['shortcode'] = $shortCode;
        $arrNewOption =  $TSCOption;
    
    }else {
        $arrOption[0] = array('shortcode'=>'[TSC_SHARECARD]','cardMessage'=>$TSC_cardMessage,'formStyle'=>base64_encode($TSC_FormStyle),'urlCode'=>$TSC_urlCode,'captcha'=>$TSC_captcha,'applyToPost'=>$TSC_applyPost);
        $shortCode = TSCShortCode;
        $arrNewOption[0] = $arrOption[0];
        $arrNewOption[0]['shortcodelabel'] = '['.$shortCode.' code=0]';
        $arrNewOption[0]['shortcode'] = $shortCode;
    }
    update_option(TSCWpOption, serialize($arrNewOption));
    echo   'SUCCESS';    
    exit();
}
if(isset($_POST['TSCEditCard']) && $_POST['TSCEditCard'] == 'Y' && $_GET['page'] == 'transmitSMSSC'){
    //delete_option(TSCWpOption);
    $TSC_cardMessage = trim($_POST['TSC_cardMessageEdit']);
    $TSC_FormStyle = trim($_POST['TSC_FormStyleEdit']);
    $TSC_urlCode = trim($_POST['TSC_urlCodeEdit']);
    $TSC_ShortCodeId = (int)$_POST['TSC_ShortCodeId'];
    $TSC_captcha = '';
    $TSC_applyPost = '';
    if(isset($_POST['TSC_captchaEdit'])){
        $TSC_captcha = $_POST['TSC_captchaEdit'];
    }
    if(isset($_POST['TSC_applyPostEdit'])){
        $TSC_applyPost = $_POST['TSC_applyPostEdit'];
    }
    $TSCOption = unserialize(@get_option(TSCWpOption));
    $TSCOption[$TSC_ShortCodeId] = array('shortcode'=>'[TSC_SHARECARD]','cardMessage'=>$TSC_cardMessage,'formStyle'=>base64_encode($TSC_FormStyle),'urlCode'=>$TSC_urlCode,'captcha'=>$TSC_captcha,'applyToPost'=>$TSC_applyPost);
    $shortCode = TSCShortCode;
    $TSCOption[$TSC_ShortCodeId]['shortcodelabel'] = '['.$shortCode.' code='.$TSC_ShortCodeId.']';
    $TSCOption[$TSC_ShortCodeId]['shortcode'] = $shortCode;
    
    update_option(TSCWpOption, serialize($TSCOption));
    echo   'SUCCESS';    
    exit();
}

if(isset($_POST['TSCDeleteCard']) && $_POST['TSCDeleteCard'] == 'Y' && $_GET['page'] == 'transmitSMSSC'){
    //delete_option(TSCWpOption);
    $TSC_ShortCodeId = (int)$_POST['shortCodeIndex'];
    $TSCOption = unserialize(@get_option(TSCWpOption));
    unset($TSCOption[$TSC_ShortCodeId]);
    update_option(TSCWpOption, serialize($TSCOption));
    echo   'SUCCESS';    
    exit();
}
if(isset($_POST['TSCrenderShortCode']) && $_POST['TSCrenderShortCode'] == 'Y' && $_GET['page'] == 'transmitSMSSC'){    
    $TSCOption = unserialize(@get_option(TSCWpOption));
    if(is_array($TSCOption) && sizeof($TSCOption) > 0){
           ?>
           <table style="font-size: 12px;" class="table table-striped table-responsive table-hover">
            <thead>
                <tr>
                    <td>No</td>
                    <td>Short Code</td>
                    <td>Card Message</td>
                    <td>Captcha</td>
                    <td>All Post</td></td>
                    <td>Action</td>
                </tr>
             </thead>
            <tbody>
           <?PHP
            $i = 0;
            foreach($TSCOption as $TSCkey => $TSCVal){
                $messageClearLine = $string = preg_replace('/\s+/', ' ', trim($TSCVal['cardMessage']));
                $i++;
                ?>
                <tr>
                    <td><?= $i; ?></td>
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
    exit();
}

?>
