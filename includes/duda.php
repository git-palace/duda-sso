<?php
class Duda {  
  private static $instance = null;

  // send get request
  protected function curl_request( $api_endpoint, $method = 'GET', $body = array() ) {
    $ch = curl_init();

    curl_setopt( $ch, CURLOPT_URL, DUDA_APIENDPOINT . $api_endpoint );
    
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1) ;
    
    curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method );

    if ( $method == 'POST' )
      curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $body ) );

    curl_setopt( $ch, CURLOPT_USERPWD, DUDA_APIUSERNAME . ':' . DUDA_APIPASSWORD );

    $headers = array();
    $headers[] = 'Content-Type: application/json';
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

    $response = curl_exec( $ch );
    if ( curl_errno( $ch ) ) {
        error_log( curl_error( $ch ) );

        return false;
    }
    curl_close ($ch);

    return json_decode( $response, true );
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
    $response = $this->curl_request( '/sites/multiscreen/templates' );

    return $response;
  }

  // select template
  function selectTemplate( $tpl_id = null ) {
    if ( empty( $tpl_id ) )
      return;
    
    $response = $this->curl_request( '/sites/multiscreen/create', 'POST', [ 'template_id' => $tpl_id ] );
    
    if ( is_wp_error( $response ) || !array_key_exists( 'site_name', $response ) ){
      error_log( "Error occured when create site with template id: " . $tpl_id );
      return false;
    }
    
    return $response['site_name'];
  }

  // create customer account
  function createCustomerAcct( $site_name = null) {
    $current_user = wp_get_current_user();

    $response = $this->curl_request( '/accounts/create', 'POST', ['account_name'  => $current_user->user_email] );
    
    if ( is_wp_error( $response ) ) {
      return false;
    }

    $response = $this->curl_request( sprintf( '/accounts/%s/sites/%s/permissions', $current_user->user_email, $site_name ), 'POST', [
      'permissions' => ['PUSH_NOTIFICATIONS','REPUBLISH','EDIT','INSITE','PUBLISH','CUSTOM_DOMAIN','RESET','SEO','STATS_TAB','BLOG']
    ] );
    
    if ( is_wp_error( $response ) ) {
      return false;
    }

    $duda_sso_token = get_user_meta( get_current_user_id(), 'duda_sso_token', true );

    if ( empty( $duda_sso_token ) ) {
      $response = $this->curl_request( sprintf( '/accounts/sso/%s/token', $current_user->user_email ) );
  
      if ( is_wp_error( $response ) || !array_key_exists( 'url_parameter', $response ) ) {
        error_log( "Error occured when generate SSO Token" );
        return false;
      }
  
      $duda_sso_token = $response['url_parameter']['name'] . '=' . $response['url_parameter']['value'];
      update_user_meta( get_current_user_id(), 'duda_sso_token', $duda_sso_token );
    }

    return DUDA_SSO_ENDPOINT . '/editor/d1?reset=' . $site_name . '&' . $duda_sso_token;
  }
}