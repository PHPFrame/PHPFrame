<?php
include_once "PHPFrame.php";

class Customer extends PHPFrame_DomainObject
{
    protected $first_name, $last_name, $email;
    
    public function getFirstName()
    {
        return $this->first_name;
    }
    
    public function getLastName()
    {
        return $this->last_name;
    }
    
    public function getEmail()
    {
        return $this->email;
    }
    
    public function setFirstName($value)
    {
        $this->first_name = $value;
    }
    
    public function setLastName($value)
    {
        $this->last_name = $value;
    }
    
    public function setEmail($value)
    {
        $this->email = PHPFrame_Filter::validateEmail($value);
    }
}

class CustomerMapper extends PHPFrame_Mapper
{
    public function __construct()
    {
        parent::__construct("Customer", "#__customers");
    }
}

$customer = new Customer(array(
    "first_name"=>"Homer", 
    "last_name"=>"Simpson", 
    "email"=>"homer@simpson.com"
));

// Show customer object before insert
print_r($customer);

// Get specialised mapper
$customer_mapper = new CustomerMapper();

// Save customer object in storage
$customer_mapper->insert($customer);

// Unset $customer object and get it back from storage to prove it works
unset($customer);

foreach ($customer_mapper->find() as $customer) {
    print_r($customer);
}
