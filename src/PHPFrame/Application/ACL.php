<?php
class PHPFrame_Application_ACL extends PHPFrame_Mapper_DomainObject
{
    protected $groupid;
    protected $controller;
    protected $action;
    protected $value;
    
    public function getGroupId()
    {
        return $this->groupid;
    }
    
    public function setGroupId($int)
    {
        $this->groupid = (int) $int;
    }
    
    public function getController()
    {
        return $this->controller;
    }
    
    public function setController($str)
    {
        $this->controller = (string) $str;
    }
    
    public function getAction()
    {
        return $this->action;
    }
    
    public function setAction($str)
    {
        $this->action = $str;
    }
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function setValue($str)
    {
        $this->value = $str;
    }
}
