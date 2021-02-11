<?php 

require '../vendor/autoload.php';

use InvincibleTechSystems\EaseAmpMysql\EaseAmpMysql;
use \InvincibleTechSystems\EaseAmpMysql\CustomAmphpDnsConfigLoader;


$dbHost = "127.0.0.1";
$dbUsername = "db_username";
$dbPassword = "db_password";
$dbName = "db_name";

$customAmphpDnsConfigValues = ["208.67.222.222:53", "208.67.220.220:53","8.8.8.8:53","[2001:4860:4860::8888]:53"];

$CustomAmphpDnsConfigLoader = new CustomAmphpDnsConfigLoader($customAmphpDnsConfigValues, 5000, 3);

\Amp\Dns\resolver(new \Amp\Dns\Rfc1035StubResolver(null, $CustomAmphpDnsConfigLoader));

$dbConn = new EaseAmpMysql($dbHost, $dbUsername, $dbPassword, $dbName);

//Insert Query (insertWithIntegerAsPrimaryKey)
$query = "INSERT INTO `table_name`(`id`, `name`) VALUES (:id,:name)";

$values_array = array();
$values_array = array(':id' => 10,':name' => 'Raghu');

//$preparedQuery = $dbConn->prepareQuery($query);
//$queryResult = $dbConn->runPreparedQuery($preparedQuery, $values_array, "insertWithIntegerAsPrimaryKey");

//$queryResult = $dbConn->executeQuery($query, $values_array, "insertWithIntegerAsPrimaryKey");

echo "===============================================================================================================================================";

//Insert Query (insertWithUUIDAsPrimaryKey)
$query = "INSERT INTO `table_name`(`id`, `name`) VALUES (:id,:name)";

$values_array = array();
$values_array = array(':id' => 'abcd',':name' => 'Raghu');

//$preparedQuery = $dbConn->prepareQuery($query);
//$queryResult = $dbConn->runPreparedQuery($preparedQuery, $values_array, "insertWithUUIDAsPrimaryKey");

//$queryResult = $dbConn->executeQuery($query, $values_array, "insertWithUUIDAsPrimaryKey");

echo "===============================================================================================================================================";

//Update Query
$query = "UPDATE `table_name` SET `name`=:name WHERE `id`=:id";

$values_array = array();
$values_array = array(':name' => 'Raghuveer',':id' => 10);

//$preparedQuery = $dbConn->prepareQuery($query);
//$queryResult = $dbConn->runPreparedQuery($preparedQuery, $values_array, "update");

//$queryResult = $dbConn->executeQuery($query, $values_array, "update");

echo "===============================================================================================================================================";

//Select Query
$query = "SELECT * FROM `table_name` WHERE `id`=:id";

$values_array = array();
$values_array = array(':id' => 100);

//$preparedQuery = $dbConn->prepareQuery($query);
//$queryResult = $dbConn->runPreparedQuery($preparedQuery, $values_array, "selectSingle");

//$queryResult = $dbConn->executeQuery($query, $values_array, "selectSingle");

echo "===============================================================================================================================================";

//Select All Query
$query = "SELECT * FROM `table_name`";

$values_array = array();

//$preparedQuery = $dbConn->prepareQuery($query);
//$queryResult = $dbConn->runPreparedQuery($preparedQuery, $values_array, "selectMultiple");

//$queryResult = $dbConn->executeQuery($query, $values_array, "selectMultiple");

echo "===============================================================================================================================================";

//Delete Query
$query = "DELETE FROM `table_name` WHERE `id`=:id";

$values_array = array();
$values_array = array(':id' => 4);

//$preparedQuery = $dbConn->prepareQuery($query);
//$queryResult = $dbConn->runPreparedQuery($preparedQuery, $values_array, "delete");

//$queryResult = $dbConn->executeQuery($query, $values_array, "delete");

echo "===============================================================================================================================================";


//Describe Query
$query = "DESCRIBE `table_name`";

$values_array = array();

//$preparedQuery = $dbConn->prepareQuery($query);
//$queryResult = $dbConn->runPreparedQuery($preparedQuery, $values_array, "describe");

//$queryResult = $dbConn->executeQuery($query, $values_array, "describe");

echo "===============================================================================================================================================";

echo "<pre>";
print_r($queryResult);


?>