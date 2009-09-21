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
     * @param string $model_name The model name
     * @param string $table_name [Optional] Table name to use. If none specified 
     *                           we use the object's class name.
     * @param bool   $drop       [Optional] Default value is FALSE. When set to
     *                           true existing table will be dropped.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function create_table($model_name, $table_name=null, $drop=false)
    {
        $array = explode("/", $model_name);
        
        $class_file  = $this->_install_dir.DS."src".DS."models";
        $class_file .= DS.strtolower(implode(DS, $array)).".php";
        if (!is_file($class_file)) {
            $msg = "File ".$class_file." not found.";
            throw new RuntimeException($msg);
        }
        
        require $class_file;
        
        $class_name = end($array);
        $obj = new $class_name;
        
        $config = PHPFrame_Config::instance($this->_install_dir.DS."etc".DS."phpframe.ini");
        $dsn_class = "PHPFrame_".$config->get("db.driver")."DSN";
        $dsn_options = array(
            "db_host" => $config->get("db.host"), 
            "db_name" => $config->get("db.name")
        );
        
        $dsn        = new $dsn_class($dsn_options);
        $db         = PHPFrame_Database::getInstance(
            $dsn, 
            $config->get("db.user"), 
            $config->get("db.pass")
        );
        $table_name = $config->get("db.prefix").$table_name;
        $or_toolbox = new PHPFrame_ObjectRelationalToolbox();
        $or_toolbox->createTable($db, $obj, $table_name, $drop);
        
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