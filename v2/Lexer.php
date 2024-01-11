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
            # Is this token a function
            if(in_array($token, Utils::listTokens("Function"))){
                $tokenType = $token."Token";
                $tokens[$index] = new $tokenType($token);
            # Is
            } elseif(in_array($token, Utils::listTokens("Argument"))) {
                $tokenType = $token."Token";
                $tokens[$index] = new $tokenType($token);
            } elseif(Utils::isEnclosedWithinArray($token)) {
                $tokens[$index] = new arrayStructure($token);
            } elseif(Utils::isEnclosedWithinQuotes($token)) {
                $tokens[$index] = new stringStructure($token);
            } elseif(Utils::isExpression($token)) {
                $tokens[$index] = new expressionToken($token);

            } elseif(is_numeric($token)) {
                $tokens[$index] = new intStructure($token);
            } elseif($token === ",") {
                $tokens[$index] = new separatorToken();
            } else {
                $tokens[$index] = new variableToken($token);
            }
        }
        return $tokens;
    }



}