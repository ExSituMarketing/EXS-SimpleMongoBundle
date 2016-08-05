# EXS-SimpleMongoBundle
A simple bundle to persist and execute queries on MongDB database for php7  


## Installing the # EXS-SimpleMongoBundle in a new Symfony2 project

Edit composer.json file with # EXS-SimpleMongoBundle dependency:
``` js
//composer.json
//...
"require": {
    //other bundles
    "exs/simple-mongo-bundle": "dev-master"
},
```
Save the file and have composer update the project via the command line:
``` shell
composer update exs/simple-mongo-bundle
```

Update the app/AppKernel.php
``` php
//app/AppKernel.php
//...
public function registerBundles()
{
    $bundles = array(
    //Other bundles
    new EXS\SimpleMongoBundle\EXSSimpleMongoBundle(),
);
```

## Usage

Add your mongodb connection and dbname in the parameter file
``` php
    simple_mongo:
        connection: mongodb://localhost:27017
        dbname: YOUR_DB_NAME
```

Create the new collection(Optional)
``` shell
app/console exs:create:collection COLLECTION_NAME(Requires) OPTIONS
// Options
app/console exs:create:collection -h

```

In your controller or service
``` php
// Insert data to mongodb
$entity = new YourEntity();
$entity->setPropertyValue(THE_VALUE);
.
.

$manager = $this->get('exs_simple_mongo.service'); // get the service
$manager->persist($entity);   
$result = $manager->flush(COLLECTION_NAME); // the result will store the number of inserted entries or error message
if(!is_int($result) || $result == 0) {
    throwException($result);
}

// Get data with query
$filter = ['product' => 6];
$option = ['projection' => ['_id' => 0]];

$manager = $this->get('exs_simple_mongo.service'); // get the service
$result = $manager->exeQuery($filter, $option, COLLECTION_NAME);
// $result will contain results in an array
```



#### Contributing ####
Anyone and everyone is welcome to contribute.

If you have any questions or suggestions please [let us know][1].


[1]: http://www.ex-situ.com/
