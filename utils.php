<?php


function pp(
    $a,
    int     $exit   =0,
    string  $label  =''
) : void
{
    echo "<PRE>";
    if($label) echo "<h5>$label</h5>";
    if($label) echo "<title>$label</title>";
    echo "<pre>";
    print_r($a);
    echo '</pre>';
    if($exit) exit();
}