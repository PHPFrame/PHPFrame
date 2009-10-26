<?php
class PHPFrame_DatabaseTable
{
	private $_db, $_name, $_columns;
	
	public function __construct(PHPFrame_Database $db,  $name)
	{
		$this->_db      = $db;
		$this->_name    = $name;
		$this->_columns = new SplObjectStorage();
		
		foreach ($db->getColumns($this->getName()) as $col) {
		    $this->addColumn($col);
		}
	}
	
	public function getName()
	{
		return $this->_name;
	}
	
    public function getColumns()
    {
    	return $this->_columns;
    }
    
    public function getRows()
    {
        $sql = "SELECT * FROM ".$this->getName();
        
        return $this->_db->fetchAssocList($sql);
    }
    
    public function addColumn(PHPFrame_DatabaseColumn $column)
    {
        $this->_columns->attach($column);
    }
    
    public function removeColumn(PHPFrame_DatabaseColumn $column)
    {
        $this->_columns->detach($column);
    }
}
