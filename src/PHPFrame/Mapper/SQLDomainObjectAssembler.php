<?php
/**
 * PHPFrame/Mapper/SQLDomainObjectAssembler.php
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
 * SQL Domain Object Assembler Class
 * 
 * @category PHPFrame
 * @package  Mapper
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_Mapper_SQLDomainObjectAssembler 
    extends PHPFrame_Mapper_DomainObjectAssembler
{
    /**
     * Constructor
     * 
     * @param PHPFrame_Mapper_PersistenceFactory $factory
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Mapper_PersistenceFactory $factory)
    {
        parent::__construct($factory);
    }
    
    /**
     * Find a domain object using an IdObject
     * 
     * @param PHPFrame_Mapper_IdObject $id_obj
     * 
     * @access public
     * @return PHPFrame_Mapper_DomainObject
     * @since  1.0
     */
    public function findOne($id_obj)
    {
        if (is_int($id_obj)) {
            $id = $id_obj;
            
            // Get table name
            $table_name = $this->factory->getTableName();
            
            // Create new IdObject
            $id_obj = $this->factory->getIdObject();
            $id_obj->where("id", "=", ":id")->params(":id", $id);
        }
        
        if (!$id_obj instanceof PHPFrame_Mapper_IdObject) {
            $msg  = "Wrong argument type. ";
            $msg .= get_class($this);
            $msg .= "::findOne() expected only argument to be of type ";
            $msg .= "PHPFrame_Mapper_IdObject or integer.";
            throw new RuntimeException($msg);
        }
        
        $collection = $this->find($id_obj);
        
        return $collection->getElement(0);
    }
    
    /**
     * Find a collection of domain objects using an IdObject
     * 
     * @param PHPFrame_Mapper_IdObject|int $id_obj
     * 
     * @access public
     * @return PHPFrame_Mapper_Collection
     * @since  1.0
     */
    public function find(PHPFrame_Mapper_IdObject $id_obj=null)
    {
        // Create default select statemen if no id object is provided
        if (is_null($id_obj)) {
            $id_obj = $this->factory->getIdObject();
        }
        
        // Get raw data as array from db
        $db  = PHPFrame::DB();
        $raw = $db->fetchAssocList($id_obj->getSQL(), $id_obj->getParams());
        
        // Create collectioj object
        return $this->factory->getCollection($raw);
    }
    
    /**
     * Persist domain object
     * 
     * @param PHPFrame_Mapper_DomainObject $obj
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function insert(PHPFrame_Mapper_DomainObject $obj)
    {
        if ($obj->getId() <= 0) {
            $obj->setCreated(date("Y-m-d H:i:s"));
            $build_query_method = "_buildInsertQuery";
        } else {
            $build_query_method = "_buildUpdateQuery";
        }
        
        $obj->setModified(date("Y-m-d H:i:s"));
        
        $sql    = $this->$build_query_method($obj->toArray());
        $params = $this->_buildQueryParams($obj->toArray());
        PHPFrame::DB()->query($sql, $params);
        
        if ($obj->getId() <= 0) {
            $obj->setId(PHPFrame::DB()->lastInsertId());
        }
        
        $obj->markClean();
    }
    
    /**
     * Delete domain object from the database
     * 
     * @param PHPFrame_Mapper_DomainObject $obj
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function delete(PHPFrame_Mapper_DomainObject $obj)
    {
        $sql    = "DELETE FROM `".$this->factory->getTableName()."`";
        $sql   .= " WHERE id = :id";
        $params = array(":id"=>$obj->getId());
        
        PHPFrame::DB()->query($sql, $params);
    }
    
    /**
     * Build the INSERT SQL query
     * 
     * @param array $array
     * 
     * @access private
     * @return string
     * @since  1.0
     */
    private function _buildInsertQuery(array $array)
    {
        $sql  = "INSERT INTO ".$this->factory->getTableName()." (`";
        $sql .= implode("`, `", array_keys($array));
        $sql .= "`) VALUES (:";
        $sql .= implode(", :", array_keys($array));
        $sql .= ")";
        
        foreach ($array as $key=>$value) {
            $params[":".$key] = $value;
        }
        
        return $sql;
    }
    
    /**
     * Build the UPDATE SQL query
     * 
     * @param array $array
     * 
     * @access private
     * @return string
     * @since  1.0
     */
    private function _buildUpdateQuery(array $array)
    {
        $sql = "UPDATE ".$this->factory->getTableName()." SET ";
        
        $count = 0;
        foreach (array_keys($array) as $key) {
            if ($key == "id") continue;
            if ($count > 0) $sql .= ", ";
            $sql .= "`".$key."` = :".$key;
            $count++;
        }
        
        $sql .= " WHERE id = :id";
        
        return $sql;
    }
    
    /**
     * Build SQL query parameters
     * 
     * @param array $array
     * 
     * @access private
     * @return array
     * @since  1.0
     */
    private function _buildQueryParams(array $array)
    {
        foreach ($array as $key=>$value) {
            $params[":".$key] = $value;
        }
        
        return $params;
    }
}
