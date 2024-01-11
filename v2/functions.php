<?php


abstract class FunctionToken {
}

class upperToken extends FunctionToken
{
    public $args = [
        [
            "name" => "function_name",
            "cast" => ["stringStructure"],
            "value" => NULL,
        ],
    ];

    public $returns = "stringStructure";

    public function resolve()
    {
        return strtoupper($this->args[0]['value']);
    }
}



class lowerToken extends FunctionToken
{

    public $args = [
        [
            "name" => "function_name",
            "cast" => ["stringStructure"],
            "value" => NULL,
        ],
    ];

    public $returns = "stringStructure";


    public function resolve()
    {
        return strtolower($this->args[0]['value']);
    }
}

class concatToken extends FunctionToken
{
    public $args = [
        [
            "name" => "string a",
            "cast" => ["stringStructure"],
            "value" => NULL,
        ],
        [
            "name" => "string b",
            "cast" => ["stringStructure"],
            "value" => NULL,
        ],
    ];


    public $returns = "stringStructure";


    public function resolve()
    {
        return $this->args[0]['value'] . $this->args[1]['value'];
    }
}


class fnToken extends FunctionToken {

    public $args = [
        [
            "name"      => "function_name",
            "cast"      => ["variableToken"],
            "value"     => NULL,
        ],
        [
            "name"      => "argument(s) declaration",
            "cast"      => ["argumentListToken"],
            "value"     => NULL,
        ],
        [
            "name"      => "output declaration",
            "cast"      => ["argumentToken"],
            "value"     => NULL,
        ],

        [
            "name"      => "operation expression",
            "cast"      => ["expressionToken"],
            "value"     => NULL,
        ],

    ];

    public function resolve()
    {
        global $functionMapping;
        $functionMapping[$this->args[0]['value']] = $this->args;
    }

}





class variableToken  {

    public function __construct($value)
    {
        $this->value = $value;
    }

}


class argumentListToken extends FunctionToken {

    public $args = [
        [
            "name"      => "argument",
            "cast"      => ["argumentToken"],
            "value"     => NULL,
        ]

    ];

    public function resolve()
    {
        $abcMapping = range('A', 'Z');

        $mapping = [];
        foreach($this->args as $argIndex=>$arg)
        {
            $mapping[strtolower($abcMapping[$argIndex])] = str_replace("Token","Structure",$arg['value']);
        }
        return $mapping;
    }
}


class sacToken extends FunctionToken
{

    public $args = [
        [
            "name" => "argument",
            "cast" => ["whereToken"],
            "value" => NULL,
        ]
    ];


    public $returns = "arrayStructure";


};


class whereToken extends FunctionToken
{

//    public $args = [
//        [
//            "name" => "argument",
//            "cast" => ["whereToken"],
//            "value" => NULL,
//        ]
//    ];


};


# argument declaration
class argumentToken {

}


class intToken extends argumentToken {
}


class stringToken extends argumentToken {
}


class separatorToken {
}


class expressionToken {
    public function __construct($value)
    {
        $this->value = $value;
    }
}




