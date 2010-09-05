<?php
/**
 * PHPFrame/Mapper/SQLPersistentObjectAssembler.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Mapper
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * SQL Domain Object Assembler Class
 *
 * @category PHPFrame
 * @package  Mapper
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_SQLPersistentObjectAssembler
    extends PHPFrame_PersistentObjectAssembler
{
    /**
     * Constructor
     *
     * @param PHPFrame_PersistenceFactory $factory Instance of persistence
     *                                             factory to be used with the
     *                                             assembler.
     *
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
     * @param int|PHPFrame_IdObject $id_or_id_obj Either a numeric id or an
     *                                            instance of IdObject.
     *
     * @return PHPFrame_PersistentObject
     * @since  1.0
     */
    public function findOne($id_or_id_obj)
    {
        if (is_int($id_or_id_obj)) {
            $id = $id_or_id_obj;

            // Create new IdObject
            $id_obj = $this->factory->getIdObject();
            $id_obj->where("id", "=", ":id")->params(":id", $id);
        } else {
            $id_obj = $id_or_id_obj;
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
     * @param PHPFrame_IdObject|int $id_obj Instance of {@link PHPFrame_IdObject}.
     *
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

        // Get total number of entries without taking limits into account
        // This is used to build pagination for the collection objects
        $sql_from   = $id_obj->getFromSQL();
        $table_name = $this->factory->getTableName();
        //check if they aliased the table name
        if (strpos($sql_from, ' AS ') !== false) {
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
     * @param PHPFrame_PersistentObject $obj The persistent object we want to
     *                                       store with the mapper.
     *
     * @return void
     * @since  1.0
     */
    public function insert(PHPFrame_PersistentObject $obj)
    {
        if ($obj->id() <= 0) {
            $obj->ctime(time());
            $build_query_method = "buildInsertQuery";
        } else {
            $build_query_method = "buildUpdateQuery";
        }

        $obj->mtime(time());

        $sql    = $this->$build_query_method(iterator_to_array($obj));
        $params = $this->buildQueryParams(iterator_to_array($obj));
        $db     = $this->factory->getDB();

        $stmt = $db->query($sql, $params);
        if ($build_query_method = "buildUpdateQuery"
            && $stmt->rowCount() == 0
        ) {
            $sql = $this->buildInsertQuery(iterator_to_array($obj));
            $stmt = $db->query($sql, $params);
        }

        if ($obj->id() <= 0) {
            $obj->id($db->lastInsertId());
        }
    }

    /**
     * Delete persistent object from the database
     *
     * @param int|PHPFrame_PersistentObject $id_or_obj Either a numeric id or
     *                                                 an instance of the
     *                                                 persistence object.
     *
     * @return void
     * @since  1.0
     */
    public function delete($id_or_obj)
    {
        if ($id_or_obj instanceof PHPFrame_PersistentObject) {
            $id = $id_or_obj->id();
        } else {
            $id = (int) $id_or_obj;
        }

        $sql = "DELETE FROM ";
        if ($this->factory->getDB()->isMySQL()){
            $sql .= "`".$this->factory->getTableName()."`";
        } else {
            $sql .= $this->factory->getTableName();
        }
        $sql .= " WHERE id = :id";
        $params = array(":id"=>$id);

        $this->factory->getDB()->query($sql, $params);
    }

    /**
     * Build the INSERT SQL query
     *
     * @param array $array Associative array containing the object's data.
     *
     * @return string
     * @since  1.0
     */
    public function buildInsertQuery(array $array)
    {
        $sql  = "INSERT INTO ";
        if ($this->factory->getDB()->isMySQL()){
            $sql .= "`".$this->factory->getTableName()."`";
        } else {
            $sql .= $this->factory->getTableName();
        }
        $sql .= " (`".implode("`, `", array_keys($array));
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
     * @param array $array Associative array containing the object's data.
     *
     * @return string
     * @since  1.0
     */
    public function buildUpdateQuery(array $array)
    {
        $sql = "UPDATE ";
        if ($this->factory->getDB()->isMySQL()){
            $sql .= "`".$this->factory->getTableName()."` SET ";
        } else {
            $sql .= $this->factory->getTableName()." SET ";
        }

        $count = 0;
        foreach (array_keys($array) as $key) {
            if ($key == "id") {
                continue;
            }

            if ($count > 0) {
                $sql .= ", ";
            }

            $sql .= "`".$key."` = :".$key;
            $count++;
        }

        $sql .= " WHERE id = :id";

        return $sql;
    }

    /**
     * Build SQL query parameters
     *
     * @param array $array Associative array containing the query parameters.
     *
     * @return array
     * @since  1.0
     */
    public function buildQueryParams(array $array)
    {
        foreach ($array as $key=>$value) {
            $params[":".$key] = $value;
        }

        return $params;
    }
}
