<?php
/**
 * PHPFrame/Mapper/PersistentObjectCollection.php
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
 * Persistent Object Collection Class
 *
 * @category PHPFrame
 * @package  Mapper
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_PersistentObjectCollection extends PHPFrame_Collection
{
    /**
     * A domain factory object used to create objects in collection
     *
     * @var PHPFrame_PersistentObjectFactory
     */
    private $_obj_fact;
    /**
     * Raw array used to generate persistent objects
     *
     * @var array
     */
    private $_raw;
    /**
     * Limit of entries per page
     *
     * @var int
     */
    private $_limit;
    /**
     * Position at which the current page starts
     *
     * @var int
     */
    private $_limitstart;
    /**
     * The total number of elements in the collection (this will normally be a
     * subset determined by pagination parameters)
     *
     * @var int
     */
    private $_total_subset;
    /**
     * The total number of elements in the storage media
     *
     * @var int
     */
    private $_total_superset;
    /**
     * Internal array pointer
     *
     * @var int
     */
    private $_pointer = 0;
    /**
     * Storage array used to manage the collection's objects
     *
     * @var array;
     */
    private $_objects = array();

    /**
     * Constructor
     *
     * @param array                            $raw         [Optional] Array
     *                                                      containig the raw
     *                                                      collection data.
     * @param PHPFrame_PersistentObjectFactory $obj_factory [Optional] Instance
     *                                                      of persistence object
     *                                                      factory.
     * @param int                              $total       [Optional] The total
     *                                                      number of records in
     *                                                      the superset.
     * @param int                              $limit       [Optional] The number
     *                                                      of records the current
     *                                                      subset is lmited to.
     *                                                      Default value is '-1',
     *                                                      which means there is
     *                                                      no limit, so we will
     *                                                      get all the records.
     * @param int                              $limitstart  [Optional] The entry
     *                                                      number from which to
     *                                                      start the subset.
     *                                                      If ommited default
     *                                                      value '0' will be
     *                                                      used, meaning that
     *                                                      we start from the
     *                                                      first page of results.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(
        array $raw=null,
        PHPFrame_PersistentObjectFactory $obj_factory=null,
        $total=null,
        $limit=-1,
        $limitstart=0
    ) {
        if (!is_null($raw) && !is_null($obj_factory)) {
            // If the raw array is only one level of depth we assume it is
            // only one element and we wrap it in an array to make is a
            // collection of a single entry
            $array_obj = new PHPFrame_Array($raw);

            if ($array_obj->depth() == 1) {
                $raw = array($raw);
            }

            $this->_raw          = $raw;
            $this->_total_subset = count($raw);
        }

        $this->_obj_fact   = $obj_factory;
        $this->_limit      = (int) $limit;
        $this->_limitstart = (int) $limitstart;

        if (!is_null($total)) {
            $this->_total_superset = (int) $total;
        } else {
            $this->_total_superset = $this->_total_subset;
        }
    }

    /**
     * Get persistent object at given key
     *
     * @param string $key The key of the element to get.
     *
     * @return PHPFrame_PersistentObject
     * @since  1.0
     */
    public function getElement($key)
    {
        if ($key >= $this->count() || $key < 0) {
            return null;
        }

        if (isset($this->_objects[$key])) {
            return $this->_objects[$key];
        }

        if (isset($this->_raw[$key])) {
            $this->_objects[$key] = $this->_obj_fact->createObject(
                $this->_raw[$key]
            );

            return $this->_objects[$key];
        }
    }

    /**
     * Add persistent object to the collection
     *
     * @param PHPFrame_PersistentObject $obj An instance of a persistent object
     *                                       to add to the collection.
     *
     * @return void
     * @since  1.0
     */
    public function addElement(PHPFrame_PersistentObject $obj)
    {
        if (in_array($obj, $this->_objects)) {
            return;
        }

        $this->_objects[$this->_total_subset++] = $obj;
    }

    /**
     * Remove persistent object from the collection
     *
     * @param PHPFrame_PersistentObject $obj An instance of a persistent object
     *                                       to remove from the collection.
     *
     * @return void
     * @since  1.0
     */
    public function removeElement(PHPFrame_PersistentObject $obj)
    {
        if (in_array($obj, $this->_objects)) {
            $keys = array_keys($this->_objects, $obj);
            unset($this->_objects[$keys[0]]);
        }

        $updated_raw = array();
        foreach ($this->_raw as $raw_item) {
            if (isset($raw_item["id"]) && $raw_item["id"] == $obj->id()) {
                continue;
            }

            $updated_raw[] = $raw_item;
        }

        $this->_raw = $updated_raw;
        $this->_total_subset--;
        $this->_total_superset--;
    }

    /**
     * Get limit
     *
     * @return int
     * @see PHPFrame/Base/PHPFrame_Collection#getLimit()
     * @since  1.0
     */
    public function getLimit()
    {
        return $this->_limit;
    }

    /**
     * Get limitstart.
     *
     * @return int
     * @see PHPFrame/Base/PHPFrame_Collection#getLimitstart()
     * @since  1.0
     */
    public function getLimitstart()
    {
        return $this->_limitstart;
    }

    /**
     * Get total records in superset.
     *
     * @return int
     * @see PHPFrame/Base/PHPFrame_Collection#getTotal()
     * @since  1.0
     */
    public function getTotal()
    {
        return $this->_total_superset;
    }

    /**
     * Implementation of Iterator::current()
     *
     * @return PHPFrame_PersistentObject
     * @since  1.0
     */
    public function current()
    {
        return $this->getElement($this->key());
    }

    /**
     * Implementation of Iterator::next()
     *
     * @return void
     * @since  1.0
     */
    public function next()
    {
        $this->_pointer++;
    }

    /**
     * Implementation of Iterator::key()
     *
     * @return int
     * @since  1.0
     */
    public function key()
    {
        return $this->_pointer;
    }

    /**
     * Implementation of Iterator::valid()
     *
     * @return bool
     * @since  1.0
     */
    public function valid()
    {
        return ($this->key() < $this->count());
    }

    /**
     * Implementation of Iterator::rewind()
     *
     * @return void
     * @since  1.0
     */
    public function rewind()
    {
        $this->_pointer = 0;
    }

    /**
     * Implementation of the Countable interface. It returns the number of
     * objects in the current subset.
     *
     * @return int
     * @since  1.0
     */
    public function count()
    {
        return $this->_total_subset;
    }
}
