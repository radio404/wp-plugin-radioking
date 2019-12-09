<?php

$page_title = __( 'Historique des pistes RadioKing', 'radio404' );

?><div class="wrap">
	<h1><?= $page_title ?></h1>

    <table class="tracks-history__table"><tbody><?php

        try {
            $utc_timezone = new DateTimeZone('Europe/London');
	        $now           = new DateTime( 'now', new DateTimeZone('Europe/Paris') );
	        $now->modify( '-1 day' );
	        $today         = date_format( $now, 'Y-m-d H:i:sP' );
	        $tomorrow      = date_format( $now->modify( '+1 day' ), 'Y-m-d  H:i:sP' );
	        $yesterday      = date_format( $now->modify( '-2 day' ), 'Y-m-d  H:i:sP' );
	        $history_sql = "SELECT * FROM `wp_track_log` WHERE `started_at` >= '$yesterday' AND `started_at` < '$tomorrow' AND `end_at` < '$tomorrow' AND `end_at` >= '$today' ORDER BY `id_track_log` DESC";
	        $history       = $wpdb->get_results( $history_sql );
	        $wp_tracks_ids = array_column( $history, 'wp_track_id' );
	        $wp_tracks     = get_posts( [
		        'numberposts' => - 1,
		        'post_type'   => 'track',
		        'post__in'    => $wp_tracks_ids,
	        ] );
	        foreach ( $history as $line ) {
		        $wp_track_key = array_search( $line->wp_track_id, array_column( $wp_tracks, 'ID' ) );
		        //$date = new DateTime($line->started_at, DATE_ISO8601);
		        //$f = $date->format(DATE_RFC1123);
		        $started_at_time = substr($line->started_at,10,9);
		        echo "<tr><td><code title='$line->rk_track_id'>$started_at_time</code> $f</td>";
		        if ( $wp_track_key ) {
			        $wp_post           = $wp_tracks[ $wp_track_key ];
			        $wp_post_edit_link = get_edit_post_link($wp_post->ID);
			        $wp_post_thumbnail = get_the_post_thumbnail_url( $line->wp_track_id, [ 100, 100 ] );
			        $wp_post_author = $wp_post->post_author == 0 ? 'un bel inconnu' : get_the_author_meta('display_name',$wp_post->post_author);
			        $wp_post_thumbnail_img = $wp_post_thumbnail ?
                        "<img class='thumbnail' width='25' height='25' src='$wp_post_thumbnail' alt='' />":"";
			        echo "<td>$wp_post_thumbnail_img</td><td class='tracks-history__track-title'><a href='$wp_post_edit_link'>$wp_post->post_title</a> uploadé par <strong>$wp_post_author</strong></td>";
		        } else {
			        echo "<td colspan='2'></td><td></td>";
		        }
		        echo "</tr>";
	        }
        }catch (Throwable $err){
            echo $err->getMessage();
        }

		?></tbody></table>

</div>