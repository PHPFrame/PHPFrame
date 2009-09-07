<?php
/**
 * PHPFrame/Mapper/DomainObjectAssembler.php
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
 * DomainObjectAssembler Class
 * 
 * This is an abstract class that will need to be extended by all i
 * 
 * @category PHPFrame
 * @package  Mapper
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
abstract class PHPFrame_DomainObjectAssembler
{
    /**
     * Reference to the factory object to use
     * 
     * @var PHPFrame_PersistenceFactory
     */
    protected $factory=null;
    
    /**
     * Constructor
     * 
     * @param PHPFrame_PersistenceFactory $factory
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_PersistenceFactory $factory)
    {
        $this->factory = $factory;
    }
    
    /**
     * Find a domain object using an IdObject or numeric id
     * 
     * @param PHPFrame_IdObject|int $id_obj
     * 
     * @access public
     * @return PHPFrame_DomainObject
     * @since  1.0
     */
    abstract public function findOne($id_obj);
    
    /**
     * Find a collection of domain objects using an IdObject
     * 
     * @param PHPFrame_IdObject $id_obj
     * 
     * @access public
     * @return PHPFrame_DomainObjectCollection
     * @since  1.0
     */
    abstract public function find(PHPFrame_IdObject $id_obj=null);
    
    /**
     * Persist domain object
     * 
     * @param PHPFrame_DomainObject $obj
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    abstract public function insert(PHPFrame_DomainObject $obj);
    
    /**
     * Delete domain object
     * 
     * @param PHPFrame_DomainObject $obj
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    abstract public function delete(PHPFrame_DomainObject $obj);
}
