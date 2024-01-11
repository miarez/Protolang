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
                    if(!empty($token->args[$argIndex-1]['value']))
                    {
                        $nextTokenIndex = $index+1;

                    } else {
                        $nextTokenIndex = $index+$argIndex+1;
                    }

                    if(!empty($token->args[$argIndex]['value']))
                    {
                        continue;
                    }

                    $nextToken      = $tokens[$nextTokenIndex];


                    if(!$nextToken){
//                        $alertResponse = Alert::registerError(Alert::MISSING_TOKEN);


                        $class = get_class($token);
                        $types = implode(",",$arg['cast']);


                        Throw new ParseError("[{$class}] Requires Argument [$argIndex] To Be Of Type(s) [$types], NULL Provided");
                    }

                    if(is_a($nextToken, "DataToken")) {


                        $token->args[$argIndex]['value'] = $nextToken->value;
                        unset($tokens[$nextTokenIndex]);
                        $tokens = array_values($tokens);
                        continue;

                    } else {
                        $index = $nextTokenIndex;
                        continue 2;
                    }
                }



                if($token->returns == "Stop")
                {
                    unset($tokens[$index]);
                    $token->resolve();
                    $tokens = array_values($tokens);
                } else {


                    # if I am here, I have all the arguments for my token set
                    $tokens[$index] = new $token->returns($token->resolve());
                    if(sizeof($tokens) !== 1){
                        $index--;
                    }
                }


            } else {
                $memory = $token->value;
                unset($tokens[$index]);
                $tokens = array_values($tokens);
            }

        }

        return $memory;
    }

}
