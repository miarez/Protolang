<?php


abstract class FunctionToken {}
class printToken extends FunctionToken {
    public $args = [
        [
            "cast"      => ["StringToken"],
            "value"     => NULL,
        ]
    ];
    public $resolved = -1;
}




class reverseToken extends FunctionToken {
    public $args = [
        [
            "cast"      => ["StringToken"],
            "value"     => NULL,
        ]
    ];
    public $returns     = "StringToken";

    public function resolve()
    {
        return strrev($this->args[0]['value']);
    }
}



class substrToken extends FunctionToken {
    public $args = [
        [
            "cast"      => ["IntToken"],
            "value"     => NULL,
        ],
        [
            "cast"      => ["IntToken"],
            "value"     => NULL,
        ],
        [
            "cast"      => ["StringToken"],
            "value"     => NULL,
        ]
    ];
    public $returns     = "StringToken";

    public function resolve()
    {
        return substr($this->args[2]['value'], $this->args[0]['value'], $this->args[1]['value']);
    }
}



class sizeToken extends FunctionToken {
    public $args = [
        [
            "cast"      => ["StringToken","ArrayToken"],
            "value"     => NULL,
        ]
    ];

    public $returns     = "IntToken";
    public function resolve()
    {
        if(is_string($this->args[0]['value'])){
            return strlen($this->args[0]['value']);
        } elseif(is_array($this->args[0]['value'])) {
            return sizeof($this->args[0]['value']);
        } else {
            Throw New ParseError("Invalid Type Received By Size");
        }


    }
}
class upperToken extends FunctionToken {
    public $args = [
        [
            "cast"      => ["StringToken"],
            "value"     => NULL,
        ]
    ];
    public $returns     = "StringToken";

    public function resolve()
    {
        return strtoupper($this->args[0]['value']);
    }
}
class lowerToken extends FunctionToken {
    public $args = [
        [
            "cast"      => ["StringToken"],
            "value"     => NULL,
        ]
    ];
    public $returns     = "StringToken";

    public function resolve()
    {
        return strtolower($this->args[0]['value']);
    }
}
class concatToken extends FunctionToken {
    public $args = [
        [
            "cast"      => ["StringToken"],
            "value"     => NULL,
        ],
        [
            "cast"      => ["StringToken"],
            "value"     => NULL,
        ]
    ];
    public $returns     = "StringToken";

    public function resolve()
    {
        return $this->args[0]['value'].$this->args[1]['value'];
    }
}


class diffToken extends FunctionToken {
    public $args = [
        [
            "cast"      => ["ArrayToken"],
            "value"     => NULL,
        ],
        [
            "cast"      => ["ArrayToken"],
            "value"     => NULL,
        ]
    ];
    public $returns     = "ArrayToken";

    public function resolve()
    {
        return array_values(array_diff($this->args[0]['value'], $this->args[1]['value']));
    }
}
class jdecodeToken extends FunctionToken {
    public $args = [
        [
            "cast"      => ["StringToken"],
            "value"     => NULL,
        ]
    ];
    public $returns     = "ArrayToken";

    public function resolve()
    {
        return json_decode($this->args[0]['value'], TRUE);
    }
}

class readToken extends FunctionToken
{
    # What arguments does this token expect?
    public $args = [
        # expects only 1 argument
        [
            # expects the argument to be a string Token
            "cast"      => ["StringToken"],
            "value"     => NULL,
        ]
    ];
    # technically it returns a JSON but php doesn't support JSON types natively
    public $returns     = "StringToken";

    # how to actually apply this function?
    public function resolve()
    {
        return file_get_contents($this->args[0]['value']);
    }
}

class mergeToken extends FunctionToken {
    public $args = [
        [
            "cast"      => ["ArrayToken"],
            "value"     => NULL,
        ],
        [
            "cast"      => ["ArrayToken"],
            "value"     => NULL,
        ]
    ];
    public $returns     = "ArrayToken";

    public function resolve()
    {
        return array_merge($this->args[0]['value'], $this->args[1]['value']);
    }
}
class throughToken extends FunctionToken {
    public $args = [
        [
            "cast"      => ["ArrayToken"],
            "value"     => NULL,
        ]
    ];
    public $returns     = "ArrayToken";

    public function resolve()
    {
        return $this->args[0]['value'];
    }
}





class putToken extends FunctionToken {

    public $args = [
        [
            "cast"      => ["StringToken"],
            "value"     => NULL,
        ],
        [
            "cast"      => ["StringToken"],
            "value"     => NULL,
        ]
    ];
    public $returns     = "Stop";

    public function resolve()
    {
        global $variableMapping;
        $variableMapping[$this->args[0]['value']] = $this->args[1]['value'];
        return $this->args[1]['value'];
    }
}
class getToken extends FunctionToken {

    public $args = [
        [
            "cast"      => ["StringToken"],
            "value"     => NULL,
        ]
    ];
    public $returns     = "StringToken";

    public function resolve()
    {
        global $variableMapping;
        return $variableMapping[$this->args[0]['value']];
    }
}

# plot data line x '.host' y '.clicks' div '.revenue' 100
class plotToken extends FunctionToken {

    public $args = [
        [
            "name"      => "geometry",
            "cast"      => ["GeomToken"],
            "value"     => NULL,
        ],
        [
            "name"      => "x",
            "cast"      => ["xToken"],
            "value"     => NULL,
        ]
    ];


}



abstract class GeomToken {
}

class pointToken extends GeomToken {
}
class lineToken extends GeomToken {
}


class xToken extends FunctionToken {
    public $args = [
        [
            "name"      => "xAxis",
            "cast"      => ["StringToken"],
            "value"     => NULL,
        ],
    ];

    public $returns     = "xToken";

    public function resolve()
    {
        return $this->args[0]['value'];
    }



}
