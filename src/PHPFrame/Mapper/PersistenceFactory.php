<?php
/**
 * PHPFrame/Mapper/PersistenceFactory.php
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
 * Abstract Persistence Factory Class
 *
 * @category PHPFrame
 * @package  Mapper
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
abstract class PHPFrame_PersistenceFactory
{
    /**
     * Target class
     *
     * @var string
     */
    private $_target_class;
    /**
     * Target class
     *
     * @var string
     */
    private $_table_name;
    /**
     * Type column
     *
     * @var string
     */
    private $_type_column;

    /**
     * Constructor
     *
     * @param string $target_class The persistent object class this factory will
     *                             work with.
     * @param string $table_name   [Optional] The table name where we will be
     *                             mapping the persistent objects. If omitted
     *                             the table name will be assumed to be the same
     *                             as the target class.
     * @param string $type_column  [Optional] Name of column storing the subtype
     *                             if any. When storing subtypes in the same
     *                             table the subtype class name needs to be
     *                             stored in a column in order to instantiate
     *                             the correct objects when retrievin data from
     *                             storage.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(
        $target_class,
        $table_name=null,
        $type_column=null
    ) {
        $this->_target_class = trim((string) $target_class);

        if (!is_null($table_name)) {
            $this->_table_name = trim((string) $table_name);
        } else {
            $this->_table_name = $this->_target_class;
        }

        if (!is_null($type_column)) {
            $this->_type_column = trim((string) $type_column);
        }
    }

    /**
     * Get PersistentObjectFactory
     *
     * @return PHPFrame_PersistentObjectFactory
     * @since  1.0
     */
    public function getPersistentObjectFactory()
    {
        return new PHPFrame_PersistentObjectFactory($this);
    }

    /**
     * Get Collection
     *
     * @param array $raw        [Optional] Array containig the raw collection
     *                          data.
     * @param int   $total      [Optional] The total number of records in the
     *                          superset.
     * @param int   $limit      [Optional] The number of records the current
     *                          subset is lmited to. Default value is '-1',
     *                          which means there is no limit, so we will get
     *                          all the records.
     * @param int   $limitstart [Optional] The entry number from which to start
     *                          the subset. If ommited default value '0' will
     *                          be used, meaning that we start from the first
     *                          page of results.
     *
     * @return PHPFrame_PersistentObjectCollection
     * @since  1.0
     */
    public function getCollection(
        array $raw=null,
        $total=null,
        $limit=-1,
        $limitstart=0
    ) {
        return new PHPFrame_PersistentObjectCollection(
            $raw,
            $this->getPersistentObjectFactory(),
            $total,
            $limit,
            $limitstart
        );
    }

    /**
     * Get target class
     *
     * @return string
     * @since  1.0
     */
    public function getTargetClass()
    {
        return $this->_target_class;
    }

    /**
     * Get table name
     *
     * @return string
     * @since  1.0
     */
    public function getTableName()
    {
        return $this->_table_name;
    }

    /**
     * Get type column
     *
     * @return string|null
     * @since  1.0
     */
    public function getTypeColumn()
    {
        return $this->_type_column;
    }

    /**
     * Get object assembler
     *
     * @return PHPFrame_PersistentObjectAssembler
     * @since  1.0
     */
    abstract public function getAssembler();

    /**
     * Create a new IdObject to work with the target class
     *
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    abstract public function getIdObject();
}
