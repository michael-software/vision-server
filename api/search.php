<?php

require dirname(dirname(__FILE__)) . '/includes/LoginManager.php';

if(!empty($_GET['query'])) {
	require_once dirname(dirname(__FILE__)).'/includes/SearchManager.php';
	
	$searchManager = new SearchManager();
	$array = $searchManager->getArray($_GET['query']);


    if(!empty($_GET['format']) && $_GET['format'] == 'jui') {
        $jUI = new JUI\Manager();


        $list = new JUI\ListView();

        for($i = 0, $z = count($array); $i < $z; $i++) {
            $entry = $array[$i];

            $list->addItem($entry['title'], $entry['click']);
        }

        $jUI->add($list);

        echo $jUI->getJsonString();
    } else if(!empty($array) && $array != null) {
		echo json_encode($array);
	}
}

?>