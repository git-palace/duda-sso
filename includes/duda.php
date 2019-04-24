<?php
class Duda {  
  private static $instance = null;

  // generate api header
  private function getAPIHeader() {
    if ( !defined( 'DUDA_USERNAME' ) || !defined( 'DUDA_PASSWORD' ) ) {
      error_log( 'DUDA API credentials are not configured.' );
      return false;
    }

    return 'Authorization:' . base64_encode( DUDA_USERNAME . ':' . DUDA_PASSWORD );
  }

  static function getInstance() {
    if ( is_null( self::$instance ) ) {
      self::$instance = new Duda();
    }

    return self::$instance;
  }

  // constructor
  function __construct() {
  }

  // get template
  function getTemplates() {
    return 'dfadsfa sd';
  }
}