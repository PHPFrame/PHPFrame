<?php
/**
 * PHPFrame/Mapper/MySQLIdObject.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Mapper
 * @author    Chris McDonald <chris.mcdonald@sliderstudio.co.uk>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 * @since     1.0
 */

/**
 * SQL IdObject class
 *
 * This class encapsulates the selection of rows from the database.
 *
 * @category PHPFrame
 * @package  Mapper
 * @author   Chris McDonald <chris.mcdonald@sliderstudio.co.uk>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
 class PHPFrame_MySQLIdObject extends PHPFrame_SQLIdObject
 {

     /**
     * An array with the names of any real tables used for this query, used as
     * a reference for sub part of a query that requires backtick quoting for
     * table names.
     *
     * @var array
     */
    private $_table_names = array();

    /**
     * Constructor
     *
     * @param array $options [Optional] An associative array with initialisation
     *                       options. For a list of available options call
     *                       {@link PHPFrame_IdObject::getOptions()}.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        parent::__construct($options);
    }

    /**
     * Set the table from which to select rows
     *
     * This method supports only one table in the from clause.
     * Please use the join() method to add join tables.
     *
     * Tables may be passed with an alias. Ie: "table_name AS tn".
     * There is no need to quote table names with backticks, these will be
     * added and handled internally.
     * Overrides SQLIdObject.from() to handle recording of real table names used
     * for determining any table references to be backtick quoted.
     *
     * @param string $table A string with the table name.
     *
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function from($table)
    {
        parent::from($table);

        if (is_array($this->_from)){
            $this->_table_names[] = $this->_from[0];
        } else {
            $this->_table_names[] = $this->_from;
        }

        return $this;
    }

    /**
     * Add a join clause to the select statement. There is no need to use
     * backtick quotes for table names, these will be added and handled
     * internally.
     * Overrides SQLIdObject.join() to handle recording of real table names used
     * for determining any table references to be backtick quoted.
     *
     * @param sting $join A join statement
     *
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function join($join)
    {
        parent::join($join);

        $last_join = $this->_join[count($this->_join) - 1];
        if (array_key_exists("table_name", $last_join)){
            $this->_table_names[] = $last_join["table_name"];
        }

        return $this;
    }

    /**
     * Get SELECT SQL, overrides SQLIdObject.getSelectSQL() in order to use
     * backticks for any table names and fields.
     *
     * @return string
     * @since  1.0
     */
    public function getSelectSQL()
    {
        if (count($this->_select) < 1) {
            $msg = "Can not build query. No fields have been selected.";
            throw new LogicException($msg);
        }

        $sql = "SELECT ";

        for ($i=0; $i<count($this->_select); $i++) {
            if ($i>0) {
                $sql .= ", ";
            }

            $table_name  = $this->_select[$i]["table_name"];
            $field_name  = $this->_select[$i]["field_name"];
            $field_alias = $this->_select[$i]["field_alias"];

            if (!empty($table_name)) {
                if (in_array($table_name, $this->_table_names)) {
                    $sql .= "`".$table_name."`.";
                } else {
                    $sql .= $table_name.".";
                }
            }

            if ($field_name == '*' || preg_match("/\(.*\)/", $field_name)){
                $sql .= $field_name;
            } else {
                $sql .= "`".$field_name."`";
            }

            if (!empty($field_alias)) {
                $sql .= " AS ".$field_alias;
            }
        }

        return $sql;
    }

    /**
     * Get FROM SQL, overrides SQLIdObject.getFromSQL() in order to use
     * backticks for any table names.
     *
     * @return string
     * @since  1.0
     */
    public function getFromSQL()
    {
        if (empty($this->_from)) {
            $exception_msg = "Can not build query. No table to select from.";
            throw new LogicException($exception_msg);
        }

        if (is_array($this->_from)) {
            $sql = "FROM `".$this->_from[0]."` AS ".$this->_from[1];
        } else {
            $sql = "FROM `".$this->_from."`";
        }

        return $sql;
    }

    /**
     * Get JOIN SQL, overrides SQLIdObject.getFromSQL() in order to use
     * backticks for any table names and fields.
     *
     * @return string
     * @since  1.0
     */
    public function getJoinsSQL()
    {
        $sql = "";

        $bt_tables = array();
        foreach ($this->_table_names as $table){
            $bt_tables[] = "`$table`";
        }

        foreach ($this->_join as $join) {
            $sql .= " ".$join["type"]." ";
            $join_table = explode(" ", $join["table_name"]);
            if (in_array($join_table[0], $this->_table_names)){
                $sql .= "`".$join_table[0]."` ".$join_table[1];
            } else {
                $sql .= $join["table_name"]." ";
            }
            if (isset($join["table_alias"])) {
                $sql .= $join["table_alias"]." ";
            }
            $sql .= "ON ";
            $on = str_replace($this->_table_names, $bt_tables, $join["on"]);
            $sql .= $on[0]." ".$on[1]." ".$on[2];
        }

        return $sql;
    }

    // /**
    //  * Get GROUP BY SQL
    //  *
    //  * @return string
    //  * @since  1.0
    //  */
    // public function getGroupBySQL()
    // {
    //     $sql = "";
    //
    //     if (!empty($this->_groupby)) {
    //         $sql = "GROUP BY `".$this->_groupby."`";
    //     }
    //
    //     return $sql;
    // }
    //
    // /**
    //  * Get ORDER BY SQL statement
    //  *
    //  * @return string
    //  * @since  1.0
    //  */
    // public function getOrderBySQL()
    // {
    //     $sql = " ORDER BY ";
    //
    //     if (is_string($this->_orderby) && $this->_orderby != "") {
    //         $i=0;
    //         foreach (explode(",", $this->_orderby) as $field) {
    //             if ($i>0) {
    //                 $sql .= ", ";
    //             }
    //
    //             $j=0;
    //             foreach (explode(".", trim($field)) as $part) {
    //                 if ($j>0) {
    //                     $sql .= ".";
    //                 }
    //
    //                 $sql .= "`".$part."`";
    //
    //                 $j++;
    //             }
    //
    //             $i++;
    //         }
    //
    //         $sql .= " ";
    //         $sql .= ($this->_orderdir == "DESC") ? $this->_orderdir : "ASC";
    //     }
    //
    //     return $sql;
    // }
 }