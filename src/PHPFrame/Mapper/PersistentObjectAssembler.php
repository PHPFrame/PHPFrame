<?php
/**
 * PHPFrame/Mapper/PersistentObjectAssembler.php
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
 * Persistent Object Assembler Class
 *
 * This is an abstract class that will need to be extended by all i
 *
 * @category PHPFrame
 * @package  Mapper
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
abstract class PHPFrame_PersistentObjectAssembler
{
    /**
     * Reference to the factory object to use
     *
     * @var PHPFrame_PersistenceFactory
     */
    protected $factory=null;

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
        $this->factory = $factory;
    }

    /**
     * Find a persistent object using an IdObject or numeric id
     *
     * @param int|PHPFrame_IdObject $id_or_id_obj Either a numeric id or an
     *                                            instance of IdObject.
     *
     * @return PHPFrame_PersistentObject
     * @since  1.0
     */
    abstract public function findOne($id_or_id_obj);

    /**
     * Find a collection of persistent objects using an IdObject
     *
     * @param PHPFrame_IdObject $id_obj Instance of {@link PHPFrame_IdObject}.
     *
     * @return PHPFrame_PersistentObjectCollection
     * @since  1.0
     */
    abstract public function find(PHPFrame_IdObject $id_obj=null);

    /**
     * Persist persistent object
     *
     * @param PHPFrame_PersistentObject $obj The persistent object we want to
     *                                       store with the mapper.
     *
     * @return void
     * @since  1.0
     */
    abstract public function insert(PHPFrame_PersistentObject $obj);

    /**
     * Delete persistent object
     *
     * @param int|PHPFrame_PersistentObject $id_or_obj Either a numeric id or
     *                                                 an instance of the
     *                                                 persistence object.
     *
     * @return void
     * @since  1.0
     */
    abstract public function delete($id_or_obj);
}
