<?php

function ignore_amp_filter($value){
	return str_replace('&amp;','&',$value);
}

function cover_upload_dir($upload){
	$upload['subdir'] = '/cover' . $upload['subdir'];
	$upload['path']       = $upload['basedir'] . $upload['subdir'];
	$upload['url']        = $upload['baseurl'] . $upload['subdir'];
	return $upload;
}

function radioking_tracks_import($offset=0,$limit=1,$box=1,$access_token=null){

	$access_token = $access_token ?? radioking_get_token();
	$api_headers = [ "authorization"=> "Bearer $access_token"];
	$response = Requests::get("https://www.radioking.com/api/track/tracks/240028/limit/$limit/offset/$offset/order/upload_date/desc?box=$box",$api_headers);

	$wp_users = get_users();
	$wp_users_display_name = [];
	$default_user_id = 0;
	foreach ($wp_users as $user){
		if($user->data->display_name === 'robot404'){
			$default_user_id = $user->ID;
		}
		$wp_users_display_name[strtolower($user->data->display_name)] = $user->ID;
	}

	$tracks = json_decode($response->body)->data;
	$tracks_imported = [];

	add_filter('upload_dir','cover_upload_dir',10);
	add_filter('title_save_pre','ignore_amp_filter',10);
	add_filter('title_save_pre','ignore_amp_filter',10);

	foreach ($tracks as $track){

		$wp_track = get_track_by_id($track->idtrack);

		if($wp_track->ID){
			$track_no_sync = get_field('no_sync',$wp_track->ID);
			if($track_no_sync){
				$tracks_imported[] = [
					'wp_track'=>$wp_track,
					'track'=>$track,
					'track_no_sync'=>$track_no_sync,
				];
				continue;
			}
		}

		switch($box){
			case 3:
				$album_post_type = 'podcast';
				$wp_album = get_podcast_by_title($track->album);
				$wp_cover = get_cover_by_album(false, false, $track->cover);
				break;
			default:
				$album_post_type = 'album';
				$wp_album = get_album_by_title_and_artist($track->album,$track->artist);
				$wp_cover = get_cover_by_album($track->album, $track->artist, $track->cover);
				break;
		}


		$id_author = $default_user_id;
		$upload_date = new DateTime($track->upload_date);
		$post_date = $upload_date->format("Y-m-d H:i:s");

		if(!! $wp_track || (intval($wp_track->post_author) <= 0)) {
			// détails des tags
			$response      = Requests::get( "https://www.radioking.com/api/track/240028/$track->idtrack", $api_headers );
			$track_details = json_decode( $response->body )->data;
			if($track_details->tags){
				foreach($track_details->tags as $index=>$tag){
					$tagname = strtolower($tag->name);
					if(isset($wp_users_display_name[$tagname])){
						$id_author = $wp_users_display_name[$tagname];
					}
				}
				$track->tags = $track_details->tags;
			}
		}else if($wp_track){
			$id_author = $wp_track->post_author;
		}

		$artists_names = array_map('trim',preg_split("/[,;]/",$track->artist));
		$artist_list = [];

		foreach ($artists_names as $artist_name){
			switch(strtolower($artist_name)){
				case '':
				case 'Inconnu':
				case 'Unknown':
					$artist_name = 'Inconnu';
					break;
			}
			$wp_artist = get_artist_by_name($artist_name);
			$wp_artist_attr = [
				'post_title' => $artist_name,
				'post_author'=> $id_author,
				'post_type' => 'artist',
				'post_status' => 'publish',
				'post_date' => $post_date,
			];
			if(!$wp_artist){
				$wp_artist_id = wp_insert_post($wp_artist_attr);
				$wp_artist = get_post($wp_artist_id);
			}else{
				$wp_artist_attr['ID'] = $wp_artist->ID;
				$artist_no_sync = get_field('no_sync',$wp_artist->ID);
				if(!$artist_no_sync){
					wp_update_post($wp_artist_attr);
				}
			}
			$artist_list[] = $wp_artist->ID;
		}

		$wp_album_meta = [
			'artist_literal' => $track->artist,
			'artist' => $artist_list,
			'release_year' => $track->year,
		];

		$wp_album_attr = [
			'post_title' => $track->album,
			'post_type' => $album_post_type,
			'post_status' => 'publish',
			'post_author'=> $id_author,
			'post_name' => sanitize_title("$track->artist--$track->album"),
			'post_date' => $post_date,
			'post_date_gmt' => $post_date,
			//'meta_input' => $wp_album_meta,
		];
		$album_no_sync = false;
		if(!$wp_album){
			$wp_album_id = wp_insert_post($wp_album_attr);
			$wp_album = get_post($wp_album_id);

		}else{
			$wp_album_attr['ID'] = $wp_album->ID;
			$album_no_sync = get_field('no_sync',$wp_album->ID);
			if(!$album_no_sync){
				wp_update_post($wp_album_attr);
			}
		}
		if(!$album_no_sync) {
			foreach ( $wp_album_meta as $field_key => $field_value ) {
				update_field( $field_key, $field_value, $wp_album->ID );
			}
		}

		$wp_track_meta = [
			'idtrack' => "$track->idtrack",
			'upload_date' => $track->upload_date,
			'release_year' => $track->year,
			'bpm' => $track->bpm,
			'tracklength_seconds' => $track->tracklength_seconds,
			'tracklength_string' => $track->tracklength_string,
			'playtime_seconds' => $track->playtime_seconds,
			'playtime_string' => $track->playtime_string,
			'artist' => $artist_list,
			'artist_literal' => $track->artist,
			'album' => $wp_album->ID,
			'album_post_type' => $album_post_type,
			'album_literal' => $track->album,
		];
		$wp_track_attr = [
			'post_title' => $track->title,
			'post_type' => 'track',
			'post_status' => 'publish',
			'post_author'=> $id_author,
			'post_name' => sanitize_title("$track->album--$track->title"),
			'post_date' => $post_date,
			'post_date_gmt' => $post_date,
			//'meta_input' => $wp_track_meta
		];
		if(!$wp_track){
			$wp_track_id = wp_insert_post($wp_track_attr);
			$wp_track = get_post($wp_track_id);
		}else{
			$wp_track_attr['ID'] = $wp_track->ID;
			wp_update_post($wp_track_attr);
		}
		foreach ($wp_track_meta as $field_key => $field_value){
			update_field($field_key, $field_value, $wp_track->ID);
		}

		// on ajoute la track à l'album/podcast
		$track_listing = get_field('track_listing',$wp_album) ?? [];
		$is_in_track_listing = false;
		foreach($track_listing as $item){
			$item_track = $item['track'];
			$is_in_track_listing |= (!!$item_track && ($item_track->ID === $wp_track->ID));
		};
		if(!$is_in_track_listing){
			update_field('track_listing',array_merge($track_listing,[['track'=>$wp_track->ID]]),$wp_album->ID);
		}

		if($track->cover_url) {
			$wp_cover_meta = [
				'is_cover'    => true,
				'idtrack'     => $track->idtrack,
				'id_album'    => $wp_album->ID,
				'album'       => $track->album,
				'artist'      => $artist_list,
				'artist_literal' => $track->artist,
				'cover'       => $track->cover,
			];
			if ( ! $wp_cover) {
				$wp_cover_id = insert_attachment_from_url(
					$track->cover_url,
					$wp_album->ID,
					$track->title,
					$wp_cover_meta
				);
				$wp_cover    = get_post( $wp_cover_id );
			}

			if(!$album_no_sync){
				set_post_thumbnail( $wp_album->ID, $wp_cover->ID );
			}
			set_post_thumbnail( $wp_track->ID, $wp_cover->ID );
		}else{
			$wp_cover_id = false;
		}

		$tracks_imported[] = [
			'download'=> !!$wp_cover_id,
			'track'=>$track,
			'wp_track'=>$wp_track,
			'wp_cover'=>$wp_cover,
			'track_listing'=>$track_listing,
			'track_no_sync'=>$track_no_sync ?? false,
			'cover_url'=>get_the_post_thumbnail_url($wp_track->ID,'thumbnail')
		];

	}
	return $tracks_imported;
}