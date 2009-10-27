<?php
class ScaffoldController extends PHPFrame_ActionController
{
    private $_install_dir=null;
    
    /**
     * Constructor
     * 
     * @param string $install_dir
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($install_dir=null)
    {
        if (is_null($install_dir)) {
            $this->_install_dir = getcwd();
        } else {
            $this->_install_dir = trim((string) $install_dir);
        }
        
        parent::__construct("usage");
    }
    
    /**
     * Display controller's usage instructions
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function usage()
    {
        $doc = new PHPFrame_ControllerDoc(new ReflectionClass(get_class($this)));
        
        PHPFrame::Response()->getDocument()->setTitle("Usage instructions");
        PHPFrame::Response()->getDocument()->setBody((string) $doc);
    }
    
    /**
     * Create a database table for a given PersistentObject class.
     *  
     * @param string $path        [Optional] Path to file or directory  
     *                            containing the persistent objects. If  
     *                            ommitted the default 'src/models' directory 
     *                            will be scanned.
     * @param array  $table_names [Optional] Associative array with the table 
     *                            names to use. Keys should be the class names. 
     *                            If none specified we use the object's class 
     *                            name.
     * @param bool   $drop        [Optional] Default value is FALSE. When set 
     *                            to true existing table will be dropped.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function create_table($path="", $table_names=array(), $drop=false)
    {
    	$path = $this->_install_dir.DS."src".DS."models".DS.$path;
    	
    	if (is_dir($path)) {
    	    $dir_it    = new RecursiveDirectoryIterator($path);
    	    $flat_it   = new RecursiveIteratorIterator($dir_it);
    	    $filter_it = new RegexIterator($flat_it, '/\.php$/');
    	    
    	    foreach ($filter_it as $file) {
    	        require_once $file->getRealPath();
    	    }
    	} elseif (is_file($path)) {
    	   require_once $path;
    	} else {
    		$msg  = "Could not find any PHP files to search for persistent ";
    		$msg .= "objects.";
    	    throw new UnexpectedValueException($msg);
    	}
    	
    	$declared_classes = get_declared_classes();
    	
    	// We get the key of the current class in the get_declared_classes() 
        // array in order to detect new declared classes after including the
        // php files
        $class_key = array_keys($declared_classes, get_class($this));
        $class_key = $class_key[0];
    	
        $objs = array();
    	for ($i=($class_key+1); $i<count($declared_classes); $i++) {
    	    $reflection_obj = new ReflectionClass($declared_classes[$i]);
    	    if ($reflection_obj->isSubclassOf("PHPFrame_PersistentObject")) {
    	    	$objs[] = $reflection_obj->newInstance();
    	    }
    	}
    	
    	// Get database options from config file
    	$config_file = $this->_install_dir.DS."etc".DS."phpframe.ini";
    	$config      = PHPFrame_Config::instance($config_file);
    	$options_it  = new RegexIterator(
            new IteratorIterator(PHPFrame::Config()), 
            '/^db\./', 
            RegexIterator::MATCH, 
            RegexIterator::USE_KEY
        );
        $options = iterator_to_array($options_it);
        
        // Make db_name absolute path if sqlite and relative path given
        if (
            isset($options["db.driver"]) 
            && isset($options["db.name"]) 
            && strtolower($options["db.driver"]) == "sqlite"
            && !preg_match('/^\//', $options["db.name"])
        ) {
        	$var_dir = $this->_install_dir.DS."var";
        	$options["db.name"] = $var_dir.DS.$options["db.name"];
        }
        
    	$db = PHPFrame::DB($options);
    	
        $or_toolbox = new PHPFrame_ObjectRelationalToolbox();
        
        foreach ($objs as $obj) {
        	if (array_key_exists(get_class($obj), $table_names)) {
        	    $table_name = $table_names[get_class($obj)];
        	} else {
        	    $table_name = get_class($obj);
        	}
        	
        	if (isset($options["db.prefix"]) && !empty($options["db.prefix"])) {
        	    $table_name = $options["db.prefix"].$table_name;
        	}
        	
            $or_toolbox->createTable($db, $obj, $table_name, $drop);
        }
        
        $this->notifySuccess("Database table successfully created.");
    }
    
    public function create_persistent_object()
    {
        
    }
    
    public function create_feature()
    {
        
    }
    
    public function create_controller()
    {
        
    }
    
    public function create_view()
    {
        
    }
}