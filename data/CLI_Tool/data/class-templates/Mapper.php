<?php
class MyExtendedMapper extends PHPFrame_Mapper
{
    public function __construct(PHPFrame_Database $db)
    {
        parent::__construct("target_class", $db, "table_name")
    }

    public function insert(target_class $db)
    {

    }
}
