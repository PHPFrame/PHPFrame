<?php
/**
 * PHPFrame/Base/Collection.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Base
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Abstract Collection
 *
 * @category PHPFrame
 * @package  Base
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      ArrayObject
 * @since    1.0
 */
abstract class PHPFrame_Collection implements Iterator, Countable
{
    /**
     * Get limit of entries per page
     *
     * @return int
     * @since  1.0
     */
    abstract public function getLimit();

    /**
     * Get position at which the current page starts
     *
     * @return int
     * @since  1.0
     */
    abstract public function getLimitstart();

    /**
     * Get total number of entries in superset
     *
     * @return int
     * @since  1.0
     */
    abstract public function getTotal();

    /**
     * Get number of pages
     *
     * @return int
     * @since  1.0
     */
    public function getPages()
    {
        if ($this->getLimit() > 0 && $this->getTotal() > 0) {
            // Calculate number of pages
            return (int) ceil($this->getTotal()/$this->getLimit());
        } else {
            return 0;
        }
    }

    /**
     * Get current page number
     *
     * @return int
     * @since  1.0
     */
    public function getCurrentPage()
    {
        // Calculate current page
        if ($this->getLimit() > 0) {
            return (int) (ceil($this->getLimitstart()/$this->getLimit())+1);
        } else {
            return 0;
        }
    }
}
