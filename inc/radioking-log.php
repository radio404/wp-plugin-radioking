<?php

function radioking_log_track($payload){
	global $wpdb;

	$rk_track_id = $payload->id;
	$started_at = $payload->started_at;
	$end_at = $payload->end_at;
	$wp_track = get_track_by_id($rk_track_id);
	$wp_track_id = $wp_track->ID ?? 0;
	$success = $wpdb->insert($wpdb->prefix.'track_log',[
		'rk_track_id'=>$rk_track_id,
		'wp_track_id'=>$wp_track_id,
		'started_at'=>$started_at,
		'end_at'=>$end_at,
	]);

	return ['success'=>!!$success];
}