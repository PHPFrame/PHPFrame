<?php
/**
 * PHPFrame/Mapper/CompositeMapper.php
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
 * This class extends {@link PHPFrameMapper} and provides extended functionality
 * to handle nested objects mapped to other tables (using the main object id
 * the foreign key).
 *
 * This class should be extended to provide more specialised mappers for common
 * persistent objects that require mapping.
 *
 * @category PHPFrame
 * @package  Mapper
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_CompositeMapper extends PHPFrame_Mapper
{
    private $_nested_mappers;

    /**
     * Constructor
     *
     * @param string            $target_class The class the mapper will be
     *                                        working with. This class will
     *                                        have to descend from
     *                                        PHPFrame_PersistentObject.
     * @param PHPFrame_Database $db           Database object.
     * @param string            $table_name   [Optional]
     * @param array             $nested_objs  [Optional]
     *
     * @return void
     * @since  1.0
     */
    public function __construct(
        $target_class,
        PHPFrame_Database $db,
        $table_name=null,
        array $nested_objs=array()
    ) {
        parent::__construct($target_class, $db, $table_name);

        $this->_nested_mappers = array();

        foreach ($nested_objs as $nested_obj) {
            if (count($nested_obj) == 4){
                $this->_nested_mappers[] = array(
                        $nested_obj[0],
                        new PHPFrame_Mapper($nested_obj[1], $db, $nested_obj[2]),
                        $nested_obj[3]
                );
            } else {
                $this->_nested_mappers[] = array(
                        $nested_obj[0],
                        $nested_obj[1],
                        $nested_obj[2],
                        'mapper'
                );
            }
        }
    }

    /**
     * Find a collection of persistent objects using an IdObject
     *
     * @param PHPFrame_IdObject $id_obj [Optional] Instance of
     *                                  {@link PHPFrame_IdObject}.
     *
     * @return PHPFrame_PersistentObjectCollection
     * @since  1.0
     */
    public function find(PHPFrame_IdObject $id_obj=null)
    {
        if (is_null($id_obj)) {
            $id_obj = $this->getIdObject();
            $id_obj->orderby("id", "DESC");
            $id_obj->limit(10, 0);
        }

        $factory = $this->getFactory();
        $db      = $factory->getDB();
        $raw     = $db->fetchAssocList($id_obj->getSQL(), $id_obj->getParams());

        // If no entries we return empty collection
        if (!is_array($raw) || count($raw) < 1) {
            return $factory->getCollection();
        }

        $index = array();
        foreach ($raw as $raw_item) {
            $index[$raw_item["id"]] = $raw_item;
        }

        $id_obj = new PHPFrame_SQLIdObject();
        $id_obj->select("*");

        foreach ($this->_nested_mappers as $nested_mapper) {
            if (count($nested_mapper) == 3) {
                $nested_tbl_name = $nested_mapper[1]->getFactory()->getTableName();
                $nested_target_class = $nested_mapper[1]->getFactory()->getTargetClass();
                $foreign_key = $nested_mapper[2];

                $id_obj->from($nested_tbl_name);
                $id_obj->where($foreign_key, "IN", "(".implode(",", array_keys($index)).")");
                $id_obj->orderby($foreign_key, "DESC");

                $nested_raw = $db->fetchAssocList(
                    $id_obj->getSQL(),
                    $id_obj->getParams()
                );

                foreach ($nested_raw as $nested_raw_item) {
                    $index[$nested_raw_item[$foreign_key]][$nested_mapper[0]][] = $nested_raw_item;
                }
            } else {
                $mapper = $nested_mapper[2];
                $foreign_key = $nested_mapper[1];
                $id_obj->from($mapper->getFactory()->getTableName());
                $id_obj->where($foreign_key, "IN", "(".implode(",", array_keys($index)).")");
                $id_obj->orderby($foreign_key, "DESC");

                $nested_collection = $mapper->find($id_obj);
                foreach ($nested_collection as $nested_item) {
                    $index[$nested_item->$foreign_key()][$nested_mapper[0]][] = $nested_item;
                }
            }
        }

        return $factory->getCollection(array_values($index));
    }

    /**
     * This is variation of find() that uses a single SQL query using LEFT
     * OUTER JOINS, so it is more flexible for search or retrieving sets
     * without pagination.
     *
     * @param PHPFrame_IdObject $id_obj [Optional]
     *
     * @return PHPFrame_PersistentObjectCollection
     * @since  1.0
     */
    public function find2(PHPFrame_IdObject $id_obj=null)
    {
        if (is_null($id_obj)) {
            $id_obj = $this->getIdObject();
        }

        $tbl_name = $this->getFactory()->getTableName();

        $select = $tbl_name.".*";

        foreach ($this->_nested_mappers as $array) {
            $mapper_index = 1;
            if (count($array) == 4)
                $mapper_index = 2;
            $nested_tbl_name     = $array[$mapper_index]->getFactory()->getTableName();
            $nested_target_class = $array[$mapper_index]->getFactory()->getTargetClass();
            $foreign_key = ($mapper_index == 1 ? $array[2] : $array[1]);
            $nested_obj          = new $nested_target_class;

            foreach (array_keys(iterator_to_array($nested_obj)) as $col) {
                $select .= ", `".$nested_tbl_name."`.`".$col."` ";
                $select .= "AS ".$nested_target_class."_".$col;
            }

            $join  = "LEFT OUTER JOIN `".$nested_tbl_name."` ON ";
            $join .= "`".$nested_tbl_name."`.`".$foreign_key."` = ";
            $join .= "`".$tbl_name."`.id";
            $id_obj->join($join);
        }

        $id_obj->select($select);

        $raw = $this->getFactory()->getDB()->fetchAssocList(
            $id_obj->getSQL(),
            $id_obj->getParams()
        );

        $array = array();
        for ($i=0; $i<count($raw); $i++) {
            foreach ($raw[$i] as $key=>$value) {
                $found = false;
                foreach ($this->_nested_mappers as $nested_mapper_array) {
                    $mapper_index = 1;
                    if (count($array) == 4)
                        $mapper_index = 2;
                    $mapper = $nested_mapper_array[$mapper_index];
                    $nested_target_class = $mapper->getFactory()->getTargetClass();
                    if (preg_match("/".$nested_target_class."_(.+)/", $key, $matches)) {
                        $found = $nested_mapper_array[0];
                        $key   = $matches[1];
                        break;
                    }
                }

                if ($found) {
                    if ($i > 0 && $raw[$i]["id"] == $raw[$i-1]["id"]) {
                        if (array_key_exists($found, $array[count($array)-1])) {
                            $prev_id = $array[count($array)-1][$found][count($found)-1]["id"];
                            if ($prev_id == $raw[$i][$nested_target_class."_id"]) {
                                continue;
                            }
                            $array[count($array)-1][$found][count($found)][$key] = $value;
                        } else {
                            $array[count($array)-1][$found][0][$key] = $value;
                        }
                    } else {
                        $tmp_array[$found][0][$key] = $value;
                    }
                } else {
                    $tmp_array[$key] = $value;
                }
            }

            if ($i == 0 || $raw[$i]["id"] != $raw[$i-1]["id"]) {
                $array[] = $tmp_array;
            }
        }

        return $this->getFactory()->getCollection($array);
    }

    /**
     * Find an invoice object using a numeric id.
     *
     * @param int $id Numeric id.
     *
     * @return Invoice|null
     * @since  1.0
     */
    public function findOne($id)
    {
        $tbl_name = $this->getFactory()->getTableName();

        $id_obj = $this->getIdObject();
        $id_obj->where($tbl_name.".id", "=", ":id");
        $id_obj->params(":id", $id);

        $collection = $this->find($id_obj);
        $obj        = $collection->getElement(0);

        if ($obj instanceof PHPFrame_PersistentObject) {
            return $obj;
        } else {
            return null;
        }
    }

    /**
     * Insert/update object in db.
     *
     * @param PHPFrame_PersistentObject $obj The object to store.
     *
     * @return void
     * @since  1.0
     */
    public function insert(PHPFrame_PersistentObject $obj)
    {
        parent::insert($obj, false);

        foreach ($this->_nested_mappers as $array) {
            $getter = $array[0];
            $prop_name_parts  = explode("_", $array[2]);
            $parent_id_method = strtolower($prop_name_parts[0]);
            for ($i=1; $i<count($prop_name_parts); $i++) {
                $parent_id_method .= ucfirst(strtolower($prop_name_parts[$i]));
            }

            foreach ($obj->$getter() as $child) {
                $child->$parent_id_method($obj->id());

                $child->owner($obj->owner());
                $child->group($obj->group());
                $child->perms($obj->perms());

                $array[1]->insert($child);
            }
        }

        $obj->markClean();
    }

    /**
     * Delete obj and all its nested objects from db.
     *
     * @param int|PHPFrame_PersistentObject $id_or_obj Either a numeric obj ID
     *                                                 or an invoice object.
     *
     * @return void
     * @since  1.0
     */
    public function delete($id_or_obj)
    {
        if (!$id_or_obj instanceof PHPFrame_PersistentObject) {
            $id = filter_var($id_or_obj, FILTER_VALIDATE_INT);

            if ($id === false) {
                $msg  = "Invalid argument passed to ".get_class($this);
                $msg .= "::".__FUNCTION__."().";
                throw new InvalidArgumentException($msg);
            }

            $obj = $this->findOne($id);

        } else {
            $obj = $id_or_obj;
        }

        foreach ($this->_nested_mappers as $nested_mapper) {
            $tbl_name = $nested_mapper[1]->getFactory()->getTableName();
            $sql      = "DELETE FROM ".$tbl_name;
            $sql     .= " WHERE ".$nested_mapper[2]." = :parent_id";
            $params   = array(":parent_id"=>$obj->id());
            $this->getFactory()->getDB()->query($sql, $params);
        }

        return parent::delete($obj);
    }
}
