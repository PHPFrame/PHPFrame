<?php
class PHPFrame_Addons_Lib extends PHPFrame_Mapper_DomainObject
{
    protected $name="";
    protected $author="";
    protected $enabled=false;
    protected $version="";
     
    /**
     * Constructor
     * 
     * @param array $options
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        parent::__construct($options);
    }
    
    protected function _doToArray($array)
    {
        return $array;
    }
}
