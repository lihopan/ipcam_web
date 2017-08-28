<?php
require 'vendor/autoload.php'; // include Composer goodies

class db {
    var $db_host = 'localhost';
    var $db_name = 'ipcam';   
    var $manager;

    function connect() {
        $this->manager = new MongoDB\Driver\Manager("mongodb://".$this->db_host.":27017");
    }
    
    function query($filter,$options,$collection) {
        $query = new MongoDB\Driver\Query($filter, $options);
        $cursor =  $this->manager->executeQuery($this->db_name.'.'.$collection, $query);
        return $cursor;
    }
    
    function aggregate($pipeline,$collection) {
        $command = new MongoDB\Driver\Command([
            'aggregate' => $collection,
            'pipeline' => $pipeline,
            'cursor' => new stdClass(),            
        ]);
        $cursor = $this->manager->executeCommand($this->db_name,$command);
        return $cursor;
    }
        
    function count($filter,$collection) {
        $cmd = new \MongoDB\Driver\Command( [ 'count' => $collection, 'query' => $filter ] );
        $cursor = $this->manager->executeCommand( $this->db_name, $cmd );                     
        return $cursor->toArray()[0]->n;        
    }
        
    //insert bulk wirete to MongoDB
    function insert($data_array,$collection) {        
        $bulk = new MongoDB\Driver\BulkWrite();     //Create a Bulk object to store data 
        
        //Store data array to Bulk
        foreach ($data_array as $data) {  
            $bulk->insert($data);
        }     
        
        //Create a MongoDB write concern 
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
        
        //Execute the Bulk write
        $result = $this->manager->executeBulkWrite($this->db_name.'.'.$collection, $bulk);
        
        //Check the execute result              
        return $result;        
    }
    
    function update($id_array, $data_array, $collection) {
        $bulk = new MongoDB\Driver\BulkWrite();     //Create a Bulk object to store data 
        
        //Store data array to Bulk
        $bulk->update($id_array,$data_array);  
        
        //Create a MongoDB write concern 
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
        
        //Execute the Bulk write
        $result = $this->manager->executeBulkWrite($this->db_name.'.'.$collection, $bulk);
        
        //Check the execute result              
        return $result;          
    }
    
    function delete($id_array,$collection) {
        $bulk = new MongoDB\Driver\BulkWrite();     //Create a Bulk object to store data 
        
        //Store data array to Bulk
        $bulk->delete($id_array);
        
        //Create a MongoDB write concern 
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
        
        //Execute the Bulk write
        $result = $this->manager->executeBulkWrite($this->db_name.'.'.$collection, $bulk);
        
        //Check the execute result              
        return $result;          
    }
        
}