<?php

function like_track($payload){
	$response = Requests::get( "https://www.radioking.com/widgets/api/v1/radio/240028/track/current" );
	$current = false;
	if($response->body){
		$current = json_decode($response->body);
	}
	$is_current_track = $current && $current->id == $payload->id;
	$is_radio_king_like = $is_current_track && radioking_like_track($payload);
	$wp_track = get_track_by_id($payload->id);
	return [
		'is_current_track'=>$is_current_track,
		'is_radio_king_like'=>$is_radio_king_like,
		'wp_track'=>$wp_track,
	];
}

function radioking_like_track($vote=1, $with_proxy=false, $try_count=0, $max_try=8){

	global $proxy_list;
	$ch = curl_init();
	$url = "https://www.radioking.com/api/radio/240028/track/vote";
	$headers = [];

	curl_setopt($ch, CURLOPT_POST,true);
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_TIMEOUT,6);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"vote\":$vote}");
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$proxy_url = "N/A";
	if($with_proxy) {
		// trying to use public proxy to bypass ip restriction
		if(!(count($proxy_list) >= $max_try)){
			$proxy_list_api_url = "https://www.proxy-list.download/api/v1/get?type=socks5";
			$response           = Requests::get( $proxy_list_api_url );
			$proxy_list         = explode( "\r\n", $response->body );
		}
		$proxy_url = $proxy_list[ $try_count ];
		curl_setopt($ch, CURLOPT_PROXY, $proxy_url);
		curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
	}

	$curl_body = curl_exec($ch);
	$curl_error = curl_error($ch);
	$curl_status = curl_getinfo($ch,CURLINFO_HTTP_CODE);
	curl_close($ch);

	$json_body = json_decode($curl_body);
	if($curl_error) {
		if($try_count<$max_try){
			return radioking_like_track($vote, true, $try_count++);
		}else{
			return ['status'=>'error','message'=>$curl_error];
		}
	}
	if($json_body->status === 'success'){
		return $json_body;
	}else if($json_body->status === 'error'){
		if($curl_status === 429 && $try_count<$max_try){
			return radioking_like_track($vote, true, $try_count++);
		}else{
			return $json_body;
		}
	}else{
		return ['status'=>'error','message'=>'fatal error'];
	}


}