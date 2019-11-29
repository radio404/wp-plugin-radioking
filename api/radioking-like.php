<?php

add_action( 'rest_api_init', 'radioking_like_api_route' );
function radioking_like_api_route() {
	register_rest_route( 'radioking', 'like', array(
			'methods' => 'POST',
			'callback' => 'radioking_like_api_callback',
		)
	);
}
function radioking_like_api_callback($request_data) {

	$success = ['success'=>false];
	try {
		$payload       = json_decode( $request_data->get_body() );
		$like_response = [];
		if ( $payload ) {
			if ( $payload->id ) {
				$success = like_track($payload);
			}
		}
	}catch (Throwable $err){
		$payload = $err->getMessage();
	}

	return rest_ensure_response( $success );
}