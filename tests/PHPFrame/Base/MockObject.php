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

        $return = true;
        $this->enforceReturnType($return);
        return $return;
    }

    /**
     *
     * @return string
     */
    public function bar($str, $enforce_arguments=false, $enforce_return=true)
    {
        if ($enforce_arguments) {
            $this->enforceArgumentTypes();
        }

        if ($enforce_return) {
            $this->enforceReturnType(1);
        }
    }

    public function someMethod()
    {
        $this->enforceReturnType(1);
    }
}
