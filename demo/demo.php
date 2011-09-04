<?php

if (isset($_POST['text'])) {

    //echo json_encode(array('html'=>str_replace(array('<',"\n"), array('&lt;','<br />'), $_POST['text'])));
    require '../wiki-v3.lib.php';
    //echo "Hello World";
    //echo json_encode(array('html'=>wiki_parse_string($_POST['text'])));
    echo wiki_parse_string($_POST['text']);
}
