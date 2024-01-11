<?php


abstract class Structure {

    public function __construct(
        $value
    )
    {
        $this->value = $value;
    }
}

class intStructure extends Structure {

    public function __construct(
        $value
    )
    {
        parent::__construct($value);
        $this->value = (int) $value;
    }

}


class arrayStructure extends Structure {

    public function __construct(
        $value
    )
    {
        parent::__construct($value);
        $value = explode(",",trim(trim($value, "["), "]"));
        $this->value = (array) $value;
    }

}



class stringStructure extends Structure {

    public function __construct(
        $value
    )
    {
        parent::__construct($value);
        $this->value = trim($value, "'");
    }


}





