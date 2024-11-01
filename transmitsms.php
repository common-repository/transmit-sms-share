<?php
/*
Plugin Name: Transmit SMS Share 
Plugin URI: 
Description: Share content, article, card via SMS through Transmit SMS
Version: 1.8
Author:  Transmit SMS
Author URI: 
*/
define( 'TSC_VERSION', '1.8' );
define( 'TSC_REQUIRED_WP_VERSION', '3.5' );
global $pagenow;
if ( ! defined( 'TSC_PLUGIN_BASENAME' ) )
	define( 'TSC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
if ( ! defined( 'TSC_PLUGIN_NAME' ) )
	define( 'TSC_PLUGIN_NAME', trim( dirname(TSC_PLUGIN_BASENAME), '/' ));
if ( ! defined( 'TSC_PLUGIN_DIR' ) )
	define( 'TSC_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
if ( ! defined( 'TSC_PLUGIN_URL' ) )
	define( 'TSC_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
if ( ! defined( 'TSC_PLUGIN_MODULES_DIR' ) )
	define( 'TSC_PLUGIN_MODULES_DIR', TSC_PLUGIN_DIR . '/modules' );

if ( ! defined( 'TS2_LOAD_JS' ) )
	define( 'TSC_LOAD_JS', true );

if ( ! defined( 'TS2_LOAD_CSS' ) )
	define( 'TS2_LOAD_CSS', true );

if ( ! defined( 'TS2_AUTOP' ) )
	define( 'TSC_AUTOP', true );

if ( ! defined( 'TSC_USE_PIPE' ) )
	define( 'TSC_USE_PIPE', true );
    
    
if(!defined('TSC_successVerify'))
    define('TSC_successVerify',"Your key has been verified successfully");
if(!defined('TSC_failVerify'))
    define('TSC_failVerify',"Sorry..api key and secret you entered  still invalid");
if(!defined('TSC_successSubmit'))
    define('TSC_successSubmit',"Card has been saved");
if(!defined('TSC_successcardSent'))
    define('TSC_successcardSent',"Shared as SMS to ");
if(!defined('TSC_failSubmit'))
    define('TSC_failSubmit',"oops sorry something went wrong please try again later");
if(!defined('TSC_phoneFormatWrong'))
    define('TSC_phoneFormatWrong',"sorry you have entered wrong phone number");
if(!defined('TSC_WrongCaptcha'))
    define('TSC_WrongCaptcha',"Sorry, the code entered was incorrect. Please try again.");
define('TSCShortCode','TSCSHARE');
define('TSCWpOption','TSCSmsSettings'); 
define('TSCWpOptionApi','TSCSmsSettingsApi');
define('TSC_BurstSMSList','Wordpress 2FA');
$TSC_ShortCodeShare = array('[ARTICLE TITLE]','[ARTICLE LINK]');

//register_activation_hook(__FILE__, 'TSCctivatePlugin');
register_uninstall_hook(__FILE__, 'TSCdeletePlugin');

require_once TSC_PLUGIN_DIR . '/settings.php';

function TSCdeletePlugin(){
    delete_option('TSCSmsSettings');
    delete_option('TSCSmsSettingsApi');
 }

if ( 'plugins.php' === $pagenow )
{
    // Better update message
    $file   = basename( __FILE__ );
    $folder = basename( dirname( __FILE__ ) );
    $hook = "in_plugin_update_message-{$folder}/{$file}";
    add_action( $hook, 'your_update_message_cb', 20, 2 );
}

function your_update_message_cb( $plugin_data, $r )
{
    // readme contents
    $data       = file_get_contents( 'https://plugins.svn.wordpress.org/transmit-sms-share/trunk/readme.txt' );
    $arrUpgardeNotice = explode('Upgrade notice',$data);
    $upgradeNotice= trim($arrUpgardeNotice[1]);
    $upgradeNotice = substr($upgradeNotice,2);
    if($separateString =  strpos($upgradeNotice,'==') !== false){
        if($separateString > 5){
            $upgradeNotice =  substr($upgradeNotice,0,$separateString);
        }
    }
	$upgradeNotice = str_replace('=','',$upgradeNotice);
    $output = '<div style="margin-top:10px" class="alert alert-info"><i class="fa fa-info-circle fa-lg"></i> '.$upgradeNotice.'</div>';
    return print $output;
}
?>
