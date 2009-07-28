<?php
/**
 * PHPFrame/Application/Widgets.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Widgets Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Application_Widgets
{
    /**
     * Array containing the installed modules
     * 
     * @var array
     */
    private $_array=array();
    
    /**
     * Constructor
     * 
     * @return void
     * @since  1.0
     */
    function __construct() 
    {
        $sql = "SELECT m.*, mo.option AS `option` FROM #__modules AS m ";
        $sql .= " LEFT JOIN #__modules_options mo ON mo.moduleid = m.id ";
        $sql .= " ORDER BY m.ordering ASC";
        
        $this->_array = PHPFrame::DB()->fetchAssocList($sql);
    }
    
    /**
     * Count the number of modules assigned to the given position in the current component option.
     * 
     * @param string $position
     * 
     * @return int
     * @since  1.0
     */
    function countModules($position) 
    {
        $count = 0;
        
        foreach ($this->_array as $widget) {
            if ($widget['position'] == $position 
                && $widget['enabled'] == 1
                && ($widget['option'] == PHPFrame::Request()->getControllerName() || $widget['option'] == "*")
                ) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Display modules
     * 
     * This method displays modules assigned to a named position depending on 
     * whether the are also assigned to the current component.
     * 
     * @param string $position
     * @param string $class_suffix
     * 
     * @return mixed
     * @since  1.0
     */
    function display($position, $class_suffix='') 
    {
        $output = array();
        
        foreach ($this->_array as $widget) {
            if ($widget['position'] == $position 
                && $widget['enabled'] == 1
                && ($widget['option'] == PHPFrame::Request()->getControllerName() || $widget['option'] == "*")
                ) {
                $widget_file_path = PHPFRAME_INSTALL_DIR.DS."src".DS."views".DS."partials".DS.$widget['name'].".php";
                if (file_exists($widget_file_path)) {
                    // Start buffering
                    ob_start();
                    require_once $widget_file_path;
                    // save buffer
                    $output[] = ob_get_contents();
                    // clean output buffer
                    ob_end_clean();
                }
                else {
                    throw new PHPFrame_Exception('Widget file '.$widget_file_path.' not found.');
                }
            }
        }
            
        // prepare html output and filter out empty modules
        $html = '';
        for ($i=0; $i<count($output); $i++) {
            $output[$i] = trim($output[$i]);
            if (!empty($output[$i])) {
                $html .= '<div class="module'.$class_suffix.'">';
                $html .= $output[$i];
                $html .= '</div>';
            }
        }
            
        return $html;
    }
}
