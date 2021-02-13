# EaseAmpMysql
> A very simple and safe PHP library to execute SQL Queries as Prepared Statements on MySQL Database, in an asynchronous & non-blocking way. Methods are provided to prepare a SQL Statement & it's execution separately as different methods (to facilitate caching of prepared statements) as well as together in a single method too, all basing upon Amphp, an event driven concurrent framework in php and its Amphp\Mysql package.

### Why EaseAmpMysql?
Doing SQL Queries on MySQL/MariaDB in a simple and secure way, preventing SQL Injection is always an important thing. on other hand, doing SQL Queries in an asynchronous & non-blocking way improves the responsiveness w.r.t. database queries. This library is based upom Amphp\MySQL Package.

### Advantages
- Uses prepared statements
- MySQL/MariaDB Connection object supported at present
- Named parameters syntax, similar to that of PDO syntax, is supported
- While sanitizing inputs is always a good practice, values that are provided as input to `runPreparedQuery` or `executeQuery` methods serve the purpose by securely executing respective DB queries
- Have required checks to find database connection errors and to reconnect to the database when preparing db queries

### Getting started
With Composer, run

```sh
composer require invincible-tech-systems/easeampmysql:^1.0.5
```

Note that the `vendor` folder and the `vendor/autoload.php` script are generated by Composer; they are not part of PDOLight.

To include the library,

```php
<?php
require 'vendor/autoload.php';

use InvincibleTechSystems\EaseAmpMysql\EaseAmpMysql;
```

As Amphp/dns is among the dependencies of this library, to prevent recursive DNS Server resolution errors that may occur due reasons like open_basedir restrictions/ no access to /etc/resolv.conf file on the linux server etc..., do include the following lines in your code,

```php

use \InvincibleTechSystems\EaseAmpMysql\CustomAmphpDnsConfigLoader;

$customAmphpDnsConfigValues = ["208.67.222.222:53", "208.67.220.220:53","8.8.8.8:53","[2001:4860:4860::8888]:53"];

$CustomAmphpDnsConfigLoader = new CustomAmphpDnsConfigLoader($customAmphpDnsConfigValues, 5000, 3);

\Amp\Dns\resolver(new \Amp\Dns\Rfc1035StubResolver(null, $CustomAmphpDnsConfigLoader));

```

Note: Do skip including the above, if incase similar custom DNS Config Loader is loaded from any of the other Amphp/dns dependent libraries like EaseAmyMysqlRedis (https://github.com/invincible-tech-systems/easeamp-mysql-redis) or EaseAmpRedis (https://github.com/invincible-tech-systems/easeamp-redis) in the application.

In order to connect to the database, you need to initialize the `EaseAmpMysql` class, by passing your database credentials as parameters, in the following order (server hostname, username, password, database name):

```php
$dbHost = "localhost";
$dbUsername = "database_username";
$dbPassword = "database_password_value";
$dbName = "database_name";

$dbConn = new EaseAmpMysql($dbHost, $dbUsername, $dbPassword, $dbName);
```

To execute a SQL query, while preparing and executing the statement in a single method, `executeQuery` method has to be called with SQL Query and corresponding values as associative array and the CRUD Operation Type as third parameter.

Note: Values of CRUD Operation Type include: insertWithIntegerAsPrimaryKey | update | delete | selectSingle | selectMultiple

```php
`INSERT Query (with Named Parameters as placeholders):`

$query = "INSERT INTO `site_members`(`sm_firstname`, `sm_lastname`) VALUES (:sm_firstname,:sm_lastname)";

$values_array = array(':sm_firstname' => 'First Name',':sm_lastname' => 'Last Name');

$queryResult = $dbConn->executeQuery($query, $values_array, "insertWithIntegerAsPrimaryKey");

	
```

```php
`UPDATE Query (with Named Parameters as placeholders):`

$query = "UPDATE `site_members` SET `sm_firstname`=:sm_firstname, `sm_lastname`=:sm_lastname WHERE `sm_memb_id`=:sm_memb_id";

$values_array = array(':sm_firstname' => 'Srirama',':sm_lastname' => 'D',':sm_memb_id' => 2);

$queryResult = $dbConn->executeQuery($query, $values_array, "update");
	
```

```php
`SELECT Query (with Named Parameters as placeholders):`

$query = "SELECT * FROM `site_members` WHERE `sm_memb_id`=:sm_memb_id";

$values_array = array(':sm_memb_id' => 1);

$queryResult = $dbConn->executeQuery($query, $values_array, "selectSingle");

```

```php
`SELECT ALL Query (with Named Parameters as placeholders):`

$query = "SELECT * FROM `site_members`";

$values_array = array();

$queryResult = $dbConn->executeQuery($query, $values_array, "selectMultiple");
	
```

```php
`DELETE Query (with Named Parameters as placeholders):`

$query = "DELETE FROM `site_members` WHERE `sm_memb_id`=:sm_memb_id";

$values_array = array(':sm_memb_id' => 4);

$queryResult = $dbConn->executeQuery($query, $values_array, "delete");
	
```

To execute a SQL query, while preparing and executing the statement in two different methods, `prepareQuery` and `runPreparedQuery` methods has to be called one after another with SQL Query prepared using `prepareQuery` method and prepared statement along with corresponding values (as associative array) and the CRUD Operation Type input as third parameter has to be provided to `runPreparedQuery` to execute the query.

Note: Values of CRUD Operation Type include: insertWithIntegerAsPrimaryKey | update | delete | selectSingle | selectMultiple

```php
`INSERT Query (with Named Parameters as placeholders):`

$query = "INSERT INTO `site_members`(`sm_firstname`, `sm_lastname`) VALUES (:sm_firstname,:sm_lastname)";

$values_array = array(':sm_firstname' => 'First Name',':sm_lastname' => 'Last Name');

$preparedQuery = $dbConn->prepareQuery($query);
$queryResult = $dbConn->runPreparedQuery($preparedQuery, $values_array, "insertWithIntegerAsPrimaryKey");

	
```

```php
`UPDATE Query (with Named Parameters as placeholders):`

$query = "UPDATE `site_members` SET `sm_firstname`=:sm_firstname, `sm_lastname`=:sm_lastname WHERE `sm_memb_id`=:sm_memb_id";

$values_array = array(':sm_firstname' => 'Srirama',':sm_lastname' => 'D',':sm_memb_id' => 2);

$preparedQuery = $dbConn->prepareQuery($query);
$queryResult = $dbConn->runPreparedQuery($preparedQuery, $values_array, "update");

	
```

```php
`SELECT Query (with Named Parameters as placeholders):`

$query = "SELECT * FROM `site_members` WHERE `sm_memb_id`=:sm_memb_id";

$values_array = array(':sm_memb_id' => 1);

$preparedQuery = $dbConn->prepareQuery($query);
$queryResult = $dbConn->runPreparedQuery($preparedQuery, $values_array, "selectSingle");


```

```php
`SELECT ALL Query (with Named Parameters as placeholders):`

$query = "SELECT * FROM `site_members`";

$values_array = array();

$preparedQuery = $dbConn->prepareQuery($query);
$queryResult = $dbConn->runPreparedQuery($preparedQuery, $values_array, "selectMultiple");

	
```

```php
`DELETE Query (with Named Parameters as placeholders):`

$query = "DELETE FROM `site_members` WHERE `sm_memb_id`=:sm_memb_id";

$values_array = array(':sm_memb_id' => 4);

$preparedQuery = $dbConn->prepareQuery($query);
$queryResult = $dbConn->runPreparedQuery($preparedQuery, $values_array, "delete");

	
```

## License
This software is distributed under the [MIT](https://opensource.org/licenses/MIT) license. Please read [LICENSE](https://github.com/easeappphp/PDOLight/blob/main/LICENSE) for information on the software availability and distribution.
