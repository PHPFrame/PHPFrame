<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ValidatorTest extends PHPUnit_Framework_TestCase
{
    private $_validator;
    
    public function setUp()
    {
        $this->_validator = new PHPFrame_Validator();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_setFilter()
    {
        $filter = new PHPFrame_StringFilter();
        $this->_validator->setFilter("some_field", $filter);
        $filter2 = $this->_validator->getFilter("some_field");
        $this->assertEquals($filter, $filter2);
    }
    
    public function test_setFilterInvalidArgumentExceptionArray()
    {
        $this->setExpectedException("InvalidArgumentException");
        
        $this->_validator->setFilter(array(), new PHPFrame_StringFilter());
    }
    
    public function test_setFilterInvalidArgumentExceptionEmptyString()
    {
        $this->setExpectedException("InvalidArgumentException");
        
        $this->_validator->setFilter("", new PHPFrame_StringFilter());
    }
    
    public function test_getFilters()
    {
        $this->_validator->setFilter("some_field", new PHPFrame_StringFilter());
        $this->_validator->setFilter("another_field", new PHPFrame_IntFilter());
        
        $array = $this->_validator->getFilters();
        
        $this->assertType("array", $array);
        $this->assertTrue(count($array) == 2);
        $this->assertArrayHasKey("some_field", $array);
        $this->assertArrayHasKey("another_field", $array);
        $this->assertType("PHPFrame_StringFilter", $array["some_field"]);
        $this->assertType("PHPFrame_IntFilter", $array["another_field"]);
    }
    
    public function test_allowsNull()
    {
        $this->_validator->setFilter("some_field", new PHPFrame_StringFilter());
        $this->_validator->setFilter("another_field", new PHPFrame_IntFilter(), true);
        
        $this->assertFalse($this->_validator->allowsNull("some_field"));
        $this->assertTrue($this->_validator->allowsNull("another_field"));
    }
    
    public function test_throwExceptions()
    {
        
    }
    
    public function test_setExceptionClass()
    {
        
    }
    
    public function test_validate()
    {
        $this->_validator->setFilter("some_field", new PHPFrame_StringFilter());
        
        // Values that can be filtered as strings
        $values = array(1, 3.14, "some string");
        foreach ($values as $value) {
            $this->assertTrue($this->_validator->validate("some_field", $value));
        }
        
        // Values that can not be filtered as strings
        $values = array(true, false, array(), new stdClass());
        foreach ($values as $value) {
            $this->assertFalse($this->_validator->validate("some_field", $value));
        }
    }
    
    public function test_validateAll()
    {
        $this->_validator->setFilter("some_field", new PHPFrame_StringFilter());
        $this->_validator->setFilter("another_field", new PHPFrame_IntFilter(), true);
        
        $this->assertFalse($this->_validator->validateAll(array(
            "some_field"    => "blah", 
            "another_field" => "hmm"
        )));
        
        $this->assertFalse($this->_validator->validateAll(array(
            "some_field"    => true, 
            "another_field" => 1
        )));
        
        $filtered = $this->_validator->validateAll(array(
            "some_field"    => 123, 
            "another_field" => "12"
        ));
        
        $this->assertType("array", $filtered);
        $this->assertArrayHasKey("some_field", $filtered);
        $this->assertArrayHasKey("another_field", $filtered);
        
        // Check that the filters where applied
        $this->assertType("string", $filtered["some_field"]);
        $this->assertType("int", $filtered["another_field"]);
    }
    
    public function test_getOriginalValues()
    {
        
    }
    
    public function test_getFilteredValues()
    {
        
    }
    
    public function test_getOriginalValue()
    {
        
    }
    
    public function test_getFilteredValue()
    {
        
    }
    
    public function test_getMessages()
    {
        
    }
}
