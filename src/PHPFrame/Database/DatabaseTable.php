<?php
/**
 * PHPFrame/Database/DatabaseTable.php
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
 * This class is designed to represent a database table and works together with
 * the {@link PHPFrame_Database} and {@link PHPFrame_DatabaseColumn} classes.
 *
 * @category PHPFrame
 * @package  Database
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_Database, PHPFrame_DatabaseColumn
 * @since    1.0
 */
class PHPFrame_DatabaseTable
{
    /**
     * Reference to database object the table belongs to.
     *
     * @var PHPFrame_Database
     */
    private $_db;
    /**
     * The table name.
     *
     * @var string
     */
    private $_name;
    /**
     * An object of type SplObjectStorage containing the column objects.
     *
     * @var SplObjectStorage
     */
    private $_columns;

    /**
     * Constructor.
     *
     * @param PHPFrame_Database $db   Reference to database object the table
     *                                belongs to.
     * @param string            $name The database name.
     *
     * @return void
     * @since  1.0
     */
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

    /**
     * Get the table name.
     *
     * @return string
     * @since  1.0
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get table columns. This method returns an array of
     * {@link PHPFrame_DatabaseColumn} objects. If no columns are found it will
     * return an empty array.
     *
     * @return array
     * @since  1.0
     */
    public function getColumns()
    {
        $cols = iterator_to_array($this->_columns);

        if (!is_array($cols) || count($cols) <= 0) {
            return array();
        }

        return $cols;
    }

    /**
     * Get all rows in table as an array of associative arrays. Each
     * associative array represents a single row.
     *
     * @return array
     * @since  1.0
     */
    public function getRows()
    {
        $sql = "SELECT * FROM `".$this->getName()."`";

        return $this->_db->fetchAssocList($sql);
    }

    /**
     * Add column to the table. If a column with the same name already exists
     * it will be replaced with the one passed to this method.
     *
     * @param PHPFrame_DatabaseColumn $column The column object to add to the
     *                                        table.
     *
     * @return void
     * @since  1.0
     */
    public function addColumn(PHPFrame_DatabaseColumn $column)
    {
        foreach ($this->getColumns() as $col) {
            if ($col->getName() == $column->getName()) {
                $this->removeColumn($col);
            }
        }

        $this->_columns->attach($column);
    }

    /**
     * Remove column from the table.
     *
     * @param PHPFrame_DatabaseColumn $column The column object to remove from
     *                                        the table.
     *
     * @return void
     * @since  1.0
     */
    public function removeColumn(PHPFrame_DatabaseColumn $column)
    {
        $this->_columns->detach($column);
    }
}
