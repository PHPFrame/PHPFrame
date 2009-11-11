<?php
/**
 * PHPFrame/Document/PlainRenderer.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Document
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Plain text renderer class
 * 
 * @category PHPFrame
 * @package  Document
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      PHPFrame_IRenderer
 * @since    1.0
 */
class PHPFrame_PlainRenderer implements PHPFrame_IRenderer
{
    public function render($value)
    {
        if (
            is_null($value)
            || (is_string($value) && empty($value))
            || (is_array($value) && count($value) == 0
            || ($value instanceof Countable) && count($value) == 0)
        ) {
            return;
        } elseif ($value instanceof PHPFrame_View) {
            $value = $this->renderView($value);
        }
        
        return strip_tags(trim((string) $value));
    }
    
    /**
     * Render view and store in document's body
     * 
     * This method is invoked by the views and renders the ouput data in the
     * document specific format.
     * 
     * @param PHPFrame_View $view The view object to process.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function renderView(PHPFrame_View $view)
    {
        $str = "";
        
        foreach ($view->getData() as $key=>$value) {
            $str .= $key.": ";
            $str .= (string) $value;
            $str .= "\n\n";
        }
        
        return $str;
    }
}
