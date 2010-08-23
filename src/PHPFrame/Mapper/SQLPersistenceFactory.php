<?php
/**
 * PHPFrame/Mapper/SQLPersistenceFactory.php
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
 * SQL Persistence Factory Class
 *
 * @category PHPFrame
 * @package  Mapper
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_SQLPersistenceFactory extends PHPFrame_PersistenceFactory
{
    private $_db;

    /**
     * Constructor
     *
     * @param string            $target_class The target class for this factory.
     * @param string            $table_name   The table name the target class is
     *                                        mapped to.
     * @param PHPFrame_Database $db           Reference to the databse object.
     * @param string            $type_column  [Optional] Name of column storing
     *                                        the subtype if any. When storing
     *                                        subtypes in the same table the
     *                                        subtype class name needs to be
     *                                        stored in a column in order to
     *                                        instantiate the correct objects
     *                                        when retrievin data from storage.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(
        $target_class,
        $table_name,
        PHPFrame_Database $db,
        $type_column=null
    ) {
        parent::__construct($target_class, $table_name, $type_column);

        $this->_db = $db;
    }

    /**
     * Get object assembler
     *
     * @return PHPFrame_PersistentObjectAssembler
     * @since  1.0
     */
    public function getAssembler()
    {
        return new PHPFrame_SQLPersistentObjectAssembler($this);
    }

    /**
     * Create a new IdObject to work with the target class
     *
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function getIdObject()
    {
        $options = array("select"=>"*", "from"=>$this->getTableName());

        //TODO: This line has been added to void using the PHPFrame_MySQLIdObject
        // as it is breaking code in existing PHPFrame based apps.
        return new PHPFrame_SQLIdObject($options);

        if ($this->getDB()->isMySQL()){
            return new PHPFrame_MySQLIdObject($options);
        } else {
            return new PHPFrame_SQLIdObject($options);
        }
    }

    /**
     * Get reference to database object.
     *
     * @return PHPFrame_Database
     * @since  1.0
     */
    public function getDB()
    {
        return $this->_db;
    }
}
