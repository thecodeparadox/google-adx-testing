<?php

function debug($arg, $exit = true) {
    //if ( !isset($_GET['debug']) ) return false;

    $bt =  debug_backtrace();
    $file = $bt[0]['file'];
    $line = $bt[0]['line'];
    $bt = null;

    echo '<div style="margin:10px auto;border:1px solid #bbb;background: #eee;padding: 10px;border-radius:5px;">';
    echo '<p style="font-size: 16px;font-weight: bold">';
    echo sprintf('File:: %s (Line:: %s)', $file, $line);
    echo '</p>';
    echo '<pre>';
    if(is_array($arg)) {
        print_r($arg);
    } elseif(is_string($arg)) {
        print $arg;
    } else {
        var_dump($arg);
    }
    echo '</pre></div>';
    if($exit) exit();
}