<?php

add_action( 'rest_api_init', 'radioking_log_api_route' );
function radioking_log_api_route() {
	register_rest_route( 'radioking', 'log', array(
			'methods' => 'POST',
			'callback' => 'radioking_log_api_callback',
		)
	);
}
function radioking_log_api_callback($request_data) {

	$success = ['success'=>false];
	try {
		$payload       = json_decode( $request_data->get_body() );
		if ( $payload ) {
			if( !defined('WP_RK_LOG_TRACK_PASSPHRASE') || $payload->passphrase !== WP_RK_LOG_TRACK_PASSPHRASE){
				$success['error'] = 'Invalid passphrase.';
			} else if ( $payload->id) {
				$success = radioking_log_track($payload);
			}
		}
	}catch (Throwable $err){
		$success['error'] = $err->getMessage();
	}

	return rest_ensure_response( $success );
}