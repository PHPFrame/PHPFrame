<?php
interface AddOnInstaller
{
    public function install($addon);
    
    public function update($addon);
    
    public function remove($addon);
}
