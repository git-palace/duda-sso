<?php
class Duda {  
  private static $instance = null;

  // send get request
  protected function __http_get( $api_url ) {
    $ch = curl_init();

    curl_setopt( $ch, CURLOPT_URL, DUDA_APIENDPOINT . $api_url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1) ;
    curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );

    curl_setopt( $ch, CURLOPT_USERPWD, DUDA_APIUSERNAME . ':' . DUDA_APIPASSWORD );

    $headers = array();
    $headers[] = 'Content-Type: application/json';
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

    $result = curl_exec( $ch );
    if ( curl_errno( $ch ) ) {
        error_log( curl_error( $ch ) );

        return false;
    }
    curl_close ($ch);

    return json_decode( $result, true );
  }

  // get API

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
    // $result = $this->__http_get( '/sites/multiscreen/templates' );
    $result = wp_remote_get( 
      DUDA_APIENDPOINT . '/sites/multiscreen/templates', [ 
      'headers' => [ 
        'Authorization' => 'Basic ' . base64_encode( DUDA_APIUSERNAME . ':' . DUDA_APIPASSWORD),
        'Content-Type'  => 'application/json'
        ] 
      ] 
    );

    if ( is_wp_error( $result ) ) {
      echo '<pre>';
      print_r( $result );
      echo '</pre>';
      exit;
    }


    return $result;
  }

  // select template
  function selectTemplate( $tpl_id = null ) {
    if ( empty( $tpl_id ) )
      return;
    

  }
}