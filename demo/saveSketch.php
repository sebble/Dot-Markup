<?php

//echo $_SERVER['REQUEST_URI'];

//data:image/png;base64,
//1234567890123456789012

$png = base64_decode(substr($_REQUEST['pic'],22));

function genRandomString() {
    $length = 10;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $string = '';    
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters)-1)];
    }
    return $string;
}

$filename = genRandomString();

echo $filename;

file_put_contents('sketches/'.$filename.'.png', $png);
