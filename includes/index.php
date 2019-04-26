<?php
require_once( 'duda.php' );

function duda() {
  return Duda::getInstance();
}
add_action( 'init', function() {
  if ( isset( $_GET['action'] ) ) {
    switch( $_GET['action'] ) {
      case 'duda_tpl_select':
        if ( isset( $_GET['id'] ) && !empty( $_GET['id']) ) {
          $site_name = duda()->selectTemplate( $_GET['id'] );

          if ( empty( $site_name ) ) {
            error_log( "Error Occured when create site. Template ID ===> " . $_GET['id'] );
            return;
          }

          $url = duda()->createCustomerAcct( $site_name );
          
          if ( !empty( $url ) ) {
            wp_redirect( $url );
            exit;
          }
          
        }
        break;
      
    }
  }
} );