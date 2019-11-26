<?php

add_action( 'rest_api_init', 'radioking_tracks_import_api_route' );
function radioking_tracks_import_api_route() {
	register_rest_route( 'radioking', 'tracks-import', array(
			'methods' => 'POST',
			'callback' => 'radioking_tracks_import_api_callback',
		)
	);
}
function radioking_tracks_import_api_callback() {

	$radioking_access_token = $_POST['radioking_access_token'];
	$offset = intval($_POST['offset']);
	$idtrackbox = intval($_POST['idtrackbox']) ?? 1;
	$limit = intval($_POST['limit']);
	if ( !$radioking_access_token) {
		wp_die('sorry you are not allowed to access this data','cheatin eh?',403);
	}

	$tracks_imported = radioking_tracks_import($offset,$limit,$idtrackbox,$radioking_access_token);

	return rest_ensure_response( $tracks_imported );
}