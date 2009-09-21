<?php
class ManController extends PHPFrame_ActionController
{
    private $_install_dir=null;
    
    public function __construct($install_dir=null)
    {
        if (is_null($install_dir)) {
            $this->_install_dir = getcwd();
        } else {
            $this->_install_dir = (string) trim($install_dir);
        }
        
        parent::__construct("index");
    }
    
    public function index()
    {
        $app_doc = new PHPFrame_AppDoc($this->_install_dir);
        
        $str  = "To use the command line tool you will need to specify at ";
        $str .= "least a controller,\nand normally also an action and a number";
        $str .= " of parameters. For example, to get\na configuration parameter";
        $str .= " we would use the 'get' action in the 'config'\ncontroller. ";
        $str .= "The get action takes a parameter named 'key'.\n\n";
        $str .= "phpframe config get key=db.enable\n\n";
        $str .= "The above command will show the value of db.enable as defined ";
        $str .= "in the config\nfile.\n\n";
        $str .= (string) $app_doc;
        
        PHPFrame::Response()->getDocument()->setTitle("Usage instructions");
        PHPFrame::Response()->getDocument()->setBody($str);
    }
}