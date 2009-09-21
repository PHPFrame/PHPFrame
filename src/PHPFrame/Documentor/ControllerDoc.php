<?php
class PHPFrame_ControllerDoc extends PHPFrame_ClassDoc
{
    public function __toString()
    {
        $str = "";
        
        $actions = $this->getOwnMethods();
        if (count($actions) > 0) {
            $str .= "Actions:";
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
