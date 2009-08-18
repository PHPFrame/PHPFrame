<?php
class PHPFrame_Addons_Plugin extends PHPFrame_Mapper_DomainObject
{
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
