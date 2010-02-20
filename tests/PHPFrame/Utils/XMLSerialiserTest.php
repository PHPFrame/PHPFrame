<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_XMLSerialiserTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //...
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_serialiseSimpleArray()
    {
        $array        = array(1, 2, 3);
        $serialised   = PHPFrame_XMLSerialiser::serialise($array);
        $unserialised = PHPFrame_XMLSerialiser::unserialise($serialised);
        
        $this->assertEquals($array, $unserialised);
    }
    
    public function test_serialiseSimpleAssoc()
    {
        $array        = array("first"=>1,"second"=>2,"third"=>3);
        $serialised   = PHPFrame_XMLSerialiser::serialise($array);
        $unserialised = PHPFrame_XMLSerialiser::unserialise($serialised);
        
        $this->assertEquals($array, $unserialised);
    }
    
    public function test_serialiseNestedArrays()
    {
        $array        = array(array(1,2,3), array(4,5,6));
        $serialised   = PHPFrame_XMLSerialiser::serialise($array);
        $unserialised = PHPFrame_XMLSerialiser::unserialise($serialised);
        
        $this->assertEquals($array, $unserialised);
    }
    
    public function test_serialiseNestedAssoc()
    {
        $array        = array(array("first"=>1,"second"=>2,"third"=>3), "a value");
        $serialised   = PHPFrame_XMLSerialiser::serialise($array);
        $unserialised = PHPFrame_XMLSerialiser::unserialise($serialised);
        
        $this->assertEquals($array, $unserialised);
    }
    
    public function test_serialiseComplexArray()
    {
        $array = array(array(
            "first"=>1,
            "second"=>array(1,2,3), 
            "dependencies"=>array(
                "required"=>null,
                "optional"=>array(
                    array(
                        "name" => "login",
                        "version" => 1.0
                    ),
                    array(
                        "name" => "admin",
                        "version" => 1.0
                    )
                )
            )
        ));
        $serialised   = PHPFrame_XMLSerialiser::serialise($array);
        $unserialised = PHPFrame_XMLSerialiser::unserialise($serialised);
        
        $this->assertEquals($array, $unserialised);
    }
    
    public function test_serialiseUser()
    {
        $user  = new PHPFrame_User();
        $user->setUserName("someone");
        $user->setPassword("password");
        $user->setFirstName("Jimi");
        $user->setLastName("Hendrix");
        $user->setEmail("jimi@hendrix.com");
        $user->validateAll();
        
        $array = iterator_to_array($user);
        
        $serialised   = PHPFrame_XMLSerialiser::serialise($array);
        $unserialised = PHPFrame_XMLSerialiser::unserialise($serialised);
        
        $user2 = new PHPFrame_User($unserialised);
        
        $this->assertEquals(iterator_to_array($user), iterator_to_array($user2));
    }
    
    public function test_serialisePluginInfo()
    {
        $plugin = new PHPFrame_PluginInfo();
        $plugin->setName("Users");
        $plugin->setChannel("dist.phpframe.org");
        $plugin->setSummary("This is the summary");
        $plugin->setDescription("This is the description");
        $plugin->setAuthor("Luis Montero");
        $plugin->setDate("2009-08-27");
        $plugin->setTime("00:47");
        $plugin->setVersion(array("release"=>"0.1.1", "api"=>"1.0"));
        $plugin->setStability(array("release"=>"beta", "api"=>"beta"));
        $plugin->setLicense(array(
            "name" => "BSD Style", 
            "uri"  => "http://www.opensource.org/licenses/bsd-license.php"
        ));
        $plugin->setNotes("This are the notes....");
        $plugin->setDependencies(array(
            "required" => null,
            "optional" => array(
                "feature" => array(
                    array(
                        "name"    => "Login",
                        "channel" => "dist.phpframe.org",
                        "min"     => "0.0.1",
                        "max"     => "0.0.1"
                    ),
                    array(
                        "name"    => "Admin",
                        "channel" => "dist.phpframe.org",
                        "min"     => "0.0.1",
                        "max"     => "0.0.1"
                    ),
                    array(
                        "name"    => "Dashboard",
                        "channel" => "dist.phpframe.org",
                        "min"     => "0.0.1",
                        "max"     => "0.0.1"
                    )
                )
            )
        ));
        $plugin->setContents(array(
            array("path"=>"src/controllers/users.php", "role"=>"php"),
            array("path"=>"src/controllers/users.php", "role"=>"php"),
            array("path"=>"src/models/users.php", "role"=>"php")
        ));
        
        $plugin->validateAll();
        
        $array = iterator_to_array($plugin);
        
        $serialised   = PHPFrame_XMLSerialiser::serialise($array);
        $unserialised = PHPFrame_XMLSerialiser::unserialise($serialised);
        
        $plugin2 = new PHPFrame_PluginInfo($unserialised);
        
        $this->assertEquals(iterator_to_array($plugin), iterator_to_array($plugin2));
    }
}
