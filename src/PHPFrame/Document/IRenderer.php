<?php
/**
 * PHPFrame/Document/IRenderer.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Document
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Renderer interface
 *
 * @category PHPFrame
 * @package  Document
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
interface PHPFrame_IRenderer
{
    /**
     * Render a given value.
     *
     * @param mixed $value The value we want to render.
     *
     * @return void
     * @since  1.0
     */
    public function render($value);
}
