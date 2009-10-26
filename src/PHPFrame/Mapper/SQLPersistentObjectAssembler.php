<?php
/**
 * PHPFrame/Mapper/SQLPersistentObjectAssembler.php
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
class PHPFrame_SQLPersistentObjectAssembler 
    extends PHPFrame_PersistentObjectAssembler
{
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
        parent::__construct($factory);
    }
    
    /**
     * Find a persistent object using an IdObject
     * 
     * @param PHPFrame_IdObject $id_obj
     * 
     * @access public
     * @return PHPFrame_PersistentObject
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
        
        if (!$id_obj instanceof PHPFrame_IdObject) {
            $msg  = "Wrong argument type. ";
            $msg .= get_class($this);
            $msg .= "::findOne() expected only argument to be of type ";
            $msg .= "PHPFrame_IdObject or integer.";
            throw new RuntimeException($msg);
        }
        
        $collection = $this->find($id_obj);
        
        return $collection->getElement(0);
    }
    
    /**
     * Find a collection of persistent objects using an IdObject
     * 
     * @param PHPFrame_IdObject|int $id_obj
     * 
     * @access public
     * @return PHPFrame_PersistentObjectCollection
     * @since  1.0
     */
    public function find(PHPFrame_IdObject $id_obj=null)
    {
        // Create default select statemen if no id object is provided
        if (is_null($id_obj)) {
            $id_obj = $this->factory->getIdObject();
        }
        
        // Get raw data as array from db
        $db  = $this->factory->getDB();
        $raw = $db->fetchAssocList($id_obj->getSQL(), $id_obj->getParams());
        
        if ($db instanceof PHPFrame_SQLiteDatabase && is_array($raw)) {
        	$sqlite_raw = array();
            foreach ($raw as $array) {
            	foreach ($array as $key=>$value) {
	            	if ($key == "rowid") {
	                    $array["id"] = $value;
	                    unset($array["rowid"]);
	                }
            	}
            	
            	$sqlite_raw[] = $array;
            }
            
            $raw[] = $sqlite_raw;
        }
        
        // Get total number of entries without taking limits into account
        // This is used to build pagination for the collection objects
        $sql_from   = $id_obj->getFromSQL();
        $table_name = $this->factory->getTableName();
        //check if they aliased the table name
        if (strpos($sql_from, ' AS ') !== false){
            $table_name = substr($sql_from, strpos($sql_from, ' AS ')+4);
        }
        $sql  = "SELECT COUNT(".$table_name.".id) ";
        $sql .= "\n".$id_obj->getFromSQL();
        if ($id_obj->getJoinsSQL()) {
            $sql .= "\n".$id_obj->getJoinsSQL();
        }
        if ($id_obj->getWhereSQL()) {
            $sql .= "\n".$id_obj->getWhereSQL();
        }
        if ($id_obj->getGroupBySQL()) {
            $sql .= "\n".$id_obj->getGroupBySQL();
        }
        
        $superset_total = $db->fetchColumn($sql, $id_obj->getParams());

        
        // Create collectioj object
        return $this->factory->getCollection(
            $raw, 
            $superset_total, 
            $id_obj->getLimit(), 
            $id_obj->getLimitstart()
        );
    }
    
    /**
     * Persist persistent object
     * 
     * @param PHPFrame_PersistentObject $obj
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function insert(PHPFrame_PersistentObject $obj)
    {
        $obj->validateAll();
        
        if ($obj->getId() <= 0) {
            $obj->setCTime(time());
            $build_query_method = "_buildInsertQuery";
        } else {
            $build_query_method = "_buildUpdateQuery";
        }
        
        $obj->setMTime(time());
        
        $sql    = $this->$build_query_method(iterator_to_array($obj));
        $params = $this->_buildQueryParams(iterator_to_array($obj));
        $db     = $this->factory->getDB();
        
        $db->query($sql, $params);
        
        if ($obj->getId() <= 0) {
            $obj->setId($db->lastInsertId());
        }
        
        $obj->markClean();
    }
    
    /**
     * Delete persistent object from the database
     * 
     * @param PHPFrame_PersistentObject $obj
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function delete(PHPFrame_PersistentObject $obj)
    {
        $sql    = "DELETE FROM `".$this->factory->getTableName()."`";
        $sql   .= " WHERE id = :id";
        $params = array(":id"=>$obj->getId());
        
        $this->factory->getDB()->query($sql, $params);
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
