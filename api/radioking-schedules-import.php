<?php

add_action( 'rest_api_init', 'radioking_schedules_import_api_route' );
function radioking_schedules_import_api_route() {
	register_rest_route( 'radioking', 'schedules-import', array(
			'methods' => 'POST',
			'callback' => 'radioking_schedules_import_api_callback',
		)
	);
}
function radioking_schedules_import_api_callback() {

	$radioking_access_token = $_POST['radioking_access_token'];
	if ( !$radioking_access_token) {
		wp_die('sorry you are not allowed to access this data','cheatin eh?',403);
	}

	try {
		$schedules = radioking_sync_week_planned($radioking_access_token);
	}catch (Throwable $exception){
		return new WP_REST_Response(['message'=>$exception->getMessage()], 500);
	}

	return rest_ensure_response( $schedules );
}