<?php

$import_page_title = __( 'Syncronisation des pistes RadioKing', 'radio404' );
try {
	$access_token      = radioking_get_token();
	$track_boxes = radioking_get_track_box( $access_token );
	$count       = $track_boxes[0]->count;

}catch (Exception $err){
	$track_boxes =[];
    $count = 0;
}
/*/
$queries = ["DELETE FROM wp_posts WHERE post_type in('album','artist','track')",
'DELETE FROM wp_postmeta WHERE post_id NOT IN (SELECT id FROM wp_posts)',
'DELETE FROM wp_term_relationships WHERE object_id NOT IN (SELECT id FROM wp_posts)'];
foreach ($queries as $query){
	global $wpdb;
	try {
	    $wpdb->query( $query );
    }catch (Throwable $err){
        echo "<pre>$err</pre>";
    }
}

/* /
$args = array('post_type'=>'attachment','nopaging'=>true,'post_status'=>'any');
$query = new WP_Query($args);
if($query->posts){
	foreach($query->posts as $attachment){
		// here your code
		$delete = wp_delete_attachment($attachment->ID,true);
		echo "\n DELETE $attachment->ID ".($delete?'OK':'KO');
	}
}

//*/
?><div class="wrap">
	<h1><?= $import_page_title ?></h1>

    <pre><?php

        if(isset($err)) echo $err->getMessage();

        //$cover = get_cover_by_album('Mood Swings','Stig Of The Dump','d21b24f2-fc71-497a-9be6-61ed0f0205b1');

        //var_dump($cover);

        ?></pre>

    <select name="track-boxes" id="track-boxes" class="track-boxes-select">

    <?php foreach ($track_boxes as $trackbox){

        $names = [
          '__MUSIC__' => 'Musique',
          '__IDENTIFICATION__' => 'Habillage radio',
          '__PODCAST__' => 'Podcast',
          '__AD__' => 'Publicité',
          '__CHRONIC__' => 'Chronique',
          '__DEDICATION__' => 'Dédicace',
        ];
        $disabled = $trackbox->count===0 ? ' disabled':'';

        $name = $names[$trackbox->name] ?? $trackbox->name;
        echo "<option value='$trackbox->idtrackbox' $disabled data-name='$trackbox->name'>$name ($trackbox->count)</option>";

    } ?>
    </select>

    <select name="days-restriction" id="days-restriction" class="days-restriction">
        <option value="-1">depuis toujours</option>
        <option value="90">depuis 90 jours</option>
        <option value="30">depuis 30 jours</option>
        <option value="7">depuis une semaine</option>
        <option value="3">depuis 3 jours</option>
        <option value="1">depuis hier</option>
        <option value="0">d'aujourd'hui</option>
    </select>

    <button type="button" class="button button-primary start-tracks-import">Lancer la synchronisation des <span class="trackbox-count"><?= $count ?></span> morceaux</button>
    <span class="import-progress-label"></span>
    <script>
		var radioking_access_token = "<?= $access_token ?>";
		var track_boxes = <?= json_encode($track_boxes) ?>;
	</script>

    <progress class="tracks-import-progress" min="0" max="<?= $count ?>" value="0"></progress>

    <div class="tracks-import-list"></div>

</div>