<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_PluginTest extends PHPUnit_Framework_TestCase
{
    private $_app, $_plugin;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $data_dir = preg_replace("/tests\/.*/", "data", __FILE__);
        PHPFrame::dataDir($data_dir);

        $install_dir = preg_replace("/tests\/.*/", "data/CLI_Tool", __FILE__);

        $this->_app = new PHPFrame_Application(
            array("install_dir"=>$install_dir)
        );

        $this->_app->request(new PHPFrame_Request());

        $this->_plugin = new MockPlugin($this->_app);
    }

    public function tearDown()
    {
        // Destroy application
        $this->_app->__destruct();
    }

    public function test_interface()
    {
        $reflector = new ReflectionClass("PHPFrame_Plugin");
        $this->assertTrue($reflector->hasMethod("__construct"));
        $this->assertTrue($reflector->hasMethod("routeStartup"));
        $this->assertTrue($reflector->hasMethod("routeShutdown"));
        $this->assertTrue($reflector->hasMethod("preDispatch"));
        $this->assertTrue($reflector->hasMethod("postDispatch"));
        $this->assertTrue($reflector->hasMethod("dispatchLoopStartup"));
        $this->assertTrue($reflector->hasMethod("dispatchLoopShutdown"));
        $this->assertTrue($reflector->hasMethod("preApplyTheme"));
        $this->assertTrue($reflector->hasMethod("postApplyTheme"));
    }

    public function test_Hooks()
    {
        $plugin_info = new PHPFrame_PluginInfo();
        $plugin_info->name("MockPlugin");
        $plugin_info->enabled(true);

        $this->_app->plugins()->insert($plugin_info);
        $request = new PHPFrame_Request();

        $this->assertTrue(count($request->params()) == 0);

        ob_start();
        $this->_app->dispatch($request);
        ob_end_clean();

        $params = $request->params();
        $this->assertTrue(count($params) == 8);
        $this->assertArrayHasKey("routeStartup", $params);
        $this->assertArrayHasKey("routeShutdown", $params);
        $this->assertArrayHasKey("dispatchLoopStartup", $params);
        $this->assertArrayHasKey("preDispatch", $params);
        $this->assertArrayHasKey("postDispatch", $params);
        $this->assertArrayHasKey("dispatchLoopShutdown", $params);
        $this->assertArrayHasKey("preApplyTheme", $params);
        $this->assertArrayHasKey("postApplyTheme", $params);

        $this->_app->plugins()->delete($plugin_info);
    }

    public function test_routeStartup()
    {
        $this->assertNull($this->_app->request()->param("routeStartup"));

        $this->_plugin->routeStartup();

        $this->assertTrue($this->_app->request()->param("routeStartup"));
    }

    public function test_routeShutdown()
    {
        $this->assertNull($this->_app->request()->param("routeShutdown"));

        $this->_plugin->routeShutdown();

        $this->assertTrue($this->_app->request()->param("routeShutdown"));
    }

    public function test_preDispatch()
    {
        $this->assertNull($this->_app->request()->param("preDispatch"));

        $this->_plugin->preDispatch();

        $this->assertTrue($this->_app->request()->param("preDispatch"));
    }

    public function test_postDispatch()
    {
        $this->assertNull($this->_app->request()->param("postDispatch"));

        $this->_plugin->postDispatch();

        $this->assertTrue($this->_app->request()->param("postDispatch"));
    }

    public function test_dispatchLoopStartup()
    {
        $this->assertNull($this->_app->request()->param("dispatchLoopStartup"));

        $this->_plugin->dispatchLoopStartup();

        $this->assertTrue($this->_app->request()->param("dispatchLoopStartup"));
    }

    public function test_dispatchLoopShutdown()
    {
        $this->assertNull($this->_app->request()->param("dispatchLoopShutdown"));

        $this->_plugin->dispatchLoopShutdown();

        $this->assertTrue($this->_app->request()->param("dispatchLoopShutdown"));
    }

    public function test_preApplyTheme()
    {
        $this->assertNull($this->_app->request()->param("preApplyTheme"));

        $this->_plugin->preApplyTheme();

        $this->assertTrue($this->_app->request()->param("preApplyTheme"));
    }

    public function test_postApplyTheme()
    {
        $this->assertNull($this->_app->request()->param("postApplyTheme"));

        $this->_plugin->postApplyTheme();

        $this->assertTrue($this->_app->request()->param("postApplyTheme"));
    }
}

class MockPlugin extends PHPFrame_Plugin
{
    public function routeStartup()
    {
        $this->app()->request()->param("routeStartup", true);

        return parent::routeStartup();
    }

    public function routeShutdown()
    {
        $this->app()->request()->param("routeShutdown", true);

        return parent::routeShutdown();
    }

    public function preDispatch()
    {
        $this->app()->request()->param("preDispatch", true);

        return parent::preDispatch();
    }

    public function postDispatch()
    {
        $this->app()->request()->param("postDispatch", true);

        return parent::postDispatch();
    }

    public function dispatchLoopStartup()
    {
        $this->app()->request()->param("dispatchLoopStartup", true);

        return parent::dispatchLoopStartup();
    }

    public function dispatchLoopShutdown()
    {
        $this->app()->request()->param("dispatchLoopShutdown", true);

        return parent::dispatchLoopShutdown();
    }

    public function preApplyTheme()
    {
        $this->app()->request()->param("preApplyTheme", true);

        return parent::preApplyTheme();
    }

    public function postApplyTheme()
    {
        $this->app()->request()->param("postApplyTheme", true);

        return parent::postApplyTheme();
    }
}
