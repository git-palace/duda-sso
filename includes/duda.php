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
        // error_log( curl_error( $ch ) );

        return false;
    }
    curl_close ($ch);

    return json_decode( $response, true );
  }

  static function getInstance() {
    if ( is_null( self::$instance ) ) {
      self::$instance = new Duda();
    }

    return self::$instance;
  }

  // constructor
  function __construct() {
    add_action( 'woocommerce_subscription_status_active', [$this, 'duda_subscription_complete'], 10, 1 );
    add_action( 'woocommerce_subscription_payment_complete', [$this, 'duda_subscription_complete'], 10, 1 );
    add_action( 'woocommerce_subscription_renewal_payment_complete', [$this, 'duda_subscription_complete'], 10, 1 );

    add_action( 'woocommerce_subscription_status_pending-cancel', [$this, 'duda_subscription_failed'], 10, 1 );
    add_action( 'woocommerce_subscription_status_cancelled', [$this, 'duda_subscription_failed'], 10, 1 );
    add_action( 'woocommerce_subscription_status_expired', [$this, 'duda_subscription_failed'], 10, 1 );
    add_action( 'woocommerce_subscription_status_on-hold', [$this, 'duda_subscription_failed'], 10, 1 );
    add_action( 'woocommerce_subscription_payment_failed', [$this, 'duda_subscription_failed'], 10, 1 );
    add_action( 'woocommerce_subscription_renewal_payment_failed', [$this, 'duda_subscription_failed'], 10, 1 );
  }

  function duda_subscription_complete( $subscription ) {
    $site_name = $subscription->get_meta( 'site_name' );
    $initial_subscription = $subscription->get_meta('_subscription_resubscribe');

    if ( empty( $site_name ) && !empty( $initial_subscription)) {
      $initial_subscription = wc_get_order( $initial_subscription );

      $site_name = $initial_subscription->get_meta( 'site_name' );
    }

    $user_email = $subscription->get_billing_email();

    $this->createCustomerAcct( $site_name, $user_email, true );
    $this->set_site_publish_mode( $site_name, true );
  }

  function duda_subscription_failed( $subscription ) {
    if ( is_int( $subscription ) )
      $subscription = wc_get_order( $subscription );
    
    $site_name = $subscription->get_meta( 'site_name' );
    $initial_subscription = $subscription->get_meta('_subscription_resubscribe');

    if ( empty( $site_name ) && !empty( $initial_subscription)) {
      $initial_subscription = wc_get_order( $initial_subscription );

      $site_name = $initial_subscription->get_meta( 'site_name' );
    }
    
    $user_email = $subscription->get_billing_email();

    $this->deleteCustomerAcct( $site_name, $user_email );
    $this->set_site_publish_mode( $site_name, false );
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
      // error_log( "Error occured when create site with template id: " . $tpl_id );
      return false;
    }    
    
    WC()->cart->empty_cart();
    WC()->cart->add_to_cart( DUDA_SUBSCRIPTION_PRODUCT_ID );
    
    wp_redirect( esc_url( add_query_arg( 'site_name', $response['site_name'], wc_get_checkout_url() ) ) );
    exit;
  }

  // create customer account
  function createCustomerAcct( $site_name = null, $user_email = null, $is_completed = true ) {
    $response = $this->curl_request( '/accounts/create', 'POST', ['account_name'  => $user_email] );
    
    if ( is_wp_error( $response ) ) {
      return false;
    }

    $response = $this->curl_request( sprintf( '/accounts/%s/sites/%s/permissions', $user_email, $site_name ), 'POST', [
      'permissions' => $is_completed ? ['PUSH_NOTIFICATIONS','REPUBLISH','EDIT','INSITE','PUBLISH','CUSTOM_DOMAIN','RESET','SEO','STATS_TAB','BLOG'] : ['PUSH_NOTIFICATIONS', 'EDIT', 'STATS_TAB']
    ] );
    
    if ( is_wp_error( $response ) ) {
      return false;
    }

    return true;
  }

  // delete customer account
  function deleteCustomerAcct( $site_name = null, $user_email = null ) {
    if ( empty( $site_name ) || empty( $user_email ) )
      return;
    
    $response = $this->curl_request( sprintf( '/accounts/%s/sites/%s/permissions', $user_email, $site_name ), 'DELETE' );
    
    if ( is_wp_error( $response ) ) {
      return false;
    }

    return true;
  }

  function redirect_to_duda( $site_name = null, $user_email = null ) {
    if ( empty( $site_name ) || empty( $user_email ) )
      return;
    
    $response = $this->curl_request( sprintf( '/accounts/sso/%s/link/?target=EDITOR&site_name=%s', $user_email, $site_name ) );
    if ( is_wp_error( $response ) || !array_key_exists( 'url', $response ) ) {
      // error_log( "Error occured when generate SSO Token" );
      return false;
    }

    wp_redirect( $response['url'] );

    echo '<script>window.open( "' . $response['url']  . '", "_blank" )</script>';
      
    /* $response = $this->curl_request( sprintf( '/accounts/sso/%s/token', $user_email ) );
    if ( is_wp_error( $response ) || !array_key_exists( 'url_parameter', $response ) ) {
      // error_log( "Error occured when generate SSO Token" );
      return false;
    }

    $duda_sso_token = $response['url_parameter']['name'] . '=' . $response['url_parameter']['value'];

    wp_redirect( DUDA_SSO_ENDPOINT . '/editor/d1?reset=' . $site_name . '&' . $duda_sso_token );
    exit; */
  }

  // set site publish/unpublish
  function set_site_publish_mode( $site_name = null, $is_publish = true ) {
    if ( empty( $site_name ) )
      return;
    
    $response = $this->curl_request( sprintf( '/sites/multiscreen/%s/%s', $is_publish ? 'publish' : 'unpublish', $site_name ), 'POST' );
    
    if ( is_wp_error( $response ) ) {
      return false;
    }

    return true;
  }

  // redirect to duda editor page
  function redirect_to_duda_editor() {
    print_r( $_REQUEST );
    if ( $_REQUEST['confirm'] != 'yes')
      return;
    
    $current_user = wp_get_current_user();
    $response = $this->curl_request( sprintf( '/accounts/sso/%s/link', $current_user->user_email ) );
    echo "adsfasdf asdf asf";

    if ( is_wp_error( $response ) || !array_key_exists( 'url', $response ) ) {
      // error_log( "Error occured when generate SSO Token" );
      return false;
    }

    wp_redirect( $response['url'] );
    exit;
  }
}