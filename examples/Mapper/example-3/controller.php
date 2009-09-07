<?php
class CustomersController extends PHPFrame_ActionController
{
    public function __construct()
    {
        parent::__construct("index");
    }

    public function index()
    {
        // Create new customer object
        $customer = $this->getModel("Customer", array(array(
            "first_name"=>"Homer", 
            "last_name"=>"Simpson", 
            "email"=>"homer@simpson.com"
        )));
        
        // Get specialised mapper
        $customer_mapper = $this->getModel("CustomerMapper");
        
        // Save customer object in storage
        $customer_mapper->insert($customer);
        
        // Unset $customer object and get it back from storage to prove it works
        unset($customer);
        
        $customer_collection = $customer_mapper->find();
        
        // Get the "list" view and assign the customers collection to it
        $view = $this->getView("customers/list");
        $view->addData("customers", $customer_collection);
        $view->display();
    }
}