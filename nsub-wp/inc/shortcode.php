<?php

add_shortcode("nsub_login", function ($atts) {
  ob_start();
  $c = get_site_option("nsub_page_advanced");
  require_once __DIR__ . "/template/btn-login.tpl";
  if( !empty($atts['show_logout']) ){
    require_once __DIR__ . "/template/btn-logout.tpl";
    return sprintf(ob_get_clean() , !empty($c["login_button_title"]) ? $c["login_button_title"] : "Login with NEAR" ,
    !empty( $c["logout_button_title"]) ? $c["logout_button_title"] : "Logout"  ) ;
  }
  return sprintf(ob_get_clean() , !empty($c["login_button_title"]) ? $c["login_button_title"] : "Login with NEAR") ;
}); //nsub login 

//nsub content lock shortcode
add_shortcode("nsub_lock", function ($atts, $content = null) {
  ob_start();?>
  <div id="nsub_content_wrap">
    <?php require_once __DIR__ . "/template/btn-pay.tpl" ?>
  </div>
<?php 
  $c = get_site_option("nsub_page_main");
  return sprintf(ob_get_clean(), !empty($c["unlock_button_title"]) ? $c["unlock_button_title"] : "Unlock") ;
});

//nsub donate 
add_shortcode("nsub_donate" , function( $atts){
  ob_start();
  require_once __DIR__ . "/template/btn-donate.tpl";
  return sprintf(ob_get_clean() , 
    !empty( $atts['amount']) ? 'display:none' : '' ,
    !empty( $atts['label']) ? $atts['label'] : '' ,
    !empty( $atts['amount']) ? floatval(trim($atts['amount'])) : '', 
    !empty($atts['receiver'] ) ? trim($atts['receiver']) : nsub_get_receiver(),
    !empty($atts['thankyou'] ) ? trim($atts['thankyou']) : '',
    !empty($atts['title'] ) ? trim($atts['title']) : 'Donate NEAR'
  ) ;
});