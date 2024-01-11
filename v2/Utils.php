<?php

class Utils {


    public static function insertValueAtPosition(
        $array,
        $index,
        $val
    )
    {
        $size = count($array); //because I am going to use this more than one time
        if (!is_int($index) || $index < 0 || $index > $size)
        {
            return -1;
        }
        else
        {
            $temp   = array_slice($array, 0, $index);
            $temp[] = $val;
            return array_merge($temp, array_slice($array, $index, $size));
        }
    }

    public static function listTokens(
        $tokenType
    )
    {
        $tokenType = $tokenType."Token";
        if(!in_array($tokenType, ['FunctionToken', 'ArgumentToken'])){
            Throw New TypeError("[{$tokenType}] Is Not A Valid Token Type");
        }
        $children = [];
        foreach( get_declared_classes() as $class ){
            if( is_subclass_of( $class, $tokenType ) )
                $children[] = str_replace("Token", "", $class);
        }
        return $children;
    }


    public static function isEnclosedWithinQuotes(
        string $token
    ) : bool
    {
        return (self::isBacktick($token[0]) && self::isBacktick(substr($token, -1)));
    }


    public static function isExpression(
        string $token
    ) : bool
    {
        return ($token[0] === "{" && substr($token, -1) === "}");
    }



    public static function isEnclosedWithinArray(
        string $token
    ) : bool
    {
        return (
            ($token[0] === "[" && substr($token, -1) === "]")
        );
    }

    public static function isBacktick(
        string $token
    ) : bool
    {
        return ($token === "'" || $token === "\"" || $token === "`");
    }




}