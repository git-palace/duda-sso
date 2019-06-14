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

		$current_user = wp_get_current_user();
		// $user_email = $subscription->get_billing_email();
		$user_email = $current_user->user_email;

		$this->createCustomerAcct( $site_name, $user_email, true );
		$this->update_site_content( $site_name );
		// $this->set_site_publish_mode( $site_name, true );
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

		$current_user = wp_get_current_user();
		// $user_email = $subscription->get_billing_email();
		$user_email = $current_user->user_email;

		$this->deleteCustomerAcct( $site_name, $user_email );
		// $this->set_site_publish_mode( $site_name, false );
	}

	// get template
	function getTemplates() {
		$response = $this->curl_request( '/sites/multiscreen/templates' );

		return $response;
	}

	// select template
	function selectTemplate( $tpl_id = null, $addon_ids = [] ) {
		if ( empty( $tpl_id ) )
			return;
		
		$response = $this->curl_request( '/sites/multiscreen/create', 'POST', [ 'template_id' => $tpl_id ] );
		
		if ( is_wp_error( $response ) || !array_key_exists( 'site_name', $response ) ){
			// error_log( "Error occured when create site with template id: " . $tpl_id );
			return false;
		}
		
		WC()->cart->empty_cart();
		WC()->cart->add_to_cart( DUDA_SUBSCRIPTION_PRODUCT_ID );
		
		foreach( $addon_ids as $addon_id )
			WC()->cart->add_to_cart( $addon_id );
		
		wp_redirect( esc_url( add_query_arg( 'site_name', $response['site_name'], wc_get_checkout_url() ) ) );
		exit;
	}

	// create customer account
	function createCustomerAcct( $site_name = null, $user_email = null, $is_completed = true ) {
		if ( empty( $user_email ) )
			return false;
		
		$response = $this->curl_request( '/accounts/create', 'POST', ['account_name'  => $user_email] );
		
		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( empty( $site_name ) )
			return false;

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

	function redirect_to_duda( $site_name = null, $user_email = null, $open_new_tab = true ) {
		if ( empty( $site_name ) || empty( $user_email ) )
			return;
		
		$response = $this->curl_request( sprintf( '/accounts/sso/%s/link/?target=EDITOR&site_name=%s', $user_email, $site_name ) );
		if ( is_wp_error( $response ) || !array_key_exists( 'url', $response ) ) {
			// error_log( "Error occured when generate SSO Token" );
			return false;
		}

		if ( !$open_new_tab ) {
			wp_redirect( $response['url'] );
			exit;
		}

		ob_start();
		?>
			<script>
				var w = window.open( '', 'WebsiteBuilder' );
				setTimeout( function() {
					w.location.href = '<?php _e( $response['url'] ); ?>';
				}, 100);
			</script>
		<?php

		$script = ob_get_contents();

		ob_end_clean();

		echo $script;
	}

	// redirect to duda editor page
	function redirect_to_duda_editor() {
		if ( $_REQUEST['confirm'] != 'yes')
			return;

		if ( !wcs_user_has_subscription( get_current_user_id(), DUDA_SUBSCRIPTION_PRODUCT_ID, 'active' ) ) {
			wp_redirect( home_url( '/websitebuilder' ) );
			exit;
		}
		
		$current_user = wp_get_current_user();
		$response = $this->curl_request( sprintf( '/accounts/sso/%s/link', $current_user->user_email ) );

		if ( is_wp_error( $response ) || !array_key_exists( 'url', $response ) ) {
			// error_log( "Error occured when generate SSO Token" );
			return false;
		}

		wp_redirect( $response['url'] );
		exit;
	}

	// update site content
	function update_site_content( $site_name = null ) {
		if ( empty( $site_name ) ) return;

		$broker_user_id = current_wp_erp_user_is( 'broker' ) ? get_current_user_id() : get_user_meta( get_current_user_id(), 'created_by', true );

		$current_user = wp_get_current_user();
		$email = $current_user->user_email;
		$full_name = $current_user->user_firstname . ' ' . $current_user->user_lastname;

		$office_address_1 = get_user_meta( $broker_user_id, 'office_address_1', true );
		$office_address_2 = get_user_meta( $broker_user_id, 'office_address_2', true );
		$office_city = get_user_meta( $broker_user_id, 'office_city', true );
		$office_state = get_user_meta( $broker_user_id, 'office_state', true );
		$office_zip = get_user_meta( $broker_user_id, 'office_zip', true );

		$office_full_address = sprintf( 
			'%s<br/>%s, %s %s',
			$office_address_1 . ( empty( $office_address_2 ) ? '' : ', ' . $office_address_2 ),
			$office_city,
			$office_state,
			$office_zip
		);

		if ( current_wp_erp_user_is( 'broker' ) || current_wp_erp_user_is( 'staff' ) ) {
			$dre_number = get_user_meta( $broker_user_id, 'office_dre_number', true );
		} else {
			$dre_number = get_user_meta( get_current_user_id(), 'dre_number', true );
		}

		$employee = new WeDevs\ERP\HRM\Employee( get_current_user_id() );
		$work_phone = $employee->get_work_phone();
		$description = $employee->get_data()['personal']['description'];

		$custom_content = [];

		if ( !empty( $full_name ) ) {
			$custom_content[] = [
				'label' => 'Full Name',
				'text'  => sprintf( '<p class="rteBlock">%s</p>', $full_name)
			];
		}

		if ( !empty( $work_phone ) ){
			$custom_content[] = [
				'label' => 'Work Phone',
				'text'  => sprintf( '<p class="rteBlock">%s</p>', $work_phone )
			];
		}

		if ( !empty( $email ) ){
			$custom_content[] = [
				'label' => 'Email Address',
				'text'  => sprintf( '<p class="rteBlock">%s</p>', $email )
			];
		}

		if ( !empty( $dre_number ) ) {
			$dre_number = str_replace( 'dre', '', strtolower( $dre_number ) );
			$dre_number = str_replace( '#', '', strtolower( $dre_number ) );
			
			$custom_content[] = [
				'label' => 'DRE Number',
				'text'  => sprintf( '<p class="rteBlock">DRE# %s</p>', $dre_number )
			];
		}

		if ( !empty( $description ) ) {
			$custom_content[] = [
				'label'	=> 'Biography',
				'text'  => sprintf( '<p class="rteBlock">%s</p>', $description )
			];
		}

		if ( !empty( $description ) ) {
			$custom_content[] = [
				'label'	=> 'Office Address',
				'text'  => sprintf( '<p class="rteBlock">%s</p>', $office_full_address )
			];
		}

		$site_images = [];

		$photo_id = get_user_meta( $broker_user_id, 'office_logo', true );
		$office_logo_url = $photo_id ? wp_get_attachment_url( $photo_id ) : '';
		if ( !empty( $office_logo_url ) ) {
			$site_images[] = [
				'label' => 'Office Logo',
				'url'	=> $office_logo_url,
				'alt'	=> 'Office Logo'
			];
		}

		$avatar_url = $employee->get_avatar_url( 450 );
		if ( !empty( $avatar_url ) ) {
			$site_images[] = [
				'label' => 'Headshot',
				'url'	=> $avatar_url,
				'alt'	=> 'Headshot'
			];
		}

		$site_content = [
			'site_texts'  => ['custom' => $custom_content], 
			'site_images' => $site_images
		];

		return $site_content;

		// $response = $this->curl_request( sprintf( '/sites/multiscreen/%s/content', $site_name ), 'POST', $site_content );
	}
}