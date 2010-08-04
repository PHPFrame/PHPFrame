<?php
/**
 * PHPFrame/Database/MySQLDatabase.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Database
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 * @since     1.0
 */

/**
 * This class is the MySQL implementation of {@link PHPFrame_Database}.
 *
 * @category PHPFrame
 * @package  Database
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_Database
 * @since    1.0
 */
class PHPFrame_MySQLDatabase extends PHPFrame_Database
{
    /**
     * Create PDO object to represent db connection. This class override's the
     * parent's connect method in order to set MySQL specific attributes.
     *
     * @return void
     * @since  1.0
     */
    public function connect()
    {
        parent::connect();

        $this->getPDO()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    }

    /**
     * Get the database tables.
     *
     * @param bool $return_names [Optional] Default value is FALSE. If set to
     *                           TRUE an array containing table names will be
     *                           returned instead of an array containing
     *                           objects of type {@link PHPFrame_DatabaseTable}.
     *
     * @return array
     * @since  1.0
     */
    public function getTables($return_names=false)
    {
        $sql       = "SHOW TABLES";
        $tbl_names = $this->fetchColumnList($sql);
        $tbl_objs  = array();

        if ((bool) $return_names && is_array($tbl_names)) {
            return $tbl_names;
        }

        if (is_array($tbl_names) && count($tbl_names) > 0) {
            foreach ($tbl_names as $tbl_name) {
                $tbl_objs[] = new PHPFrame_DatabaseTable($this, $tbl_name);
            }
        }

        return $tbl_objs;
    }

    /**
     * Create database table for a given table object
     *
     * @param PHPFrame_DatabaseTable $table A reference to an object of type
     *                                      PHPFrame_DatabaseTable representing
     *                                      the table we want to create.
     *
     * @return void
     * @since  1.0
     */
    public function createTable(PHPFrame_DatabaseTable $table)
    {
        $sql = "CREATE TABLE `".$table->getName()."` (";

        $i = 0;
        foreach ($table->getColumns() as $col) {
            $sql .= ($i>0) ? ",\n" : "\n";
            $sql .= "`".$col->getName()."`";
            $sql .= " ".$col->getType();

            // Add display width
            switch ($col->getType()) {
            case PHPFrame_DatabaseColumn::TYPE_BOOL :
                $def_value = (int) (bool) $col->getDefault();
                break;
            case PHPFrame_DatabaseColumn::TYPE_INT :
                $sql      .= "(11)";
                $def_value = (int) $col->getDefault();
                break;
            case PHPFrame_DatabaseColumn::TYPE_TEXT :
            	$col->setDefault(null);
            case PHPFrame_DatabaseColumn::TYPE_VARCHAR :
            case PHPFrame_DatabaseColumn::TYPE_CHAR :
                $sql      .= "(".$col->getLength().")";
                $def_value = (string) $col->getDefault();
                break;
            case PHPFrame_DatabaseColumn::TYPE_ENUM :
                $sql      .= "('".implode("', '", $col->getEnums())."')";
                $def_value = (string) $col->getDefault();
                break;
            default :
                $def_value = (string) $col->getDefault();
                break;
            }

            if (!$col->getNull()) {
                $sql .= " NOT NULL";
            }

            if (!is_null($col->getDefault())) {
                $sql .= " DEFAULT '".$def_value."'";
            }

            if (!is_null($col->getExtra())) {
                $sql .= " ".$col->getExtra();
            }

            if (!is_null($col->getKey())) {
                switch ($col->getKey()) {
                case "PRI" :
                    $sql .= " PRIMARY KEY";
                }
            }

            $i++;
        }

        $sql .= "\n) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci\n";
        //echo $sql;
        $this->query($sql);
    }

    /**
     * Alter a database table
     *
     * @param PHPFrame_DatabaseTable $table A reference to an object of type
     *                                      PHPFrame_DatabaseTable representing
     *                                      the table we want to alter.
     *
     * @return void
     * @since  1.0
     * @todo   This method needs to be implemented.
     */
    public function alterTable(PHPFrame_DatabaseTable $table)
    {

    }

    /**
     * Truncate a database table. This method deletes all records from a table
     * and resets the auto increment counter back to zero.
     *
     * @param string $table_name The name of the table we want to truncate.
     *
     * @return void
     * @since  1.0
     */
    public function truncate($table_name)
    {
        $sql = "TRUNCATE TABLE `".$table_name."`";

        // Run SQL query
        $this->query($sql);
    }

    /**
     * Get the columns of a given table.
     *
     * @param string $table_name The name of the table for which we want to get
     *                           the columns.
     *
     * @return array
     * @since  1.0
     */
    public function getColumns($table_name)
    {
        // Store structure in array uning table name as key
        $sql  = "SHOW COLUMNS FROM `".$table_name."`";
        $cols = $this->fetchAssocList($sql);

        if (!is_array($cols) || count($cols) <= 0) {
            return array();
        }

        $col_objs = array();
        foreach ($cols as $col) {
            $col_obj = new PHPFrame_DatabaseColumn(array(
                "name" => $col["Field"]
            ));

            $type = preg_replace("/(.*)\(\d+\)/i", "$1", $col["Type"]);
            $col_obj->setType($type);
            if (
                $type == PHPFrame_DatabaseColumn::TYPE_VARCHAR
                || $type == PHPFrame_DatabaseColumn::TYPE_CHAR
            ) {
                $length = preg_replace("/(.*)\((\d+)\)/i", "$2", $col["Type"]);
                $col_obj->setLength($length);
            }
            $col_obj->setNull(($col["Null"] == "NO") ? false : true);
            $col_obj->setDefault($col["Default"]);
            $col_obj->setKey($col["Key"]);
            $col_obj->setExtra($col["Extra"]);

            $col_objs[] = $col_obj;
        }

        return $col_objs;
    }
}
