<?php
/**
 * PHPFrame/Mapper/PersistentObjectFactory.php
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
 * Persistent Object Factory Class
 *
 * @category PHPFrame
 * @package  Mapper
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_PersistentObjectFactory
{
    private $_factory;

    /**
     * Constructor
     *
     * @param PHPFrame_PersistenceFactory $factory Instance of persistence
     *                                             factory.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_PersistenceFactory $factory)
    {
        $this->_factory = $factory;
    }

    /**
     * Create persistent object from persisted data.
     *
     * @param array $array Associative array with data to be used to create
     *                     object.
     *
     * @return PHPFrame_PersistentObject
     * @since  1.0
     */
    public function createObject(array $array)
    {
        $type_column = $this->_factory->getTypeColumn();
        if (!is_null($type_column) && array_key_exists($type_column, $array)) {
            $class_name = $array[$type_column];
        } else {
            $class_name = $this->_factory->getTargetClass();
        }

        $reflectionObj = new ReflectionClass($class_name);
        if (!$reflectionObj->isSubclassOf("PHPFrame_PersistentObject")) {
            $msg = "Domain Object '".$class_name."' not supported.";
            throw new RuntimeException($msg);
        }

        $obj = $reflectionObj->newInstanceArgs(array($array));

        $obj->markClean();

        return $obj;
    }
}
