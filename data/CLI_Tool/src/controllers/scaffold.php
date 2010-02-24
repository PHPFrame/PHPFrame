<?php
/**
 * data/CLITool/src/controllers/scaffold.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   PHPFrame_CLITool
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Scaffold controller.
 * 
 * @category PHPFrame
 * @package  PHPFrame_CLITool
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class ScaffoldController extends PHPFrame_ActionController
{
    /**
     * Constructor.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        parent::__construct("usage");
    }
    
    /**
     * Display controller's usage instructions
     * 
     * @return void
     * @since  1.0
     */
    public function usage()
    {
        $doc = new PHPFrame_ControllerDoc(new ReflectionClass($this));
        
        $this->response()->title("Usage instructions");
        $this->response()->body((string) $doc);
    }
    
    /**
     * Create a database table for a given PersistentObject class.
     *  
     * @param string $path        Path to file with the persistent object class.
     * @param bool   $drop        [Optional] Default value is FALSE. When set 
     *                            to true existing table will be dropped.
     * @param string $install_dir [Optional] Absolute path to the installation 
     *                            directory of the app we are working with.
     * 
     * @return void
     * @since  1.0
     */
    public function create_table($path, $drop=false, $install_dir=null)
    {
        $path = trim((string) $path);
        
        // If path is relative we prepend working directory
        if (!preg_match("/^\//", $path)) {
            $path = getcwd().DS.$path;
        }
        
        if (!is_file($path)) {
            $msg  = "Could not find file '".$path."'.";
            $this->raiseError($msg);
            return;
        }
        
        $class_file = file_get_contents($path);
        
        preg_match("/class\s+(\w+)\s+extends\s+\w+\s+{/", $class_file, $matches);
        
        if (!isset($matches[1])) {
            $msg  = "Could not find any classes that could extend ";
            $msg .= "PHPFrame_PersistentObject.";
            $this->raiseError($msg);
            return;
        }
        
        $reflection_obj = new ReflectionClass($matches[1]);
        if (!$reflection_obj->isSubclassOf("PHPFrame_PersistentObject")) {
            $msg = $match[1]." does not descend from PHPFrame_PersistentObject";
            $this->raiseError($msg);
            return;
        }
        
        $obj = $reflection_obj->newInstance();
        
        // Get database options from config file
        $config_file = $install_dir.DS."etc".DS."phpframe.ini";
        $config      = new PHPFrame_Config($config_file);
        $options     = $config->getSection("db");
        
        if (strtolower($options["driver"]) == "sqlite" 
            && !preg_match("/^\//", $options["name"])
        ) {
            $options["name"] = $install_dir.DS."var".DS.$options["name"];
        }
        
        $db         = PHPFrame_DatabaseFactory::getDB($options);
        $or_toolbox = new PHPFrame_ObjectRelationalToolbox();
        
        $table_name = get_class($obj);
            
        if (isset($options["prefix"]) && !empty($options["prefix"])) {
            $table_name = $options["prefix"].$table_name;
        }
        
        try {
            $or_toolbox->createTable($db, $obj, $table_name, $drop);
            
            $this->notifySuccess("Database table successfully created.");
            
        } catch (Exception $e) {
            $this->raiseError($e->getMessage());
        }
    }
}
