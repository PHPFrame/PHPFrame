<?php
class PHPFrame_SCM_SVN implements PHPFrame_SCM
{
    /**
     * Exec object
     * 
     * @var PHPFrame_Utils_Exec
     */
    private $_exec=null;
    
    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct()
    {
        $this->_exec = new PHPFrame_Utils_Exec();
    }
    
    /**
     * Checkout source from repository
     * 
     * (non-PHPdoc)
     * @see src/PHPFrame/SCM/PHPFrame_SCM#checkout($url, $path, $username, $password)
     */
    public function checkout($url, $path, $username=null, $password=null)
    {
        $cmd = "svn checkout ";
        
        if (!is_null($username)) {
            $cmd .= "--username ".$username." ";
        }
        
        $cmd .= $url." ".$path;
        
        $this->_exec->run($cmd);
        var_dump($this->_exec);
    }
    
    public function update($path)
    {
        $cmd = "cd ".$path." && svn update";
        
        $this->_exec->run($cmd);
        var_dump($this->_exec);
    }
    
    public function switchURL($url, $path)
    {
        
    }
    
    public function export($url, $path)
    {
        
    }
    
    public function commit()
    {
        
    }
}