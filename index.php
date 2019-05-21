<?php
/**
 * Plugin Name: Duda SSO
 * Description: SSO plugin between Agent Cloud and Duda
 * Version:     1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
  exit;
}

require_once( 'config.php' );

require_once( 'includes/index.php' );

if ( function_exists( 'duda' ) ) {
  duda();
}

add_shortcode( 'duda-sso-view', function() {
  wp_enqueue_style( 'duda-sso-style' );
  wp_enqueue_script( 'duda-sso-script' );

  ob_start();
?>

  <div class="duda-sso-container container">
    <?php include_once 'views/all-templates.php'; ?>
  </div>

<?php
  $html = ob_get_contents();

  ob_end_clean();

  return $html;
} );

add_action( 'wp_enqueue_scripts', function() {  
  wp_register_style( 'duda-sso-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array( 'wp-jquery-ui-dialog' ) );
  wp_register_script( 'duda-sso-script', plugin_dir_url( __FILE__ ) . 'assets/js/scripts.js', array( 'jquery', 'jquery-ui-dialog' ), '1.0.0', true );

  if ( is_account_page() || is_checkout() ) {
    wp_enqueue_style( 'duda-woocommerce-style', plugin_dir_url( __FILE__ ) . 'assets/css/woocommerce.css' );
  }
} );

// hook when new order is placed
add_action( 'woocommerce_checkout_update_order_meta', function( $order_id ) {
  if ( !isset( $_REQUEST['site_name'] ) || empty( $_REQUEST['site_name'] ) )
    return;
  
  $order = wc_get_order( $order_id );
  $order->update_meta_data( 'site_name', $_REQUEST['site_name'] );

  $order->save();
}, 10, 1 );

add_action( 'woocommerce_thankyou', 'custom_woocommerce_auto_complete_order' );
function custom_woocommerce_auto_complete_order( $order_id ) { 
    if ( ! $order_id ) {
        return;
    }

    $order = wc_get_order( $order_id );
    $order->update_status( 'completed' );
}