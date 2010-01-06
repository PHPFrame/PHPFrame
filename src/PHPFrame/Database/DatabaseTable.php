<?php
/**
 * PHPFrame/Database/DatabaseTable.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Database
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since     1.0
 */

/**
 * Database Table Class
 * 
 * @category PHPFrame
 * @package  Database
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
class PHPFrame_DatabaseTable
{
    private $_db, $_name, $_columns;
    
    public function __construct(PHPFrame_Database $db,  $name)
    {
        $this->_db      = $db;
        $this->_name    = $name;
        $this->_columns = new SplObjectStorage();
        
        if ($db->hasTable($this->getName())) {
            foreach ($db->getColumns($this->getName()) as $col) {
                $this->addColumn($col);
            }
        }
    }
    
    public function getName()
    {
        return $this->_name;
    }
    
    public function getColumns()
    {
        return iterator_to_array($this->_columns);
    }
    
    public function getRows()
    {
        $sql = "SELECT * FROM ".$this->getName();
        
        return $this->_db->fetchAssocList($sql);
    }
    
    public function addColumn(PHPFrame_DatabaseColumn $column)
    {
        $this->_columns->attach($column);
    }
    
    public function removeColumn(PHPFrame_DatabaseColumn $column)
    {
        $this->_columns->detach($column);
    }
}
