<?php
/**
 * PHPFrame/Mapper/PersistenceFactory.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Mapper
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * PersistenceFactory Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Mapper
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Mapper_PersistenceFactory
{
    /**
     * Target class
     * 
     * @var string
     */
    private $_target_class=null;
    
    /**
     * Constructor
     * 
     * @param string $target_class
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($target_class)
    {
        $this->_target_class = (string) trim($target_class);
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
        return new PHPFrame_Mapper_DomainObjectAssembler($this);
    }
    
    /**
     * Get DomainObjectFactory
     * 
     * @access public
     * @return PHPFrame_Mapper_DomainObjectFactory
     * @since  1.0
     */
    public function getDomainObjectFactory()
    {
        return new PHPFrame_Mapper_DomainObjectFactory($this->_target_class);
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
        
        return new PHPFrame_Mapper_IdObject($options);
    }
    
    /**
     * Get Collection
     * 
     * @access public
     * @return PHPFrame_Mapper_Collection
     * @since  1.0
     */
    public function getCollection(array $raw=null)
    {
        return new PHPFrame_Mapper_Collection($raw, $this->getDomainObjectFactory());
    }
    
    /**
     * Get target class
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getTargetClass()
    {
        return $this->_target_class;
    }
    
    /**
     * Get table name
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getTableName()
    {
        // Remove PHPFrame_ prefix if needed
        $table_name = str_replace("PHPFrame_", "#__", $this->getTargetClass());
        // Make string lower case and add trailing "s"
        $table_name = strtolower($table_name)."s";
        
        return $table_name;
    }
}
