<?php
/**
 * PHPFrame/Mapper/XMLPersistenceFactory.php
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
 * XML Persistence Factory Class
 *
 * @category PHPFrame
 * @package  Mapper
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_XMLPersistenceFactory extends PHPFrame_PersistenceFactory
{
    private $_path = null;

    /**
     * Constructor
     *
     * @param string $target_class The persistent object class this factory will
     *                             work with.
     * @param string $table_name   [Optional] The table name where we will be
     *                             mapping the persistent objects. If omitted
     *                             the table name will be assumed to be the same
     *                             as the target class.
     * @param string $path         Path to directory where to store the XML file.
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
        $table_name,
        $path,
        $type_column=null
    ) {
        parent::__construct($target_class, $table_name, $type_column);

        if (!is_null($path)) {
            $this->_path = trim((string) $path);
        }
    }

    /**
     * Get object assembler
     *
     * @return PHPFrame_PersistentObjectAssembler
     * @since  1.0
     */
    public function getAssembler()
    {
        return new PHPFrame_XMLPersistentObjectAssembler($this, $this->_path);
    }

    /**
     * Create a new IdObject to work with the target class. THIS METHOD WILL
     * ALWAYS THROW AN EXCEPTION BECAUSE XML DOESN'T SUPPORT ID OBJECTS.
     *
     * @return void
     * @throws LogicException
     * @since  1.0
     */
    public function getIdObject()
    {
        throw new LogicException("XML doesn't support IdObject!");
    }
}
