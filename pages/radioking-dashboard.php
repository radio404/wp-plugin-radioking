<?php

$page_title = __( 'Tableau de bord RadioKing', 'radio404' );

?><div class="wrap">
	<h1><?= $page_title ?></h1>

    <pre><?php

        $radioking_planning = radioking_get_week_planned();

        echo json_encode($radioking_planning,JSON_PRETTY_PRINT);

        ?></pre>

</div>