<?php

function radioking_get_token(){

	$user_id = get_current_user_id();
	$api_oauth_endpoint = get_field('radioking_api_manager_endpoint','option');
	$rk_user_id = get_field("radioking_user_id","user_$user_id");
	$rk_password = get_field("radioking_password","user_$user_id");
	$response = Requests::post($api_oauth_endpoint, [], json_encode( [
		'login'    => $rk_user_id,
		'password' => $rk_password,
	] ) );

	if($response->body){
		$data         = json_decode( $response->body );
		if($data && $data->access_token){
			$access_token = $data->access_token;
			return $access_token;
		}else{
			return false;
		}
	}else{
		return false;
	}

}

function radioking_get_track_box($access_token=null){
	$access_token = $access_token ?? radioking_get_token();
	$api_headers = [ "authorization"=> "Bearer $access_token"];
	$response = Requests::get("https://www.radioking.com/api/track/box/240028",$api_headers);
	return json_decode($response->body)->data;
}

function radioking_sync_week_planned($access_token=null){
	$access_token = $access_token ?? radioking_get_token();
	$api_headers = [ "authorization"=> "Bearer $access_token"];
	$day = date('w');
	$week_start = date('Y-m-d', strtotime('-'.($day).' days'));
	$week_end = date('Y-m-d', strtotime('+'.(7-$day).' days'));
	$response = Requests::get("https://www.radioking.com/api/radio/240028/schedule/planned/$week_start/to/$week_end",$api_headers);
	$radioking_schedules = json_decode($response->body)->data;
	foreach ($radioking_schedules as &$schedule){
		$schedule->day_playlist = !!preg_match('/^Day #\d/',$schedule->name);
		if($schedule->day_playlist){
			continue;
		}

		if($schedule->idplaylist){
			$response = Requests::get("https://www.radioking.com/api/playlist/tracks/240028/$schedule->idplaylist?limit=50&offset=0",$api_headers);
			$schedule->playlist = json_decode($response->body)->data;
			foreach ($schedule->playlist->tracks as &$track){
				if($track->idtrackbox === 3){
					$wp_track = get_track_by_id($track->idtrack);
					$wp_track->acf = get_fields($wp_track->ID) ?? [];
					$wp_podcast = get_post($wp_track->acf['album']);
					$wp_podcast->acf = get_fields($wp_track->acf['album']);
					$schedule->podcast_id = $wp_track->acf['album'];
					$schedule->podcast = $wp_podcast;
					$track->wp_track = $wp_track;
				}
			}
		}
	}
	return $radioking_schedules;
}

function acf_map_meta_insert(array $source_fields):array {
	$dest_fields = [];
	foreach ($source_fields as $key=>$value){
		$dest_key = "_$key";
		if(is_array($value)){
			$dest_value = "field_$key";
			foreach ($value as $rowindex => $row){
				foreach ($row as $subkey=>$subvalue){
					$dest_sub_key = $key."_$rowindex"."_$subkey";
					$dest_fields[$dest_sub_key] = "field_$subkey";
					$dest_fields["_$dest_sub_key"] = $subvalue;
				}
			}
		}else{
			$dest_value = $value;
			$dest_fields[$key]=$dest_value;
		}
		$dest_fields[$dest_key]=$dest_value;
	}
	return $dest_fields;
}