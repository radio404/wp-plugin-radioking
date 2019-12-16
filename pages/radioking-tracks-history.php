<?php

$page_title = __( 'Historique des pistes RadioKing des dernières 24h', 'radio404' );

?><div class="wrap">
	<h1><?= $page_title ?></h1>

    <table class="tracks-history__table">
        <thead>
            <tr>
                <th></th>
                <th></th>
                <th>Titre</th>
                <th>Artist</th>
                <th>Album</th>
                <th>Upload</th>
            </tr>
        </thead>
        <tbody><?php

        try {
            $utc_timezone = new DateTimeZone('UTC');
            $paris_timezone = new DateTimeZone('Europe/Paris');
	        $now           = new DateTime( 'now', $utc_timezone);
	        $today         = date_format( $now, 'Y-m-d H:i:sP' );
	        $yesterday      = date_format( $now->modify( '-1 day' ), 'Y-m-d  H:i:sP' );
	        $history_sql = "SELECT * FROM `wp_track_log` WHERE `started_at` > '$yesterday' ORDER BY `id_track_log` DESC";
	        $history       = $wpdb->get_results( $history_sql );
	        $wp_tracks_ids = array_column( $history, 'wp_track_id' );
	        $wp_tracks     = get_posts( [
		        'numberposts' => - 1,
		        'post_type'   => 'track',
		        'post__in'    => $wp_tracks_ids,
	        ] );

	        foreach ( $history as $line ) {
		        $wp_track_key = array_search( $line->wp_track_id, array_column( $wp_tracks, 'ID' ) );
		        $date = new DateTime($line->started_at,$utc_timezone);
		        $date->setTimezone($paris_timezone);
		        $d = $date->format('H:i');


		        $started_at_time = $line->started_at;//substr($line->started_at,10,9);
		        echo "<tr><td class='col-time'><code class='time' title='$line->rk_track_id'>$d</code></td>";
		        if ( $wp_track_key ) {
			        $wp_post           = $wp_tracks[ $wp_track_key ];
			        $wp_post_edit_link = get_edit_post_link($wp_post->ID);
			        $wp_album_id = get_field('album',$wp_post->ID);
			        $wp_post_thumbnail = get_the_post_thumbnail_url( $line->wp_track_id, [ 100, 100 ] );
			        $wp_post_author = $wp_post->post_author == 0 ? 'un bel inconnu' : get_the_author_meta('display_name',$wp_post->post_author);
			        $wp_post_thumbnail_img = $wp_post_thumbnail ?
                        "<img class='thumbnail' width='25' height='25' src='$wp_post_thumbnail' alt='' loading='lazy' />":"";

			        if($wp_album_id){
				        $wp_album = get_post($wp_album_id);
				        $wp_album_link = get_edit_post_link($wp_album_id);
				        $album_info = "<a href='$wp_album_link'>$wp_album->post_title</a>";
                    }else{
				        $album_info = "";
                    }

			        $wp_artist_list = get_field('artist_list',$wp_post->ID);
			        $artist_info = '';
			        foreach ($wp_artist_list as $item){
			            $artist = $item['artist'];
			            $artist_link = get_edit_post_link($artist->ID);
				        $artist_info .= "<a href='$artist_link'>$artist->post_title</a> ";
                    }

			        echo "<td class='col-cover'>$wp_post_thumbnail_img</td>";
			        echo "<td class='col-title tracks-history__track-title'><a href='$wp_post_edit_link'>$wp_post->post_title</a> </td>";
			        echo "<td class='col-artist'>$artist_info</td>";
			        echo "<td class='col-album'>$album_info</td>";
			        echo "<td class='col-author'><strong>$wp_post_author</strong></td>";
		        } else {
			        echo "<td colspan='5'></td><td></td>";
		        }
		        echo "</tr>";
	        }
        }catch (Throwable $err){
            echo $err->getMessage();
        }

		?></tbody></table>

</div>