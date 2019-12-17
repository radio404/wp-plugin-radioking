<?php

$import_page_title = __( 'Syncronisation du planning RadioKing', 'radio404' );
try {
	$access_token      = radioking_get_token();
	$track_boxes = radioking_get_track_box( $access_token );
	$count       = $track_boxes[0]->count;

}catch (Exception $err){
	$track_boxes =[];
    $count = 0;
}


//*/
?><div class="wrap">
	<h1><?= $import_page_title ?></h1>

    <pre><?php


        ?></pre>


    <button type="button" class="button button-primary start-schedule-import">Lancer la synchronisation des programmations</button>
    <span class="import-progress-label"></span>
    <script>
		var radioking_access_token = "<?= $access_token ?>";
	</script>

    <progress class="import-progress" min="0" max="<?= $count ?>" value="0"></progress>

    <div class="planning">
        <div class="planning__container">
        <?php for($i=0; $i<7; $i++){
            $d = ($i+1)%7;
            echo "<div class='planning__day day-$d' data-day='$d'></div>";
        } ?>
        </div>
    </div>

</div>