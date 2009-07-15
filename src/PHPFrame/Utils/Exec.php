<?php
class PHPFrame_Utils_Exec
{
    private $_cmd=null;
    private $_output=null;
    private $_return_var=null;
    
    public function __construct($cmd)
    {
		$this->_cmd = (string) $cmd;
        
        exec($this->_cmd, $this->_output, $this->_return_var);

		return $this;
    }
    
    public function getCmd()
    {
        return $this->_cmd;
    }
    
    public function getOutput()
    {
        return $this->_output;
    }
    
    public function getReturnVar()
    {
        return $this->_return_var;
    }
}