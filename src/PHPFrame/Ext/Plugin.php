<?php
abstract class PHPFrame_Plugin
{
    public function routeStartup() {}
    public function routeShutdown() {}
    public function dispatchLoopStartup() {}
    public function dispatchLoopShutdown() {}
    public function preDispatch() {}
    public function postDispatch() {}
}
