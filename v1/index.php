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

global $variableMapping;
$variableMapping = [];

include "functions.php";
include "structs.php";
include "Utils.php";
include "Tokenizer.php";
include "Lexer.php";
include "Interpreter.php";

$query = "
put 'my_var' 'hello world' 
diff jdecode read 'sample_data/all_numbers.txt' jdecode read 'sample_data/even_numbers.txt'
";



# difference between two files
//$query = "
//diff jdecode read 'sample_data/all_numbers.txt' jdecode read 'sample_data/even_numbers.txt'
//";
//$query = "
//put 'my_var' 'hello world'
//get 'my_var'
//";

//$query = "
//fn my_function string string {
//    upper a
//}
//my_function 'hey'
//
//";




$tokens = (new Tokenizer())->run($query);
$tokens = (new Lexer())->run($tokens);
$output = (new Interpreter())->run($tokens);


pp($output, 1, "output");



