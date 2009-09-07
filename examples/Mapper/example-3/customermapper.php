<?php
class CustomerMapper extends PHPFrame_Mapper
{
    public function __construct()
    {
        parent::__construct("Customer", "#__customers");
    }
}