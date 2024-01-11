<?php


class Tokenizer {

    # note the longest splitter should be first
    private const SPLITTERS = ["\r\n", "!=", ">=", "<=", "<>", ":=", "\\", "&&", ">", "<", "|", "=", "^", "(",
        ")", "\t", "\n", "'", "\"", "`", ",", "@", " ", "+", "-", "*", "/", ";", "[", "]"];

    public function __construct()
    {
        $this->tokens       = [];
        $this->tokenSize    = max(array_map('strlen', self::SPLITTERS));
    }

    public function run(
        string $dslQuery
    ) : array
    {
        # split the query string into an array of tokens by known splitter characters like line lines, etc
        $tokens = $this->tokenize($dslQuery);

        # this seems to bring back the things that previously exploded into different tokens
        # into a single token if they were encapsulated by quote marks
        $tokens = $this->extractNestedStrings($tokens);


        # rebuild back things within parentheses
        $tokens = $this->extractNestedParentheses($tokens);

        #$tokens = $this->concatEscapeSequences($tokens);

        $tokens = $this->trimTokens($tokens);

        return $tokens;
    }



    private function trimTokens(
        array $tokens
    ) : array
    {
        foreach($tokens as $tokenIndex=>$token)
        {
            if(in_array($token, ["", " ", "\r\n"])){
                unset($tokens[$tokenIndex]);
            }
        }
        $tokens = array_values($tokens);
        return $tokens;
    }


    private function extractNestedParentheses(
        array $tokens
    ) : array
    {

        $initialTokenCount = count($tokens);
        $tokenIndex = 0;

        while ($tokenIndex < $initialTokenCount)
        {
            # if the first token isn't an opening parenthesis keep looping until you find one
            if ($tokens[$tokenIndex] !== '[')
            {
                $tokenIndex++;
                continue;
            }

            # the count is likely related to the nesting level
            $count = 1;

            # set the starting subTokenIndex to one greater than the current token
            # loop until the tokens are exhausted, or until you find a closing token
            for ($subTokenIndex = $tokenIndex + 1; $subTokenIndex < $initialTokenCount; $subTokenIndex++)
            {
                # this is the sub-token we are looking into
                $token = $tokens[$subTokenIndex];

                # if the sub-token is another opening parenthesis, increase the recursion counter
                if ($token === '[')
                {
                    $count++;
                }

                # however, if the token is actually a closing token, we know we've resolved our opened parenthesis closure
                if ($token === ']')
                {
                    $count--;
                }

                # if we are still here, the sub-token belongs inside the parent token
                # concat the token to the parent, unset it, and continue on with the inner loop
                $tokens[$tokenIndex] .= $token;
                unset($tokens[$subTokenIndex]);

                # if our closure has been resolved, we can break from the inner loop and continue traversing over
                # the other tokens to check for another open bracket initiator token
                if ($count === 0) {
                    $subTokenIndex++;
                    break;
                }
            }
            # once out of the loop, move the index to account for all the nested tokens we've looped over while looking
            # for the parenthesis closure
            $tokenIndex = $subTokenIndex;
        }

        # fix the array keys and return
        return array_values($tokens);
    }


    private function isBacktick($token)
    {
        return ($token === "'" || $token === "\"" || $token === "`");
    }


    # If we find a token that requires a closing token, such as quote marks, we need to loop over
    # all the next tokens until the closure token is located
    # Everything between the initiator and closure tokens can be concatenated into a single token
    private function bindTillClosureToken(
        $tokens,
        $startingTokenIndex,
        $closureToken
    ) {

        $initialTokenCount = count($tokens);

        # Since we already know the index of the starting token, we do not need to loop over all the tokens
        # we only need to loop for tokens that are after the starting token
        $tokenIndex = $startingTokenIndex + 1;

        # loop over the tokens
        while ($tokenIndex <  $initialTokenCount)
        {
            # grab the current token
            $token = $tokens[$tokenIndex];

            # if the current token is empty, it is obviously not a closure token, continue onto the next loop
            if (!isset($token))
            {
                $tokenIndex++;
                continue;
            }

            # since we are still here, append whatever the current token is to the initial token
            $tokens[$startingTokenIndex] .= $token;
            # unsetting is essential, as we moved the values of the concatenated tokens into the value of the startingToken
            unset($tokens[$tokenIndex]);

            # if the token we just appended also happens to be the closure token, break the loop and get out of the function
            if ($token === $closureToken)
            {
                break;
            }
            # if we are still here, we need to keep searching for the closure token
            $tokenIndex++;
        }

        # since we messed up the order of things, we need to re-order the array by the keys
        return array_values($tokens);
    }


    private function extractTabs(
        array $tokens
    ) : array
    {

        $tokenIndex = 0;

        # loop over all the tokens
        while ($tokenIndex < count($tokens)) {

            $token = $tokens[$tokenIndex];

            # if the current token is empty, we don't have anything to do
            if (!isset($token))
            {
                # increment the token counter and continue with the next loop of the while loop
                $tokenIndex++;
                continue;
            }

            # if a backtick is detected, we know that we need to pursue it until the pairing backtick that closes it
            if ($token == " ")
            {
                $tokens = $this->bindTillClosureToken($tokens, $tokenIndex, $token);
            }

            $tokenIndex++;
        }

        return $tokens;
    }



    private function extractNestedStrings(
        array $tokens
    ) : array
    {

        $tokenIndex = 0;

        # loop over all the tokens
        while ($tokenIndex < count($tokens)) {

            $token = $tokens[$tokenIndex];

            # if the current token is empty, we don't have anything to do
            if (!isset($token))
            {
                # increment the token counter and continue with the next loop of the while loop
                $tokenIndex++;
                continue;
            }

            # if a backtick is detected, we know that we need to pursue it until the pairing backtick that closes it
            if ($this->isBacktick($token))
            {

                $tokens = $this->bindTillClosureToken($tokens, $tokenIndex, $token);
            }

            $tokenIndex++;
        }

        return $tokens;
    }


    public function tokenize(
        string $dslQuery
    )
    {
        # Current Token
        $token  = "";

        # All Processed Tokens
        $tokens = [];

        # Current Character Position
        $queryCharPosition = 0;

        # Loop over every single character in the query
        while ($queryCharPosition < strlen($dslQuery))
        {

            # A special splitter token can be more than one character long
            # What we need to do is to check whether the particular selected characters are actually
            # a special splitter character
            # it is more important to select the full splitter (the 2+ character ones), thus we go backwards
            # and if a splitter token is found, we break out of both loops and continue on

            for ($tokenSelectorPosition = $this->tokenSize; $tokenSelectorPosition > 0; $tokenSelectorPosition--)
            {

                # Since we are looping character by character, we need to know the start offset & end offset
                # meaning for function print, it would see a P, then an R, then I, etc.
                # Once it sees a whitespace, it knows the last time a non-special character appeared was at the offset of 'P'
                # and it needs to select everything from the offset of 'P' to the current character/loop position
                $selection = substr($dslQuery, $queryCharPosition, $tokenSelectorPosition);

                # if the selection is an actually valid splitter
                if(in_array($selection, self::SPLITTERS))
                {
                    # if the token is already set, append it!
                    # the reason a token would already exist is the current character is a splitter meaning all the previous
                    # characters were part of the same token, which we're about to set to the array
                    if (isset($token))
                    {
                        $tokens[] = $token;
                    }
                    # append the current selection to the tokens
                    $tokens[]                 = $selection;

                    # increment the general position by the current selector-position to continue the loop
                    $queryCharPosition        += $tokenSelectorPosition;

                    # since this is a splitter, unset the current token since we have a clean slate now!
                    unset($token);

                    # will skip the current inner and outer loops
                    continue 2;
                }
            }

            # If we are still here, clearly the token isn't a splitter since the code didn't 'continue'
            # append the character at the current position and keep going
            # this will keep appending characters to the current token until a splitter is detected
            # the token
            $token .= $dslQuery[$queryCharPosition];
            $queryCharPosition++;
        }

        # if the final token was present, pop it onto the array as well since it didn't get handled by the previous loop
        if (!empty($token)) {
            $tokens[] = $token;
        }
        return $tokens;
    }




    /**
     * The primary purpose of this is to cover cases where there is an escaped character within the string
     * example: AND file = 'documents\hello_world.txt'
     * However, I am not entirely sure what this is for
     */
    private function concatEscapeSequences(
        array $tokens
    ) : array
    {

        $tokenIndex = 0;

        while ($tokenIndex < count($tokens)) {

            # if the current token literally ends with a back-slash (this doesn't mean \\)
            # since we have already broken everything out, it would mean that the first and last
            # character in this particular case is a backslash
            if (self::endsWith($tokens[$tokenIndex], "\\")) {

                # if the current token is a backslash and a token in the next position exists
                $tokenIndex++;
                if (isset($tokens[$tokenIndex]))
                {
                    # add the value of the next character to the current character and continue onwards
                    $tokens[$tokenIndex - 1] .= $tokens[$tokenIndex];
                    unset($tokens[$tokenIndex]);
                }
            }
            $tokenIndex++;
        }
        # since we break the order a little, we need to clean up the keys to work again
        return array_values($tokens);
    }


    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        $start = $length * -1;
        return (substr($haystack, $start) === $needle);
    }





}