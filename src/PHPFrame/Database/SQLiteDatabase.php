<?php
/**
 * PHPFrame/Database/SQLiteDatabase.php
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
 * This class is the SQLite implementation of {@link PHPFrame_Database}.
 *
 * @category PHPFrame
 * @package  Database
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_Database
 * @since    1.0
 */
class PHPFrame_SQLiteDatabase extends PHPFrame_Database
{
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
        // Get list of all tables in database
        $sql       = "SELECT tbl_name FROM sqlite_master WHERE type = 'table'";
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

        $i=0;
        foreach ($table->getColumns() as $col) {
            $sql .= ($i>0) ? ",\n" : "\n";
            $sql .= "`".$col->getName()."`";
            //$sql .= " ".$col->getType();

            if ($col->getType() == PHPFrame_DatabaseColumn::TYPE_INT
                && $col->getExtra() == PHPFrame_DatabaseColumn::EXTRA_AUTOINCREMENT
            ) {
                $sql .= " INTEGER PRIMARY KEY ASC";

            } else {
                $sql .= " ".$col->getType();

                if (!$col->getNull()) {
                    $sql .= " NOT NULL";
                }

                if (!is_null($col->getDefault())) {
                    $sql .= " DEFAULT '".$col->getDefault()."'";
                }
            }

            $i++;
        }

        $sql .= "\n)\n";
        //echo $sql; exit;
        // Run SQL query
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
        $sql = "DELETE FROM ".$table_name;

        // Run SQL query
        $this->query($sql);
    }

    /**
     * Get the columns of a given table
     *
     * If table doesn't exists it returns an empty array.
     *
     * @param string $table_name The name of the table for which we want to get
     *                           the columns.
     *
     * @return array
     * @since  1.0
     */
    public function getColumns($table_name)
    {
        // Get list of all tables in database
        $sql     = "SELECT sql FROM sqlite_master ";
        $sql    .= "WHERE type = 'table' AND name = :table_name";
        $params  = array(":table_name" => $table_name);
        $sql     = $this->fetchColumn($sql, $params);

        // If query returns null we return an empty array
        if (!$sql) {
            return array();
        }

        $pattern = '/CREATE\s+TABLE\s+`?'.$table_name.'`?\s+\(\s((.|\s)*)\s\)/i';
        preg_match($pattern, $sql, $matches);

        if (!isset($matches[1])) {
            $msg = "Could not read column information from database";
            throw new RuntimeException($msg);
        }

        $lines = explode(",", $matches[1]);
        foreach ($lines as $line) {
            $line = trim($line);
            $col  = array();

            // Extract column name and type
            preg_match('/`?([\w]+)`?\s+([\w]+)/i', $line, $matches);
            $col["name"]    = $matches[1];
            $col["type"]    = $matches[2];

            $col["default"] = null;
            $col["null"]    = true;
            $col["key"]     = null;
            $col["extra"]   = null;

            preg_match('/DEFAULT\s\'(.*)\'/', $line, $matches);
            if (isset($matches[1])) {
                $col["default"] = $matches[1];
            }

            if (preg_match('/NOT NULL/', $line, $matches)) {
                $col["null"] = false;
            }

            if (preg_match('/PRIMARY/', $line, $matches)) {
                $col["key"]   = PHPFrame_DatabaseColumn::KEY_PRIMARY;
                $col["extra"] = PHPFrame_DatabaseColumn::EXTRA_AUTOINCREMENT;
            }

            $array[] = $col;
        }

        $columns = array();

        foreach ($array as $col) {
            $obj = new PHPFrame_DatabaseColumn(array("name"=>$col["name"]));

            try {
                if ($col["type"] == "INTEGER") {
                    $col["type"] = "int";
                }

                $obj->setType($col["type"]);
            } catch (Exception $e) {
                $obj->setType(PHPFrame_DatabaseColumn::TYPE_TEXT);
            }
            $obj->setDefault($col["default"]);

            if (empty($col["null"])) {
                $col["null"] = null;
            }

            if (!is_null($col["extra"])) {
                $obj->setKey($col["key"]);
                $obj->setExtra($col["extra"]);
            }

            $columns[] = $obj;
        }

        return $columns;
    }
}
