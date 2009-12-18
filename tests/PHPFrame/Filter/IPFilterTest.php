<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_IPFilterTest extends PHPUnit_Framework_TestCase
{
    private $_filter;
    
    public function setUp()
    {
        $this->_filter = new PHPFrame_IPFilter();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_process()
    {
        $values = array(
            "1.1.1.1", 
            "192.168.0.1", 
            "79.170.43.196", 
            "2001:0db8:85a3:08d3:1319:8a2e:0370:7334"
        );
        
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result === false);
            $this->assertType("string", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processFailure()
    {
        $values = array(true, -3, 3.14, "a string", "11.2", "1.1.1", array(), new stdClass());
        for ($i=0; $i<count($values); $i++) {
            $result   = $this->_filter->process($values[$i]);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == ($i+1)));
        }
    }
    
    public function test_processIpv4()
    {
        $this->_filter->setIpv4(true);
        
        $values = array("1.1.1.1", "192.168.0.1", "79.170.43.196", "239.255.255.255");
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result === false);
            $this->assertType("string", $result);
            $this->assertEquals($value, $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processIpv4Failure()
    {
        $this->_filter->setIpv4(true);
        
        $values = array("2001:0db8:85a3:08d3:1319:8a2e:0370:7334", "", "1.1.x");
        for ($i=0; $i<count($values); $i++) {
            $result   = $this->_filter->process($values[$i]);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == ($i+1)));
        }
    }
    
    public function test_processIpv6()
    {
        $this->_filter->setIpv6(true);
        
        $values = array("2001:0db8:85a3:08d3:1319:8a2e:0370:7334");
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result === false);
            $this->assertType("string", $result);
            $this->assertEquals($value, $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processIpv6Failure()
    {
        $this->_filter->setIpv6(true);
        
        $values = array("1.1.1.1", "192.168.0.1", "79.170.43.196", "", "1.1.x");
        for ($i=0; $i<count($values); $i++) {
            $result   = $this->_filter->process($values[$i]);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == ($i+1)));
        }
    }
    
    public function test_processNoPrivRange()
    {
        $this->_filter->setNoPrivRange(true);
        
        $values = array("79.170.43.196", "2001:0db8:85a3:08d3:1319:8a2e:0370:7334");
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result === false);
            $this->assertType("string", $result);
            $this->assertEquals($value, $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processNoPrivRangeFailure()
    {
        $this->_filter->setNoPrivRange(true);
        
        $values = array("10.0.0.1", "172.26.0.1", "192.168.0.0");
        for ($i=0; $i<count($values); $i++) {
            $result   = $this->_filter->process($values[$i]);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == ($i+1)));
        }
    }
    
    public function test_processNoResRange()
    {
        $this->_filter->setNoResRange(true);
        
        $values = array("79.170.43.196", "2001:0db8:85a3:08d3:1319:8a2e:0370:7334");
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result === false);
            $this->assertType("string", $result);
            $this->assertEquals($value, $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processNoResRangeFailure()
    {
        $this->_filter->setNoResRange(true);
        
        $values = array(
            "0.0.0.0", 
            "0.255.255.255", 
            "169.254.0.0", 
            "192.0.2.0", 
            "224.0.0.0", 
            "239.255.255.255", 
            "240.0.0.0", 
            "255.255.255.255"
        );
        
        for ($i=0; $i<count($values); $i++) {
            $result   = $this->_filter->process($values[$i]);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == ($i+1)));
        }
    }
    
    public function test_processIpv4AndNoResRange()
    {
        $this->_filter->setIpv4(true);
        $this->_filter->setNoResRange(true);
        
        $values = array("79.170.43.196");
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result === false);
            $this->assertType("string", $result);
            $this->assertEquals($value, $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processIpv4AndNoResRangeFailure()
    {
        $this->_filter->setIpv4(true);
        $this->_filter->setNoResRange(true);
        
        $values = array(
            "0.0.0.0", 
            "0.255.255.255", 
            "169.254.0.0", 
            "192.0.2.0", 
            "224.0.0.0", 
            "239.255.255.255", 
            "240.0.0.0", 
            "255.255.255.255",
            "2001:0db8:85a3:08d3:1319:8a2e:0370:7334",
            "0.0",
            "1.1.x",
            ""
        );
        
        for ($i=0; $i<count($values); $i++) {
            $result   = $this->_filter->process($values[$i]);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == ($i+1)));
        }
    }
}
