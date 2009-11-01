<?php
class PHPFrame_MockObject extends PHPFrame_Object
{
	/**
	 * Foo
	 * 
	 * @param string $str
	 * @param bool   $bool
	 * @param int    $int
	 * 
	 * @return bool
	 */
	public function foo($str, $bool, $int)
	{
		$this->enforceArgumentTypes();
	}
	
	/**
	 * 
	 * @return string
	 */
	public function bar($str)
	{
		$this->enforceReturnType(1);
	}
}
