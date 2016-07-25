<?php

namespace EXS\SimpleMongoBundle\Services;

/**
 * Service for peresisting and retriving information from MongoDB in PHP7
 * 
 */
class SimpleMongoService
{
    /**
     * MongoDB server uri
     *
     * @var atring
     */
    protected $connection;
    
    /**
     * Array queue to store all MongoDb actions
     *
     * @var array
     */
    protected $bulk;
    
    /**
     * MongoDb database name to be connected
     *
     * @var string
     */
    protected $dbname;

    /**
     * Initiate the service
     * 
     * @param arary $connection
     */
    public function __construct($connection)
    {
        // set connection
        if(isset($connection['connection'])) {
            $this->connection = $connection['connection'];
        }
        
        // set dbname
        if(isset($connection['dbname'])) {
            $this->dbname = $connection['dbname'];
        }
        
        // set the queue for bulk actions 
        $this->bulk = new \MongoDB\Driver\BulkWrite();
    }
    
    /**
     * Get MongoDb manager
     * 
     * @return \MongoDB\Driver\Manager
     */
    public function getManager()
    {
        try {
            $manager = new \MongoDB\Driver\Manager($this->connection);
        } catch (\Exception $e) {
            throwException($e->getMessage());
        }
        return $manager;
    }    
    
    /**
     * Queue insert action
     * 
     * @param object $data
     * @return boolean
     */
    public function persist($data)
    {
        $mappedData = $this->mapObject($data);
        if(!empty($mappedData)) {
            $this->bulk->insert($mappedData);
            return true;
        }
        return false;
    }
    
    /**
     * Execute the bulk queue
     * 
     * @param string $collection
     * @return object
     */
    public function flush($collection)
    {
        $db = $this->dbname . '.' . $collection;
        $manager = $this->getManager();
        $writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 100);
        try {        
            $result = $manager->executeBulkWrite($db, $this->bulk, $writeConcern);
            $result = $result->getInsertedCount();
        } catch (\MongoDB\Driver\Exception\BulkWriteException $e) {
            $result = $e->getWriteResult();
        } catch (\MongoDB\Driver\Exception\Exception $e) {
            $result = $e->getMessage();
        }
        return $result;        
    }    
    
    /**
     * Map the object to MongoDB executable array
     * 
     * @param object $object
     * @return array
     */
    public function mapObject($object)
    {
        $result = array();
        $methods = get_class_methods($object);
        $result = $this->convertToArray($result, $methods, $object);
        return $result;        
    }
    
    /**
     * Convert the object to array using its getters.
     * 
     * @param array $result
     * @param array $methods
     * @param object $object
     * @return array
     */
    public function convertToArray($result, $methods, $object)
    {
        foreach ($methods as $method) {
            $result = $this->processGetters($result, $method, $object);
        }      
        return $result;
    }
    
    /**
     * Process getter method to get the value of the property
     * 
     * @param array $result
     * @param string $method
     * @param object $object
     * @return array
     */
    public function processGetters($result, $method, $object)
    {
        if (substr($method, 0, 3) == 'get') {
            $propName = strtolower(substr($method, 3, 1)) . substr($method, 4);
            if(strtolower($propName) == 'id') {
                return $result;
            }
            $value = $object->$method();
            $result[$propName] = $this->getPropertyValue($value);
        }        
        return $result;
    }
    
    /**
     * Get the value of the property
     * 
     * @param mixed $value
     * @return mixed
     */
    public function getPropertyValue($value)
    {
        if(is_object($value)) {
            if($value instanceOf \DateTime) {
                return get_object_vars($value);
            } 
            return $this->mapObject($value);                               
        }            
        return $value;        
    }
    
    /**
     * Execute the query to get documents 
     * 
     * @param array $filter
     * @param array $options
     * @param string $collection
     * @return array
     */
    public function exeQuery($filter, $options, $collection)
    {
        $db = $this->dbname . '.' . $collection;
        $manager = $this->getManager();
        $query = new \MongoDB\Driver\Query($filter, $options);
        $results = $manager->executeQuery($db, $query)->toArray();
        return $results;
    }
}
