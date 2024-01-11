<?php

# TOGGLE THIS VARIABLE TO TEST OUT VARIOUS EXAMPLES
$EXAMPLE = 0;

$examples = [

    [
        "version" => "v1",
        "query"   => "
            'hello world'
        ",
    ],
    [
        "version" => "v1",
        "query"   => "
            upper 'hello world'
        ",
    ],
    [
        "version" => "v1",
        "query"   => "
            upper lower 'HELLO WORLD'
        ",
    ],
    [
        "version" => "v1",
        "query"   => "
            jdecode read 'v1/sample_data/all_numbers.txt' 
        ",
    ],
    [
        "version" => "v1",
        "query"   => "
            size diff jdecode read 'v1/sample_data/all_numbers.txt' jdecode read 'v1/sample_data/even_numbers.txt'
        ",
    ],
    [
        "version" => "v1",
        "query"   => "
            put 'my_var' 'hello world'
            get 'my_var' 
        ",
    ],
    [
        "version" => "v2",
        "query"   => "
            fn my_function string string {
                a  
            }
            my_function 'hello world'
        ",
    ],
    [
        "version" => "v2",
        "query"   => "
            fn my_function string,string string {
                concat upper a concat ' ' lower b 
            }
            my_function 'is_lower' 'is_UPPER'
        ",
    ],
];



error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED);


include 'utils.php';
global $variableMapping, $functionMapping;
$variableMapping = [];
$functionMapping = [];

$VERSION = "v1";
$VERSION = $examples[$EXAMPLE]['version'];
$QUERY   = $examples[$EXAMPLE]['query'];

include "$VERSION/functions.php";
include "$VERSION/structs.php";
include "$VERSION/Structures.php";
include "$VERSION/Utils.php";
include "$VERSION/Tokenizer.php";
include "$VERSION/Lexer.php";
include "$VERSION/Interpreter.php";

$tokens = (new Tokenizer())->run($QUERY);
$tokens = (new Lexer())->run($tokens);
$output = (new Interpreter())->run($tokens);

pp($output, 1, "OUTPUT:");



