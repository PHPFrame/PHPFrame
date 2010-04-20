<?php
/**
 * PHPFrame/Base/Observer.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Base
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 * @since     1.0
 */

/**
 * This class provides an abstract implementation of the SplObserver interface.
 *
 * This class is designed to work together with the PHPFrame_Subject class.
 *
 * @category PHPFrame
 * @package  Base
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_Subject
 * @since    1.0
 */
abstract class PHPFrame_Observer extends PHPFrame_Object implements SplObserver
{
    /**
     * Update
     *
     * @param PHPFrame_Subject $subject Instance of subject notifying the update
     *
     * @return void
     * @since  1.0
     */
    public function update(SplSubject $subject)
    {
        $this->doUpdate($subject);
    }

    /**
     * Template method
     *
     * @param PHPFrame_Subject $subject Instance of subject notifying the update
     *
     * @abstract
     * @return void
     * @since  1.0
     */
    abstract protected function doUpdate(PHPFrame_Subject $subject);
}
