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
          duda()->selectTemplate( $_GET['id'] );          
        }
        break;

      case 'redirect_to_duda_editor':
        duda()->redirect_to_duda_editor();
        break;
    }
  }
} );