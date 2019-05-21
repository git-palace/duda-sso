<?php
require_once( 'duda.php' );

function duda() {
  return Duda::getInstance();
}

function get_duda_subscription_addons() {
  if ( !defined( DUDA_SUBSCRIPTION_PRODUCT_ADDONS ) ) return [];

  if ( !is_array( DUDA_SUBSCRIPTION_PRODUCT_ADDONS ) ) return [];

  if ( empty( DUDA_SUBSCRIPTION_PRODUCT_ADDONS ) ) return[];
  
  return DUDA_SUBSCRIPTION_PRODUCT_ADDONS;
}

add_action( 'init', function() {
  if ( isset( $_GET['action'] ) ) {
    switch( $_GET['action'] ) {
      case 'duda_tpl_select':
        if ( isset( $_GET['id'] ) && !empty( $_GET['id']) ) {
          duda()->selectTemplate( $_GET['id'] );          
        }
        break;

      case 'redirect_to_duda_editor':
        duda()->redirect_to_duda_editor();
        break;
    }
  }
} );