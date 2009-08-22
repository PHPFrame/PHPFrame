<?php
class PHPFrame_Application_Libraries
{
    /**
     * A mapper object used to store and retrieve libraries info
     *
     * @var PHPFrame_Mapper_Collection
     */
    private $_mapper;
    /**
     * A collection object holding libraries info
     *
     * @var PHPFrame_Mapper_Collection
     */
    private $_libs;
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct() 
    {
        // Get installed plugins from file
        $this->_mapper = new PHPFrame_Mapper(
            "PHPFrame_Addons_LibInfo", 
            "lib", 
            PHPFrame_Mapper::STORAGE_XML, 
            false, 
            PHPFRAME_CONFIG_DIR
        );
        
        $this->_libs = $this->_mapper->find();
    }
}
