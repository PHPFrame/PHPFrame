<?php
/**
 * PHPFrame/Mapper/XMLPersistenceFactory.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Mapper
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * XML Persistence Factory Class
 * 
 * @category PHPFrame
 * @package  Mapper
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_XMLPersistenceFactory extends PHPFrame_PersistenceFactory
{
    private $_path = null;
    
    /**
     * Constructor
     * 
     * @param string $target_class
     * @param string $table_name
     * @param string $path         [Optional]
     * 
     * @return void
     * @since  1.0
     */
    public function __construct($target_class, $table_name, $path=null)
    {
        parent::__construct($target_class, $table_name);
        
        if (!is_null($path)) {
            $this->_path = trim((string) $path);
        }
    }
    
    /**
     * Get object assembler
     * 
     * @return PHPFrame_PersistentObjectAssembler
     * @since  1.0
     */
    public function getAssembler()
    {
        return new PHPFrame_XMLPersistentObjectAssembler($this, $this->_path);
    }
    
    /**
     * Create a new IdObject to work with the target class
     * 
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function getIdObject()
    {
        $options = array("select"=>"*", "from"=>$this->getTableName());
        
        return new PHPFrame_XMLIdObject($options);
    }
}
