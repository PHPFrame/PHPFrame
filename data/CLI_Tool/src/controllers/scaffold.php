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
    private $_tmpl_path;

    /**
     * Constructor.
     *
     * @param PHPFrame_Application $app Reference to application object.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        parent::__construct($app, "table");
    }

    /**
     * Create a database table for a given PersistentObject class.
     *
     * @param string $path        Path to file with the persistent object class.
     * @param bool   $drop        [Optional] Default value is FALSE. When set
     *                            to true existing table will be dropped.
     * @param string $install_dir [Optional] Absolute path to the installation
     *                            directory of the app we are working with.
     * @param bool	 $lcase		  [Optional] Default value is TRUE. Whether 
     * 							  table name should be converted to lower case. 
     * 							  If FALSE model class name upper case letters 
     * 							  will be preserved.
     * @return void
     * @since  1.0
     */
    public function table($path, $drop=false, $install_dir=null, $lcase=true)
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
        
        //initialise possible paths from models directory
        $model_paths = array();
        $init_p = substr($path, 0, 
            strpos($path, DS.'models')+7);
        $this->_initModelPaths($init_p, $model_paths);
        //store model paths in tmpl_path to be used by autoloader
        $this->_tmpl_path = $model_paths;
        //register custom autoload function to load external model classes
        spl_autoload_register(array($this, 'autoload'));

        if (is_null($install_dir)) {
            $install_dir = getcwd();
        }

        $class_file = file_get_contents($path);

        $ptrn = "/class\s+(\w+)\s+extends\s+\w+(?:\s+implements\s+\w+?)?\s*{/";
        preg_match($ptrn, $class_file, $matches);

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

        if (!$options["enable"]) {
            $msg  = "Database is not enabled in ini file. Please edit the ";
            $msg .= "database section in etc/phpframe.ini to enable a database.";
            $this->raiseError($msg);
            return;
        }

        $db         = PHPFrame_DatabaseFactory::getDB($options);
        $or_toolbox = new PHPFrame_ObjectRelationalToolbox();

        $table_name = get_class($obj);
        if ($lcase) {
            $table_name = strtolower($table_name); 
        }

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

    /**
     * Create an empty persistent object class.
     *
     * @param string $name        The class name.
     * @param string $install_dir [Optional] Absolute path to installation
     *                            directory. If not specified the current
     *                            working directory will be used.
     *
     * @return void
     * @since  1.0
     */
    public function persistent($name, $install_dir=null)
    {
        $this->_createClass(
            "PersistentObject",
            array("MyPersistentObject"=>$name),
            "src".DS."models",
            $name.".php",
            $install_dir
        );
    }

    /**
     * Create a mapper class for a given persistent object class.
     *
     * @param string $class       The mapper name. The string 'Mapper' will
     *                            be appended to the name.
     * @param string $install_dir [Optional] Absolute path to installation
     *                            directory. If not specified the current
     *                            working directory will be used.
     *
     * @return void
     * @since  1.0
     */
    public function mapper($class, $install_dir=null)
    {
        $msg = get_class($this)."::".__FUNCTION__."() not implemented";
        throw new LogicException($msg);
    }

    /**
     * Create an empty controller class.
     *
     * @param string $name        The controller name. The string 'Controller'
     *                            will be appended to the name.
     * @param string $install_dir [Optional] Absolute path to installation
     *                            directory. If not specified the current
     *                            working directory will be used.
     *
     * @return void
     * @since  1.0
     */
    public function controller($name, $install_dir=null)
    {
        $this->_createClass(
            "ActionController",
            array("MyAction"=>$name),
            "src".DS."controllers",
            strtolower($name).".php",
            $install_dir
        );
    }

    /**
     * Create an empty view helper class.
     *
     * @param string $name        The helper name. The string 'Helper' will be
     *                            appended to the name.
     * @param string $install_dir [Optional] Absolute path to installation
     *                            directory. If not specified the current
     *                            working directory will be used.
     *
     * @return void
     * @since  1.0
     */
    public function helper($name, $install_dir=null)
    {
        $this->_createClass(
            "ViewHelper",
            array("MyView"=>$name),
            "src".DS."helpers",
            strtolower($name).".php",
            $install_dir
        );
    }

    /**
     * Create an empty plugin class.
     *
     * @param string $name        The plugin name.
     * @param string $install_dir [Optional] Absolute path to installation
     *                            directory. If not specified the current
     *                            working directory will be used.
     *
     * @return void
     * @since  1.0
     */
    public function plugin($name, $install_dir=null)
    {
        $this->_createClass(
            "Plugin",
            array("MyPlugin"=>$name),
            "src".DS."plugins",
            strtolower($name).".php",
            $install_dir
        );
    }

    /**
     * Private method to create the actual classes and write them to disk.
     *
     * @param string $tmpl        The template to use.
     * @param array  $replace     Key value pairs with patterns and replacementes.
     * @param string $dir         Absolute path to the directory where we will
     *                            be writing the class file.
     * @param string $filename    The name of the class file to write.
     * @param string $install_dir [Optional] Absolute path to installation
     *                            directory. If not specified the current
     *                            working directory will be used.
     *
     * @return void
     * @since  1.0
     */
    private function _createClass(
        $tmpl,
        array $replace,
        $dir,
        $filename,
        $install_dir=null
    ) {
        if (is_null($install_dir)) {
            $install_dir = getcwd();
        }

        $file = $install_dir.DS.$dir.DS.$filename;

        if (is_file($file)) {
            $msg = "Could not create class. File '".$file."' already exists.";
            $this->raiseError($msg);
            return;
        }

        $tmpl_path = $this->app()->getInstallDir().DS."data".DS."class-templates";
        $creator   = new ClassCreator($tmpl_path);
        $class     = $creator->create($tmpl, $replace);

        try {
            PHPFrame_Filesystem::ensureWritableDir($install_dir.DS.$dir);
        } catch (Exception $e) {
            $this->raiseError($e->getMessage());
            return;
        }

        file_put_contents($file, $class);

        $this->notifySuccess("Class file created: ".$file);
    }
    
    function autoload($class_name)
    {
        if (is_array($this->_tmpl_path)){
            foreach ($this->_tmpl_path as $path){
                $class_file = $path.DS.$class_name.'.php';
                if (is_file($class_file)){
                    include $class_file;
                    break;
                }
            }
        }
    }
    
    private function _initModelPaths($path, array &$model_paths)
    {
        if (is_dir($path)){
            $model_paths[] = $path;
            if ($dh = opendir($path)){
                while (($file = readdir($dh)) !== false){
                    if ($file == '.' || $file == '..')
                        continue;
                    $sub = $DS.$file;
                    if (is_dir($sub)){
                        $this->_initModelPaths($sub, $model_paths);
                    }
                }
                closedir($dh);
            }
        }
    }
}
