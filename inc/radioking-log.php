<?php

function radioking_log_track($payload){
	global $wpdb;

	$rk_track_id = $payload->id;
	$started_at = DateTime::createFromFormat(DATE_ISO8601, $payload->started_at);
	$end_at = DateTime::createFromFormat(DATE_ISO8601,$payload->end_at);
	$wp_track = get_track_by_id($rk_track_id);
	$wp_track_id = $wp_track->ID ?? 0;
	$success = $wpdb->insert($wpdb->prefix.'track_log',[
		'rk_track_id'=>$rk_track_id,
		'wp_track_id'=>$wp_track_id,
		'started_at'=>$started_at->format('Y-m-d H:i:s'),
		'end_at'=>$end_at->format('Y-m-d H:i:s'),
	]);

	return ['success'=>!!$success,'wp_track_id'=>$wp_track_id];
}