<?php

if (isset($_POST['text'])) {

    //echo json_encode(array('html'=>str_replace(array('<',"\n"), array('&lt;','<br />'), $_POST['text'])));
    require '../wiki-v3.lib.php';
    //echo "Hello World";
    //echo json_encode(array('html'=>wiki_parse_string($_POST['text'])));
    echo wiki_parse_string($_POST['text']);
    
    if (isset($_POST['filename'])) {
        
        $_POST['filename'] = preg_replace('#[^a-z0-9_\.\-]#i','',$_POST['filename']);
    
        file_put_contents('documents/'.$_POST['filename'].'.dm.autosave', $_POST['text']);
    }
}
