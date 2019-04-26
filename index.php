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

add_shortcode( 'duda-sso-view', function() {
  ob_start();
?>

  <div class="duda-sso-container container-fluid">
    <?php include_once 'views/all-templates.php'; ?>
  </div>

<?php
  $html = ob_get_contents();

  ob_end_clean();

  return $html;
} );

add_action( 'wp_enqueue_scripts', function() {
  wp_register_style( 'bootstrap-4-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css' );
  wp_register_script( 'popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js', array( 'jquery' ), '1.12.9', true );
  wp_register_script( 'bootstrap-4-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js', array( 'jquery' ), '4.0.0', true );
  
  wp_enqueue_style( 'duda-sso-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array( 'bootstrap-4-css') );
  wp_enqueue_script( 'duda-sso-script', plugin_dir_url( __FILE__ ) . 'assets/js/scripts.js', array( 'bootstrap-4-js' ), '1.0.0', true );
} );