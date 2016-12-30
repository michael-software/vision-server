<?php

require_once dirname(dirname(__FILE__)) . '/includes/AsyncManager.php';

if(!empty($_POST['plugin']) && !empty($_POST['action'])) {
    $value = '';
    if(!empty($_POST['value'])) {
        $value = $_POST['value'];
    }

    $asyncManager = new AsyncManager($_POST['plugin']);
    $asyncManager->triggerAction($_POST['action'], $value);
}


?>