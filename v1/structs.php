<?php


abstract class DataToken {

    public function __construct(
        $value
    )
    {
        $this->value = $value;
    }

}
class ArrayToken extends DataToken {

    public function __construct(
        $value
    )
    {
        parent::__construct($value);
        if(is_string($value)){
            $this->value = explode(",",str_replace(["(", ")", " ", "[", "]"], "", $value));
        } else {
            $this->value = $value;
        }
    }
}
class StringToken extends DataToken {
    public function __construct(
        $value
    )
    {
        parent::__construct($value);
        $this->value = trim($value, "'\"");
    }
}
class IntToken extends DataToken {}

