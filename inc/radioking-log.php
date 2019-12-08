<?php

function radioking_log_track($payload){
	global $wpdb;
	$success = false;

	$rk_track_id = $payload->id;
	$wp_track = get_track_by_id($rk_track_id);
	$wp_track_id = $wp_track->ID ?? 0;
	$success = $wpdb->insert($wpdb->prefix.'track_log',[
		'rk_track_id'=>$rk_track_id,
		'wp_track_id'=>$wp_track_id,
	]);

	return ['success'=>$success,'wp_track_id'=>$wp_track_id];
}