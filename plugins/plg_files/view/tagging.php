<?php
$command = $pluginManager->getCommand();

if(!empty($command)) {
    $folder = implode('/', $pluginManager->getCommand()).'/';
} else {
    $folder = "";
}

if($folder == "./") {
    $folder = "";
}

$folder = str_replace('//', '/', $folder);
$folder = str_replace('..', '.', $folder);



$current = $pluginManager->getSimpleStorage('current_' . md5($folder), 0);

if(!empty($_POST['file'])) {
    if(!empty($_POST['tags'])) {
        $tags = json_decode($_POST['tags']);
    } else {
        $tags = array();
    }

    $pluginManager->fileManager->setTags($_POST['file'], $tags);

    $current++;
    $pluginManager->setSimpleStorage('current_' . md5($folder), $current);
}



$folderContent = $pluginManager->fileManager->getFolder($folder);
$i = 0;
$filePath = null;

foreach($folderContent as $element) {
    if(FileManager::isVisible($element['name']) || $pluginManager->getTemporary('showHidden', false)) {
        if ($element['type'] == "image") {

            if($i == $current) {
                //echo $element['name'];
                $image = new JUI\Image($pluginManager->fileManager->getImageHash($folder . '/' . $element['name']));
                $image->setWidth("100%");
                $jUI->add($image);

                $filePath = $folder . '/' . $element['name'];
            }

            $i++;
        }
    }
}

if(!empty($filePath)) {
    $tags = $pluginManager->fileManager->getTagsForFile($filePath);


    $tagNames = array();

    if(is_array($tags))
    foreach ($tags as $tag) {
        $tagNames[] = $tag['name'];
    }


    $userTagsArray = $pluginManager->fileManager->getTags();
    $userTags = array();

    if(is_array($userTagsArray))
        foreach ($userTagsArray as $tag) {
            $userTags[] = $tag['name'];
        }

    $jUI->nline(2);

    $autoinput = new JUI\AutoInput('tags');
    $autoinput->setValue($tagNames);
    $autoinput->setPredefined($userTags);
    $jUI->add($autoinput);

    $input = new JUI\Input('file');
    $input->setValue($filePath);
    $input->setVisible(JUI\View::GONE);
    $jUI->add($input);

    //var_dump($pluginManager->fileManager->getTags());
    $jUI->nline();

    $submit = new JUI\Button('Speichern');
    $submit->setClick( new JUI\Click( JUI\Click::submit ) );
    $submit->setWidth("50%");
    $jUI->add($submit);

    $back = new JUI\Button('Zurück');
    $back->setClick( new JUI\Click( JUI\Click::openPlugin, $pluginManager, 'home', $folder ) );
    $back->setWidth("50%");
    $jUI->add($back);
} else {
    $pluginManager->setSimpleStorage('current_' . md5($folder), 0);
    $pluginManager->redirect( $pluginManager, 'home', $folder );
}

?>