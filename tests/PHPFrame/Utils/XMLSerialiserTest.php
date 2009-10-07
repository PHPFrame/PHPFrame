<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

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
    
    public function test_serialiseFeatureInfo()
    {
        $ext = new PHPFrame_FeatureInfo();
        $ext->setName("Users");
        $ext->setChannel("dist.phpframe.org");
        $ext->setSummary("This is the summary");
        $ext->setDescription("This is the description");
        $ext->setAuthor("Luis Montero");
        $ext->setDate("2009-08-27");
        $ext->setTime("00:47");
        $ext->setVersion(array("release"=>"0.1.1", "api"=>"1.0"));
        $ext->setStability(array("release"=>"beta", "api"=>"beta"));
        $ext->setLicense(array(
            "name" => "BSD Style", 
            "uri"  => "http://www.opensource.org/licenses/bsd-license.php"
        ));
        $ext->setNotes("This are the notes....");
        $ext->setDependencies(array(
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
        $ext->setContents(array(
            array("path"=>"src/controllers/users.php", "role"=>"php"),
            array("path"=>"src/controllers/users.php", "role"=>"php"),
            array("path"=>"src/models/users.php", "role"=>"php")
        ));
        
        $ext->validateAll();
        
        $array = iterator_to_array($ext);
        
        $serialised   = PHPFrame_XMLSerialiser::serialise($array);
        $unserialised = PHPFrame_XMLSerialiser::unserialise($serialised);
        
        $ext2 = new PHPFrame_FeatureInfo($unserialised);
        
        $this->assertEquals(iterator_to_array($ext), iterator_to_array($ext2));
    }
}
