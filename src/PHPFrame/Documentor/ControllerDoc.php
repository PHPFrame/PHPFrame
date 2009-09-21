<?php
class PHPFrame_ControllerDoc extends PHPFrame_ClassDoc
{
    public function __toString()
    {
        $str = $this->getClassName()."\n";
        for ($i=0; $i<strlen($this->getClassName()); $i++) {
            $str .= "-";
        }
        $str .= "\n";
        
        $actions = $this->getOwnMethods();
        if (count($actions) > 0) {
            $str .= "\nActions:\n";
            $str .= "--------\n";
            foreach ($actions as $action) {
                if ($action->getName() == "__construct") {
                    continue;
                }
                
                $str .= "\n".$action->getName();
                $str .= "(";
                $count = 0;
                foreach ($action->getParams() as $param) {
                    if ($count > 0) {
                        $str .= ", ";
                    }
                    if ($param->getType()) {
                        $str .= $param->getType()." ";
                    }
                    $str .= "$".$param->getName();
                    
                    $count++;
                }
                $str .= ")";
            }
        }
        
        return $str;
    }
}
