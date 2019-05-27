<?php
require_once( 'duda.php' );

function duda() {
  return Duda::getInstance();
}

function get_duda_subscription_addons() {
  if ( !defined( 'DUDA_SUBSCRIPTION_PRODUCT_ADDONS' ) ) return [];
  
  if ( !is_array( DUDA_SUBSCRIPTION_PRODUCT_ADDONS ) ) return [];
  
  if ( empty( DUDA_SUBSCRIPTION_PRODUCT_ADDONS ) ) return [];
  
  return DUDA_SUBSCRIPTION_PRODUCT_ADDONS;
}

function conditional_duda_redirect( $order = null ) {
  if ( empty( $order ) ) return;

  if ( is_int( $order ) )
    $order = wc_get_order( $order );

  foreach ( $order->get_items() as $item ) {
    if ( $item->get_product_id() == get_duda_subscription_addons()['neighborhoods'] ) {
      ob_start();
    ?>
      <script>
        var w = window.open( '', 'Select 10 Neighborhoods' );
        setTimeout( function() {
          w.location.href = '<?php _e( add_query_arg( 'order_id', $order->get_id(), home_url( '/10-neighborhoods' ) ) ); ?>';
        }, 100);
      </script>
    <?php
  
      $script = ob_get_contents();
  
      ob_end_clean();
  
      echo $script;

      return;
    }
  }

  $site_name = $order->get_meta( 'site_name' );

  $current_user = wp_get_current_user();
  $user_email = $current_user->user_email;

  duda()->redirect_to_duda( $site_name, $user_email );
}

add_action( 'init', function() {
  if ( isset( $_REQUEST['action'] ) ) {
    switch( $_REQUEST['action'] ) {
      case 'duda_tpl_select':
        if ( isset( $_REQUEST['id'] ) && !empty( $_REQUEST['id']) ) {
          $addon_ids = [];

          if ( isset( $_REQUEST['addon_ids'] ) && !empty( $_REQUEST['addon_ids'] ) ) {
            $addon_ids = explode( "|||", $_REQUEST['addon_ids'] );
          }
          duda()->selectTemplate( $_REQUEST['id'], $addon_ids );          
        }
        break;

      case 'redirect_to_duda_editor':
        duda()->redirect_to_duda_editor();
        break;
      
      case 'save_neighborhoods':
        extract( $_REQUEST );
        if ( !wp_verify_nonce( $__wpnonce, '10-neighborhoods' ) ) return;
        
        if ( !$order_id ) return;

        $order = wc_get_order( $order_id );

        $order->update_meta_data( 'marketplace', $marketplace );
        $order->update_meta_data( 'neighborhoods', implode( ', ', $neighborhoods ) );
      
        $current_user = wp_get_current_user();
        $user_email = $current_user->user_email;

        wp_mail(
          'help@agentcloud.com',
          'New 10 Neighborhoods',
          sprintf( 'Selected market place is %s and neighborhoods are %s.', $marketplace, implode( ', ', $neighborhoods ) ),
          ['Cc: emmanuel@agentcloud.com', sprintf( 'Cc: %s', $user_email )]
        );

        $site_name = $order->get_meta( 'site_name' );
      
        duda()->redirect_to_duda( $site_name, $user_email, false );

        break;
    }
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