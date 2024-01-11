<?php


class Interpreter {


    public function __construct()
    {
        $this->variables = [];
    }

    public function run(
        array $tokens
    )
    {
        global $functionMapping;

        $memory     = NULL;

        $index      = 0;

        # fails safe
        $maxLoops   = 100;
        $loops      = 0;

        while (!empty($tokens))
        {

            $loops++;
            if($loops >= $maxLoops){
                Throw new Error("Max Recursion Hit");
            }
            $token = $tokens[$index];

            if(is_a($token, "FunctionToken"))
            {

                foreach($token->args as $argIndex=>$arg)
                {

                    if($arg['value'] !== NULL)
                    {
                        continue;
                    }

                    $nextIndex = $index + 1;

                    $nextToken = $tokens[$nextIndex];

                    if(!$nextToken)
                    {
                        Throw new ArgumentCountError("Too Few Tokens");
                    }

                    $nextTokenClass         = get_class($nextToken);
                    $nextTokenParentClass   = get_parent_class($nextToken);


                    if(
                        in_array($nextTokenClass, $arg["cast"])         ||
                        in_array($nextTokenParentClass, $arg["cast"])
                    )
                    {

                        if($nextTokenParentClass === "Structure")
                        {
                            # if the next token is a structure and resolves my current argument
                            # set the value, remove token, continue on foreach
                            $token->args[$argIndex]["value"] = $nextToken->value;
                            unset($tokens[$nextIndex]);
                            $tokens = array_values($tokens);
                            continue;
                        }
                        else if($nextTokenClass === "variableToken")
                        {
                            # If the next token is a variable, variables are self resolving
                            # this means we set the value of the variable to the current argument
                            # unset the next token, reset the index,
                            # and continue on with the current foreach argument list loop
                            $token->args[$argIndex]["value"] = $nextToken->value;
                            unset($tokens[$nextIndex]);
                            $tokens = array_values($tokens);
                            continue;
                        }
                        elseif($nextTokenParentClass === "argumentToken")
                        {

                            $token->args[$argIndex]["value"] = $nextTokenClass;
                            unset($tokens[$nextIndex]);
                            $tokens = array_values($tokens);

                            # todo i dont like this
                            # check whether the next token is an argument separator
                            $nextToken              = $tokens[$nextIndex];

                            if(!$nextToken)
                            {
                                Throw new ArgumentCountError("Too Few Tokens NESTED");
                            }

                            $nextTokenClass         = get_class($nextToken);
                            $nextTokenParentClass   = get_parent_class($nextToken);


                            if($nextTokenClass === "separatorToken")
                            {
                                # if the next token is an argument separator, we need to stay within the scope of the argument list
                                # we will push another argument requirement to the current argument list
                                # and without moving the current index, go through the parsing process again
                                $token->args[$argIndex+1] = [
                                    "name"  => "argument " . ($argIndex+1),
                                    "cast"  => ["argumentToken"],
                                    "value" => NULL,
                                ];

                                # however, the separator token has done all we need it to do, unset it and keep going
                                unset($tokens[$nextIndex]);
                                $tokens = array_values($tokens);
                                continue 2;

                            }
                            else
                            {
                                # if the next token is not a separator, that must mean we have fulfilled the argument list
                                # we should move to the previous index and continue parsing

                                if(get_class($token) == "argumentListToken")
                                {
                                    $token->value = $token->resolve();
                                    $index--;
                                }
                                continue 2;
                            }

                        }
                        elseif($nextTokenClass === "argumentListToken")
                        {

                            # since the next token is required to be an argument list, we should check whether
                            # we have already resolved the argument list
                            if($nextToken->value !== NULL)
                            {

                                # if the argument has been resolved, we resolve it, unset, and continue on with
                                # our argument loop
                                $token->args[$argIndex]["value"] = $nextToken->resolve();
                                unset($tokens[$nextIndex]);
                                $tokens = array_values($tokens);
                                continue;
                            }
                            else
                            {
                                Throw new ParseError("Next Token Is an Argument List and is not resolved");
                            }
                        }
                        elseif($nextTokenClass === "expressionToken")
                        {
                            $token->args[$argIndex]["value"] = $nextToken->value;
                            unset($tokens[$nextIndex]);
                            $tokens = array_values($tokens);
                            continue;
                        } else
                        {
                            Throw new ParseError("Next Token Type Not Accounted For");
                        }
                    } else {

                        if($arg["cast"][0] === "argumentListToken")
                        {
                            $tokens = Utils::insertValueAtPosition($tokens, $nextIndex, new argumentListToken());
                            $index++;
                            continue 2;
                        } else {
                            $index++;
                            continue 2;
                        }
                    }
                }

                # todo this kinda sucks, it works, but sucks
                if(get_class($token) === "fnToken")
                {
                    $token->resolve();
                    unset($tokens[$index]);
                    $tokens = array_values($tokens);
                    continue;
                }

                $tokens[$index] = new $token->returns($token->resolve());
                if(sizeof($tokens) === 1){
                    $memory = $tokens[$index]->value;
                    break;
                }
                $index--;
                continue;

            } elseif(get_parent_class($token) === "Structure")
            {
                $memory = $token->value;
                unset($tokens[$index]);
                $tokens = array_values($tokens);

            } elseif($functionMapping[$token->value])
            {

                $function       = $functionMapping[$token->value];

                $fnArguments    = $function[1]['value'];
                $fnExpression   =  trim(trim($function[3]['value'], "{"), "}");

                foreach($fnArguments as $argKey=>$arg)
                {
                    $nextIndex = $index + 1;
                    $nextToken = $tokens[$nextIndex];

                    if(get_class($nextToken) !== $arg){
                        Throw new ParseError("Custom Function Is Bad");
                    }
                    $fnArguments[$argKey] = $nextToken->value;
                    unset($tokens[$nextIndex]);
                    $tokens = array_values($tokens);

                    if($arg == "stringStructure")
                    {
                        $fnExpression = preg_replace("/\s$argKey\s/", " '".$nextToken->value ."' ", $fnExpression);
                    }
                    else
                    {
                        Throw new ParseError("Unknown SubToken Type");
                    }
                }
                $subTokens  = (new Tokenizer())->run($fnExpression);
                $subTokens  = (new Lexer())->run($subTokens);
                $output     = (new Interpreter())->run($subTokens);

                $outputType = str_replace("Token" , "Structure", $function[2]["value"]);
                $tokens[$index] = new $outputType($output);
                if(sizeof($tokens) === 1)
                {
                    $memory = $tokens[$index]->value;
                    break;
                }
                $index--;

            }
            else
            {

                pp($tokens, 1, "index $index");
                Throw new TypeError("Unaccounted For Type");
            }
        }


        return $memory;
    }

}