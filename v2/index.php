<?php


global $functionMapping;
$functionMapping = [];

include "functions.php";
include "Structures.php";
include "Utils.php";
include "Tokenizer.php";
include "Lexer.php";
include "Interpreter.php";

$query = "
fn my_function string,string string {
    concat upper a concat ' ' lower b   
}
my_function 'hey' 'FRIEND'


";

//$query = "
//read 'test.txt'
//";



$tokens = (new Tokenizer())->run($query);




$tokens = (new Lexer())->run($tokens);


//pp($tokens, 1);

$output = (new Interpreter())->run($tokens);


pp($output, 1, "output");



