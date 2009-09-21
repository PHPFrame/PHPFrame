<?php
class PHPFrame_AccessDeniedException extends RuntimeException
{
    public function __construct($msg="")
    {
        parent::__construct($msg, 401);   
    }
}
