<?php
/**
 * Plugin Name: Duda SSO
 * Description: SSO plugin between Agent Cloud and Duda
 * Version:     1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
  exit;
}

if ( !is_plugin_active( 'wp-erp/wp-erp.php' ) || !is_plugin_active( 'wp-erp-shortcodes/index.php' ) ) {
  return;
}

define( 'DUDA_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require_once( 'config.php' );

require_once( 'includes/index.php' );

if ( function_exists( 'duda' ) ) {
  duda();
}

add_action( 'show_duda_addon_remove_button', function( $cart_item_key, $_product ) {
  $product_id = $_product->get_id();

  echo apply_filters( 
    'woocommerce_cart_item_remove_link', 
    sprintf(
      '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s" style="%s">&times;</a>', 
      !in_array( $product_id, array_values( get_duda_subscription_addons() ) ) ? '#' : esc_url( WC()->cart->get_remove_url( $cart_item_key ) ), 
      __( 'Remove this item', 'woocommerce' ), 
      esc_attr( $product_id ), 
      esc_attr( $_product->get_sku() ),
      !in_array( $product_id, array_values( get_duda_subscription_addons() ) ) ? 'visibility: hidden' : ''
    ), 
    $cart_item_key 
  );
}, 10, 2);

add_action( 'wp_enqueue_scripts', function() {
  wp_register_style( 'select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css' );
  wp_register_script( 'select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js', array( 'jquery' ) );

  wp_register_style( 'duda-sso-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array( 'select2-css' ) );
  wp_register_script( 'duda-sso-script', plugin_dir_url( __FILE__ ) . 'assets/js/scripts.js', array( 'jquery', 'select2-js' ), '1.0.0', true );

  wp_enqueue_style( 'duda-sso-style' );
  
  if ( is_account_page() || is_checkout() ) {
    wp_enqueue_style( 'duda-woocommerce-style', plugin_dir_url( __FILE__ ) . 'assets/css/woocommerce.css' );
  }
} );

add_shortcode( '10-neightborhoods-form-view', function() {
  $file_path = DUDA_PLUGIN_PATH . 'assets/js/neighborhoods.json';
  $marketplaces = file_exists( $file_path ) ? json_decode( file_get_contents( $file_path ), true ) : [];

  // wp_localize_script( 'duda-sso-script', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
  // wp_localize_script( 'duda-sso-script', 'marketplaces', $marketplaces );
  wp_enqueue_script( 'duda-sso-script' );
  
  ob_start();
?>

  <div class="neighborhoods-select-container container">
    <form method="POST">
      <div class="marketplace-container">
        <h2>Select Market Place:</h2>
        <select name="marketplace" required style="width: 100%">
          <option value="">Please select below one.</option>
          
          <?php foreach ( array_keys( $marketplaces ) as $marketplace ) : ?>
          
            <option value="<?php esc_attr_e( $marketplace ); ?>"><?php _e( $marketplace ); ?></option>
          
          <?php endforeach; ?>

        </select>
      </div>

      
      <h2>Select Neighborhoods (Max: 10):</h2>

      <?php foreach ( $marketplaces as $marketplace => $neighborhoods ): ?>
      
      <div class="neighborhoods-container" marketplace="<?php esc_attr_e( $marketplace ); ?>" style="display: none;">
        <?php foreach ( $neighborhoods as $neighborhood ): ?>

          <label for="<?php esc_attr_e( sanitize_title( $neighborhood ) ); ?>">
            <input type="checkbox" name="neighborhoods[]" id="<?php esc_attr_e( sanitize_title( $neighborhood ) ); ?>" value="<?php esc_attr_e( $neighborhood ); ?>"/> <?php _e( $neighborhood ); ?>
          </label>

        <?php endforeach; ?>
      </div>

      <?php endforeach; ?>

      <input type="hidden" name="action" value="save_neighborhoods" />
      <input type="hidden" name="order_id" value="<?php esc_attr_e( !empty( $_GET['order_id'] ) ? $_GET['order_id'] : 0 ); ?>"/>
      <input type="hidden" name="__wpnonce" value="<?php esc_attr_e( wp_create_nonce( '10-neighborhoods' ) ); ?>"/>

      <button type="Submit">Submit</button>
    </form>
  </div>

<?php
  $html = ob_get_contents();

  ob_end_clean();

  return $html;
} );

add_shortcode( 'duda-sso-view', function() {
  /* $current_user = wp_get_current_user();
  echo '<pre>';
  print_r( [$current_user->display_name, $current_user->user_email] );
  echo '</pre>';

  return; */

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