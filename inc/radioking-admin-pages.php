<?php

function radioking_dashboard_page(){
	include (__DIR__.'/../pages/radioking-dashboard.php');
}
function radioking_tracks_import_page(){
	wp_enqueue_script('radioking-tracks-import-script', plugins_url() . '/radioking/js/tracks-import.js', array('jquery'));
	wp_enqueue_style('radioking-tracks-import-style', plugins_url() . '/radioking/css/tracks-import.css');
	include (__DIR__.'/../pages/radioking-tracks-import.php');
}
function radioking_schedules_import_page(){
	wp_enqueue_script('radioking-schedules-import-script', plugins_url() . '/radioking/js/schedules-import.js', array('jquery'));
	wp_enqueue_style('radioking-schedules-import-style', plugins_url() . '/radioking/css/schedules-import.css');
	include (__DIR__.'/../pages/radioking-schedules-import.php');
}
function radioking_tracks_history_page(){
	global $wpdb;
	wp_enqueue_style('radioking-tracks-history-style', plugins_url() . '/radioking/css/tracks-history.css');
	include (__DIR__.'/../pages/radioking-tracks-history.php');
}

add_action('admin_menu', 'radioking_menu');

function radioking_menu() {
	add_menu_page( __( 'Syncronisation et gestion de Radioking', 'radio404' ),
		__( 'RadioKing', 'radio404' ), 'administrator',
		'radioking-admin', 'radioking_dashboard_page',
		'dashicons-radioking', 80 );

	add_submenu_page( 'radioking-admin',
		__( 'Syncronisation des pistes RadioKing', 'radio404' ),
		__( 'Syncronisation', 'radio404' ),
	'administrator',
	'radioking-tracks-import',
	'radioking_tracks_import_page',
		);

	add_submenu_page( 'radioking-admin',
		__( 'Syncronisation du planning RadioKing', 'radio404' ),
		__( 'Planning', 'radio404' ),
	'administrator',
	'radioking-schedules-import',
	'radioking_schedules_import_page',
		);
	add_submenu_page( 'radioking-admin',
		__( 'Historique des pistes RadioKing', 'radio404' ),
		__( 'Historique', 'radio404' ),
	'administrator',
	'radioking-tracks-history',
	'radioking_tracks_history_page',
		);
}