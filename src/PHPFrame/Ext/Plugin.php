<?php
abstract class PHPFrame_Plugin
{
    public function routeStartup() {}
    public function routeShutdown() {}
    public function preDispatch() {}
    public function postDispatch() {}
    public function dispatchLoopStartup() {}
    public function dispatchLoopShutdown() {}
}
