<?php
/**
 * PHPFrame/Document/PlainRenderer.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Document
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
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
        return (string) $value;
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
            
            if ($value instanceof PHPFrame_Collection) {
                $str .= $this->renderCollection($value);
            } else {
                $str .= (string) $value;
            }
            
            $str .= "\n\n";
        }
        
        $this->body = $str;
    }
}
