<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_VCardTest extends PHPUnit_Framework_TestCase
{
    private $_vcard;
    
    public function setUp()
    {
        $this->_vcard = new PHPFrame_VCard(array(
            "FAMILY" => "Montero Costa", 
            "GIVEN"  => "Luis"
        ));
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_toString()
    {
        $str = (string) $this->_vcard;
        
        $this->assertType("string", $str);
        $this->assertRegExp("/^BEGIN:VCARD\nVERSION:3\.0\nN:([\w\s\.\,]+)\nFN:([\w\s\.\,]+)\n(.*)END:VCARD$/is", $str);
    }
    
    public function test_setName()
    {
        $this->_vcard->setName("Montero Costa;:\n", "Luis", "Enrique", "Mr.");
        
        $name = $this->_vcard->getName();
        
        $this->assertType("string", $name);
        $this->assertEquals("Mr. Luis Enrique Montero Costa", $name);
    }
    
    public function test_setFormattedName()
    {
        $this->_vcard->setFormattedName("Luperas");
        
        $fn = $this->_vcard->getFormattedName();
        
        $this->assertType("string", $fn);
        $this->assertEquals("Luperas", $fn);
    }
    
    public function test_addEmail()
    {
        $this->_vcard->addEmail("lupomontero@googlemail.com", "HOME");
        $this->_vcard->addEmail("me@lupomontero.com", "HOME");
        $this->_vcard->addEmail("lupo@e-noise.com", "WORK,PREF");
        
        $emails = $this->_vcard->getEmailAddresses();
        $this->assertType("array", $emails);
        $this->assertTrue(count($emails) == 3);
        $this->assertType("array", $emails[0]);
        $this->assertType("array", $emails[1]);
        $this->assertType("array", $emails[2]);
        $this->assertTrue(count($emails[0]) == 2);
        $this->assertTrue(count($emails[1]) == 2);
        $this->assertTrue(count($emails[2]) == 2);
        $this->assertEquals("lupo@e-noise.com", $this->_vcard->getPreferredEmail());
        
        $this->_vcard->removeEmail("lupomontero@googlemail.com");
        
        $emails = $this->_vcard->getEmailAddresses();
        $this->assertType("array", $emails);
        $this->assertTrue(count($emails) == 2);
        $this->assertEquals("lupo@e-noise.com", $this->_vcard->getPreferredEmail());
        
        $this->_vcard->addEmail("me@lupomontero.com", "HOME,PREF");
        
        $emails = $this->_vcard->getEmailAddresses();
        $this->assertType("array", $emails);
        $this->assertTrue(count($emails) == 2);
        $this->assertEquals("me@lupomontero.com", $this->_vcard->getPreferredEmail());
    }
    
    public function test_setPhoto()
    {
        $photo = "http://www.gravatar.com/avatar/a65f99fc6539a579b5af2c5057b98eaf?s=80";
        $this->_vcard->setPhoto($photo);
        
        $this->assertType("string", $this->_vcard->getPhoto());
        $this->assertEquals($photo, $this->_vcard->getPhoto());
    }
}
