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
	$response = Requests::get("https://www.radioking.com/api/track/tracks/240028/limit/$limit/offset/$offset/order/upload_date/asc?box=$box",$api_headers);

	$wp_users = get_users();
	$wp_users_display_name = [];
	foreach ($wp_users as $user){
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
			$tracks_imported[] = [
				'wp_track'=>$wp_track,
				'track'=>$track
			];
			continue;
		}

		$wp_album = get_album_by_title_and_artist($track->album,$track->artist);
		$wp_cover = get_cover_by_album($track->album, $track->artist, $track->cover);

		$id_author = 0;
		$upload_date = new DateTime($track->upload_date);
		$post_date = $upload_date->format("Y-m-d H:i:s");

		if(intval($wp_track->post_author) <= 1) {
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
			if(!$wp_artist){
				$wp_artist_id = wp_insert_post([
					'post_title' => $artist_name,
					'post_author'=> $id_author,
					'post_type' => 'artist',
					'post_status' => 'publish',
					'post_date' => $post_date,
				]);
				$wp_artist = get_post($wp_artist_id);
			}
			$artist_list[] = ['artist'=>$wp_artist->ID];
		}

		$wp_album_meta = [
			'artist_literal' => $track->artist,
			'artist_list' => $artist_list,
			'release_year' => $track->year,
		];

		if(!$wp_album){
			$wp_album_id = wp_insert_post([
				'post_title' => $track->album,
				'post_type' => 'album',
				'post_status' => 'publish',
				'post_author'=> $id_author,
				'post_name' => sanitize_title("$track->artist--$track->album"),
				'post_date' => $post_date,
				'post_date_gmt' => $post_date,
				//'meta_input' => $wp_album_meta,
			]);
			$wp_album = get_post($wp_album_id);
			foreach ($wp_album_meta as $field_key => $field_value){
				update_field($field_key, $field_value, $wp_album->ID);
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
			'artist_list' => $artist_list,
			'artist_literal' => $track->artist,
			'album' => $wp_album->ID,
			'album_literal' => $track->album,
		];
		if(!$wp_track){
			$wp_track_id = wp_insert_post([
				'post_title' => $track->title,
				'post_type' => 'track',
				'post_status' => 'publish',
				'post_author'=> $id_author,
				'post_name' => sanitize_title("$track->album--$track->title"),
				'post_date' => $post_date,
				'post_date_gmt' => $post_date,
				//'meta_input' => $wp_track_meta
			]);
			$wp_track = get_post($wp_track_id);
			foreach ($wp_track_meta as $field_key => $field_value){
				update_field($field_key, $field_value, $wp_track->ID);
			}

		}

		$wp_cover_meta = [
			'is_cover' => true,
			'idtrack' => $track->idtrack,
			'id_album'   => $wp_album->ID,
			'album'   => $track->album,
			'artist_list' => $artist_list,
			'artist' => $track->artist,
			'cover' => $track->cover,
		];
		if(!$wp_cover){
			$wp_cover_id = insert_attachment_from_url(
				$track->cover_url,
				$wp_album->ID,
				$track->title,
				$wp_cover_meta
			);
			$wp_cover = get_post($wp_cover_id);
		}

		set_post_thumbnail($wp_album->ID,$wp_cover->ID);
		set_post_thumbnail($wp_track->ID,$wp_cover->ID);

		$tracks_imported[] = [
			'download'=> !!$wp_cover_id,
			'track'=>$track,
			'wp_track'=>$wp_track,
			'cover_url'=>get_the_post_thumbnail_url($wp_track->ID,'thumbnail')
		];

	}
	return $tracks_imported;
}