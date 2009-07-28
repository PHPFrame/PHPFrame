<?php
class PHPFrame_User_Mapper
{
    public function store($obj)
    {
        if (!$obj instanceof PHPFrame_User) {
            $msg = get_class($this)." can only map objects of type PHPFrame_User";
            throw new PHPFrame_Exception($msg);
        }
        
        var_dump($obj); exit;
    }
}

