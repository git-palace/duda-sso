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
      wp_redirect( add_query_arg( 'order_id', $order->get_id(), home_url( '/10-neighborhoods' ) ) );
      exit;
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

        ob_start();
        ?>

        <p>Sender is <?php _e( $current_user->user_firstname . ' ' . $current_user->user_lastname ); ?>.</p>
        <p>Selected market place is <?php _e( $marketplace ); ?></p>

        <p>Selected neighborhoods are:</p>
        <ol>
          <?php foreach ( $neighborhoods as $neighborhood ) _e( '<li>' . trim( $neighborhood ) . '</li>');?>
        </ol>

        <?php
        $email_content = ob_get_contents();
        ob_end_clean();
        
        wp_mail(
          'help@agentcloud.com',
          'New 10 Neighborhoods',
          $email_content,
          ['Cc: emmanuel@agentcloud.com', 'Cc: amirul@square1grp.com', sprintf( 'Cc: %s', $user_email ), 'Content-Type: text/html; charset=UTF-8']
        );

        $site_name = $order->get_meta( 'site_name' );
      
        duda()->redirect_to_duda( $site_name, $user_email );

        ob_start();
        ?>        
          <script>
            setTimeout( function() {
              window.location.href = '<?php _e( home_url( '/my-account/view-order/' . $order_id ) ); ?>';
            }, 200);
          </script>
        <?php
        $script = ob_get_contents();

        ob_end_clean();

        echo $script;

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