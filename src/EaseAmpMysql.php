<?php

declare(strict_types=1);

namespace InvincibleTechSystems\EaseAmpMysql;

use \Amp\Mysql;

/*
* Name: EaseAmpMysql
*
* Author: Krishnaveni Nimmala
*
* Company: Invincible Tech Systems
*
* Version: 1.0.1
*
* Description: A very simple and safe PHP library to execute SQL Queries as Prepared Statements on MySQL Database, in an asynchronous & non-blocking way. Methods 
* are provided to prepare a SQL Statement & it's execution separately as different methods (to facilitate caching of prepared statements) as well as together in a
* single method too, all basing upon Amphp, an event driven concurrent framework in php and its Amphp\Mysql package.
*
* License: MIT
*
* @copyright 2020 Invincible Tech Systems
*/
class EaseAmpMysql {
	
	private $dbHost;
	private $dbUsername;
	private $dbPassword;
	private $dbName;
	private $dbConnection;
	private $result;
	private $lastInsertId;
	private $dbResultRows;
	

	public function __construct($dbHost, $dbUsername, $dbPassword, $dbName) {
		// Assign the parameters values to the object properties
		$this->dbHost = $dbHost;
		$this->dbUsername = $dbUsername;
		$this->dbPassword = $dbPassword;
		$this->dbName = $dbName;
		
		$this->connectDb();
		
    }
	
	public function connectDb() {
		
		try {
		
			\Amp\Loop::run(function() {
			$config = \Amp\Mysql\ConnectionConfig::fromString(
				"host=$this->dbHost user=$this->dbUsername password=$this->dbPassword db=$this->dbName"
			);
			$this->pool = \Amp\Mysql\pool($config);
			});
			
		} catch (\Exception $e) {
			
			throw new \Exception($e->getMessage(), (int)$e->getCode());
		}
	}
	
	public function prepareQuery($query) {
		
		try {
			\Amp\Loop::run(function () use ($query){
				$this->statement = yield $this->pool->prepare($query);
			});
			
			return $this->statement;
			
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), (int)$e->getCode());
		}	
	}
	
	public function runPreparedQuery($preparedStmt, array $valueArray, $crudOperationType) {
		
		try {
			
			/*$valuesArray = array();
			
			if ($this->isAssoc($valueArray) === true) {
				
				
			
				foreach($valueArray as $key => $val) {
					
					if(substr($key,0,1)== ':'){
				  
						$valuesArray[substr($key,1)] = $val;
				 
					} else {
					  
						$valuesArray[$key] = $val;
					}
				 
				}
				
			} else {
				
				$valuesArray = $valueArray;
				
			}*/
			
			$valuesArray = array();
			
			foreach($valueArray as $key => $val) {
				
			    if(substr($key,0,1)== ':'){
			  
					$valuesArray[substr($key,1)] = $val;
			 
			    } else {
				  
					$valuesArray[$key] = $val;
			    }
			 
			}
			
			if ($crudOperationType == "insertWithIntegerAsPrimaryKey") {
			
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					$this->lastInsertId = $this->result->getLastInsertId();
					
				});
				if($this->lastInsertId) {
					
					return $this->lastInsertId;	
				
				} else {
					
					return "";
					
				}
				
			} else if ($crudOperationType == "insertWithUUIDAsPrimaryKey") {
			
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					$this->lastInsertId = $this->result->getLastInsertId();//NEED to GET STRING Typecasted Last Inserted ID here
					
				});
				if($this->lastInsertId) {
					
					return $this->lastInsertId;	
				
				} else {
					
					return "";
					
				}
				
			} else if ($crudOperationType == "update") {
				
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					$this->AffectedRowCount = $this->result->getAffectedRowCount();
					
				});
				if($this->AffectedRowCount) {
					
					return true;	
				
				} else {
					
					return false;
					
				}
				
				
			} else if ($crudOperationType == "delete") {
				
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					$this->AffectedRowCount = $this->result->getAffectedRowCount();
				});
				
				if($this->AffectedRowCount) {
					
					return true;	
				
				} else {
					
					return false;
					
				}
				
			} else if ($crudOperationType == "selectSingle") {
				
				
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					
					while (yield $this->result->advance()) {
						
						$this->dbResultRows = $this->result->getCurrent();
					}
				});
				
				if(($this->dbResultRows != "") && (!is_null($this->dbResultRows)) && (is_array($this->dbResultRows)) && (count($this->dbResultRows) > 0)) {
					
					return $this->dbResultRows;

				} else {
					
					return [];
					
				}
				
			} else if ($crudOperationType == "selectMultiple") {
				
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					
					while (yield $this->result->advance()) {
						
						$this->dbResultRows[] = $this->result->getCurrent();
					}
				});
				
				if(($this->dbResultRows != "") && (!is_null($this->dbResultRows)) && (is_array($this->dbResultRows)) && (count($this->dbResultRows) > 0)) {
					
					return $this->dbResultRows;

				} else {
					
					return [];
					
				}
				
			}
			
		} catch (\Exception $e) {
			throw new \Exception('Invalid CRUD Operation Type input.');
		}
		
		//$this->pool->close();
		
	}
	
	public function executeQuery($query, array $valueArray, $crudOperationType) {
		
		
		try {
			
			$preparedStmt = $this->prepareQuery($query);
			
			/*$valuesArray = array();
			
			if ($this->isAssoc($valueArray) === true) {
				
				
			
				foreach($valueArray as $key => $val) {
					
					if(substr($key,0,1)== ':'){
				  
						$valuesArray[substr($key,1)] = $val;
				 
					} else {
					  
						$valuesArray[$key] = $val;
					}
				 
				}
				
			} else {
				
				$valuesArray = $valueArray;
				
			}*/
			$valuesArray = array();
			
			foreach($valueArray as $key => $val) {
				
			    if(substr($key,0,1)== ':'){
			  
					$valuesArray[substr($key,1)] = $val;
			 
			    } else {
				  
					$valuesArray[$key] = $val;
			    }
			 
			}
		
			if ($crudOperationType == "insertWithIntegerAsPrimaryKey") {
			
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					$this->lastInsertId = $this->result->getLastInsertId();
					
				});
				if($this->lastInsertId) {
					
					return $this->lastInsertId;	
				
				} else {
					
					return "";
					
				}
				
			} else if ($crudOperationType == "insertWithUUIDAsPrimaryKey") {
			
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					$this->lastInsertId = $this->result->getLastInsertId();//NEED to GET STRING Typecasted Last Inserted ID here
					
				});
				if($this->lastInsertId) {
					
					return $this->lastInsertId;	
				
				} else {
					
					return "";
					
				}
				
			} else if ($crudOperationType == "update") {
				
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					$this->AffectedRowCount = $this->result->getAffectedRowCount();
					
				});
				if($this->AffectedRowCount) {
					
					return true;	
				
				} else {
					
					return false;
					
				}
				
				
			} else if ($crudOperationType == "delete") {
				
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					$this->AffectedRowCount = $this->result->getAffectedRowCount();
				});
				
				if($this->AffectedRowCount) {
					
					return true;	
				
				} else {
					
					return false;
					
				}
				
			} else if ($crudOperationType == "selectSingle") {
				
				
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					
					while (yield $this->result->advance()) {
						
						$this->dbResultRows = $this->result->getCurrent();
					}
				});
				echo "\ndbresultrows var dump:\n";
				var_dump($this->dbResultRows);
				/* if(count($this->dbResultRows) > 0) {
					
					return $this->dbResultRows;

				} else {
					
					return [];
					
				} */
				if(($this->dbResultRows != "") && (!is_null($this->dbResultRows)) && (is_array($this->dbResultRows)) && (count($this->dbResultRows) > 0)) {
					
					return $this->dbResultRows;

				} else {
					
					return [];
					
				}
				
			} else if ($crudOperationType == "selectMultiple") {
				
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					
					while (yield $this->result->advance()) {
						
						$this->dbResultRows[] = $this->result->getCurrent();
					}
					
				});
				
				if(($this->dbResultRows != "") && (!is_null($this->dbResultRows)) && (is_array($this->dbResultRows)) && (count($this->dbResultRows) > 0)) {
					
					return $this->dbResultRows;

				} else {
					
					return [];
					
				}
				
			}
			
		} catch (\Exception $e) {
			throw new \Exception('Invalid CRUD Operation Type input.');
		}
		
	}
	
	public function isAssoc(array $arr)	{
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
	
	
	//$this->pool->close();
}
?>