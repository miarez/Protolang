<?php


class Utils {


    public static function listTokens(
        $tokenType
    )
    {
        if(!in_array($tokenType, ['FunctionToken', 'GeomToken', 'AxisToken'])){
            Throw New TypeError("[{$tokenType}] Is Not A Valid Token Type");
        }
        $children = [];
        foreach( get_declared_classes() as $class ){
            if( is_subclass_of( $class, $tokenType ) )
                $children[] = str_replace("Token", "", $class);
        }
        return $children;
    }

    public static function listFunctionTokens() : array
    {
        $children = [];
        foreach( get_declared_classes() as $class ){
            if( is_subclass_of( $class, 'FunctionToken' ) )
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


    public static function isEnclosedWithinArray(
        string $token
    ) : bool
    {
        return (
            ($token[0] === "(" && substr($token, -1) === ")") ||
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