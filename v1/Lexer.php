<?php



class Lexer {

    public function __construct(

    )
    {
    }

    public function run(
        array $tokens
    ) : array
    {
        foreach($tokens as $index=>$token)
        {
            # Is this a function token?
            if(in_array($token, Utils::listTokens("FunctionToken")))
            {
                $className = "{$token}Token";
                $tokens[$index] = new $className; # instantiate the token's object
            } elseif(Utils::isEnclosedWithinArray($token))
            {
                # Array token
                $tokens[$index] = new ArrayToken($token);
            } elseif(Utils::isEnclosedWithinQuotes($token))
            {
                # String Token
                $tokens[$index] = new StringToken($token);
            } elseif(is_numeric($token))
            {
                # Int Token
                $tokens[$index] = new IntToken($token);
            } else
            {
                Throw new ParseError("Unknown Token Type For [{$token}]");
            }

        }

        return $tokens;
    }



}