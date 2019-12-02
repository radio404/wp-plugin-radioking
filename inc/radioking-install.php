<?php


register_activation_hook( __FILE__, 'radioking_create_db' );
function radioking_create_db() {
	// Create DB Here
	global $wpdb;
	$table_name = $wpdb->prefix . 'track_like';
	$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
 id_track_like mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
 rk_track_id int unsigned NOT NULL,
 wp_track_id int unsigned NOT NULL,
 like_offset tinyint(2) NOT NULL,
 like_emoji varchar(4) NOT NULL,
 date datetime DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY  (id_track_like)
 );";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

