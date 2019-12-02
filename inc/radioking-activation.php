<?php

function radioking_activate_plugin(){
	global $wpdb;
	$table_name = $wpdb->prefix . 'track_like';
	$sql = "CREATE TABLE $table_name (
 id_like mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
 rk_track_id int unsigned NOT NULL,
 wp_track_id int unsigned NOT NULL,
 like_offset tinyint(2) NOT NULL,
 like_emoji varchar(8) NOT NULL,
 date datetime DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY  (id_like)
 );";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

register_activation_hook( __FILE__, 'radioking_activate_plugin' );

