<?php
/**
 * PHPFrame/Mapper/XMLPersistenceFactory.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Mapper
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * XML Persistence Factory Class
 * 
 * @category PHPFrame
 * @package  Mapper
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Mapper_XMLPersistenceFactory extends PHPFrame_Mapper_PersistenceFactory
{
    private $_path = null;
    
    /**
     * Constructor
     * 
     * @param string $target_class
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($target_class, $table_name, $path=null) {
        parent::__construct($target_class, $table_name);
        
        if (!is_null($path)) {
            $this->_path = trim((string) $path);
        }
    }
    
    /**
     * Get object assembler
     * 
     * @access public
     * @return PHPFrame_Mapper_DomainObjectAssembler
     * @since  1.0
     */
    public function getAssembler()
    {
        return new PHPFrame_Mapper_XMLDomainObjectAssembler($this, $this->_path);
    }
    
    /**
     * Create a new IdObject to work with the target class
     * 
     * @access public
     * @return PHPFrame_Mapper_IdObject
     * @since  1.0
     */
    public function getIdObject()
    {
        $options = array("select"=>"*", "from"=>$this->getTableName());
        
        return new PHPFrame_Mapper_XMLIdObject($options);
    }
}
