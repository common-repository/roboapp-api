<?php
/*
Plugin Name: Robo-Apps Wordpress API
Plugin URI: http://robo-apps.com
Description: 
Author: PentaValue Company
Version: 1.0
Author URI: http://robo-apps.com
*/
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);
@ini_set('display_errors',1);

define('ARTAPPCR_DIR', plugin_dir_path(__FILE__));
define('ARTAPPCRVERSION', 1.0);

require(ARTAPPCR_DIR.'/class.api.php');

register_activation_hook(__FILE__, 'artappcr_install');
register_uninstall_hook(__FILE__, 'artappcr_uninstall');

add_action('template_redirect', 'artappcr_start');

function artappcr_start(){
  $artappcr_version = get_option('artappcr_version');
  if($artappcr_version != ARTAPPCRVERSION){
    artappcr_upgrade($artappcr_version);
  }
  if(!empty($_REQUEST['Do'])){
    if($_REQUEST['Do'] == 'appcrapi'){
      $artappcr_api = new artappcr_api();
      die();
    }
  }
}

function artappcr_install(){
  if(get_option('artappcr_version') > 0){
    return;
  }
  add_option('artappcr_version', ARTAPPCRVERSION);
}

function artappcr_uninstall(){
  global $wpdb;
  if(is_multisite()){
    $blogs = $wpdb->get_results("SELECT blog_id FROM $wpdb->blogs");
    if($blogs){
      foreach($blogs as $blog){
        switch_to_blog($blog->blog_id);
        artappcr_uninstall_code();
      }
      restore_current_blog();
    }
  }
  else{
    artappcr_uninstall_code();
  }
}

function artappcr_uninstall_code(){
  delete_option('artappcr_version');
}

function artappcr_upgrade($version){
}

?>
