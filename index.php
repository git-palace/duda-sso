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

echo '<pre>';
print_r( duda()->getTemplates() );
echo '</pre>';

exit;