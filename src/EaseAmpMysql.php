<?php

declare(strict_types=1);

namespace InvincibleTechSystems\EaseAmpMysql;

use \Amp\Mysql;

use \InvincibleTechSystems\EaseAmpMysql\Exceptions\EaseAmpMysqlException;

/*
* Name: EaseAmpMysql
*
* Author: Krishnaveni Nimmala
*
* Company: Invincible Tech Systems
*
* Version: 1.0.6
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
	private $getAffectedRowCount;
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
			
		} catch (EaseAmpMysqlException $e) {
			
			echo "\n EaseAmpMysqlException - ", $e->getMessage(), (int)$e->getCode();
			
		}
	}
	
	public function prepareQuery($query) {
		
		try {
			\Amp\Loop::run(function () use ($query){
				$this->statement = yield $this->pool->prepare($query);
			});
			
			return $this->statement;
			
		} catch (EaseAmpMysqlException $e) {
			
			echo "\n EaseAmpMysqlException - ", $e->getMessage(), (int)$e->getCode();
			
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
					
					$this->result = new \stdClass();
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					$this->lastInsertId = $this->result->getLastInsertId();
					
				});
				if($this->lastInsertId) {
					
					return $this->lastInsertId;	
				
				} else {
					
					return "";
					
				}
				
			} else if ($crudOperationType == "insertWithUUIDAsPrimaryKey") {
			
				/* \Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					$this->lastInsertId = $this->result->getLastInsertId();//NEED to GET STRING Typecasted Last Inserted ID here
					
				});
				if($this->lastInsertId) {
					
					return $this->lastInsertId;	
				
				} else {
					
					return "";
					
				} */ 
				
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = new \stdClass();
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					$this->AffectedRowCount = $this->result->getAffectedRowCount();
					
				});
				if($this->AffectedRowCount) {
					
					return true;	
				
				} else {
					
					return false;
					
				}
				
			} else if ($crudOperationType == "update") {
				
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = new \stdClass();
					
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
					
					$this->result = new \stdClass();
					
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
					
					$this->result = new \stdClass();
					$this->dbResultRows = [];
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					
					while (yield $this->result->advance()) {
						
						$this->dbResultRows = $this->result->getCurrent();
					}
				});
				
				if(($this->dbResultRows != "") && (!is_null($this->dbResultRows)) && (is_array($this->dbResultRows)) && (count($this->dbResultRows) > 0)) {
					
					if ($this->checkIfQueryIsDescribed($this->dbResultRows) == "NO") {
						//echo "\n query is NOT DESCRIBED\n";
						return $this->dbResultRows;
						
					} else {
						//echo "\n query is DESCRIBED\n";
						return [];
						
					}
					

				} else {
					
					return [];
					
				}
				
			} else if ($crudOperationType == "selectMultiple") {
				
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = new \stdClass();
					$this->dbResultRows = [];
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					
					while (yield $this->result->advance()) {
						
						$this->dbResultRows[] = $this->result->getCurrent();
					}
				});
				
				/* if(($this->dbResultRows != "") && (!is_null($this->dbResultRows)) && (is_array($this->dbResultRows)) && (count($this->dbResultRows) > 0)) {
					
					return $this->dbResultRows;

				} else {
					
					return [];
					
				} */
				
				if(($this->dbResultRows != "") && (!is_null($this->dbResultRows)) && (is_array($this->dbResultRows)) && (count($this->dbResultRows) > 0)) {
					
					if ($this->checkIfQueryIsDescribed($this->dbResultRows) == "NO") {
						//echo "\n query is NOT DESCRIBED\n";
						return $this->dbResultRows;
						
					} else {
						//echo "\n query is DESCRIBED1 \n";
						return [];
						
					}
					

				} else {
					//echo "\n query is DESCRIBED2 \n";
					return [];
					
				}
				
			} else if ($crudOperationType == "describe") {
				
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = new \stdClass();
					$this->dbResultRows = [];
					
					$this->result = yield $preparedStmt->execute();
					
					while (yield $this->result->advance()) {
						
						$this->dbResultRows[] = $this->result->getCurrent();
					}
					
				});
				
				if(($this->dbResultRows != "") && (!is_null($this->dbResultRows)) && (is_array($this->dbResultRows)) && (count($this->dbResultRows) > 0)) {
					
					return $this->dbResultRows;

				} else {
					
					return [];
					
				}
				
			} else {
				
				throw new EaseAmpMysqlException('Invalid CRUD Operation Type input.');
				
			}
			
		} catch (EaseAmpMysqlException $e) {
			
			echo "\n EaseAmpMysqlException - ", $e->getMessage(), (int)$e->getCode();
			
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
					
					$this->result = new \stdClass();
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					$this->lastInsertId = $this->result->getLastInsertId();
					
				});
				if($this->lastInsertId) {
					
					return $this->lastInsertId;	
				
				} else {
					
					return "";
					
				}
				
			} else if ($crudOperationType == "insertWithUUIDAsPrimaryKey") {
			
				/* \Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					$this->lastInsertId = $this->result->getLastInsertId();//NEED to GET STRING Typecasted Last Inserted ID here
					
				});
				if($this->lastInsertId) {
					
					return $this->lastInsertId;	
				
				} else {
					
					return "";
					
				} */ 
				
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = new \stdClass();
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					$this->AffectedRowCount = $this->result->getAffectedRowCount();
					
				});
				if($this->AffectedRowCount) {
					
					return true;	
				
				} else {
					
					return false;
					
				}
				
				
			} else if ($crudOperationType == "update") {
				
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = new \stdClass();
					
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
					
					$this->result = new \stdClass();
					
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
					
					$this->result = new \stdClass();
					$this->dbResultRows = [];
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					
					while (yield $this->result->advance()) {
						
						$this->dbResultRows = $this->result->getCurrent();
					}
				});
				
				if(($this->dbResultRows != "") && (!is_null($this->dbResultRows)) && (is_array($this->dbResultRows)) && (count($this->dbResultRows) > 0)) {
					
					if ($this->checkIfQueryIsDescribed($this->dbResultRows) == "NO") {
						//echo "\n query is NOT DESCRIBED\n";
						return $this->dbResultRows;
						
					} else {
						//echo "\n query is DESCRIBED\n";
						return [];
						
					}
					

				} else {
					
					return [];
					
				}
				
			} else if ($crudOperationType == "selectMultiple") {
				
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = new \stdClass();
					$this->dbResultRows = [];
					
					$this->result = yield $preparedStmt->execute($valuesArray);
					
					while (yield $this->result->advance()) {
						
						$this->dbResultRows[] = $this->result->getCurrent();
					}
					
				});
				
				/* if(($this->dbResultRows != "") && (!is_null($this->dbResultRows)) && (is_array($this->dbResultRows)) && (count($this->dbResultRows) > 0)) {
					
					return $this->dbResultRows;

				} else {
					
					return [];
					
				} */
				
				if(($this->dbResultRows != "") && (!is_null($this->dbResultRows)) && (is_array($this->dbResultRows)) && (count($this->dbResultRows) > 0)) {
					
					if ($this->checkIfQueryIsDescribed($this->dbResultRows) == "NO") {
						//echo "\n query is NOT DESCRIBED\n";
						return $this->dbResultRows;
						
					} else {
						//echo "\n query is DESCRIBED1 \n";
						return [];
						
					}
					

				} else {
					//echo "\n query is DESCRIBED2 \n";
					return [];
					
				}
				
			} else if ($crudOperationType == "describe") {
				
				\Amp\Loop::run(function () use($preparedStmt, &$valuesArray, $crudOperationType) {
					
					$this->result = new \stdClass();
					$this->dbResultRows = [];
					
					$this->result = yield $preparedStmt->execute();
					
					while (yield $this->result->advance()) {
						
						$this->dbResultRows[] = $this->result->getCurrent();
					}
					
				});
				
				if(($this->dbResultRows != "") && (!is_null($this->dbResultRows)) && (is_array($this->dbResultRows)) && (count($this->dbResultRows) > 0)) {
					
					return $this->dbResultRows;

				} else {
					
					return [];
					
				}
				
			} else {
				
				throw new EaseAmpMysqlException('Invalid CRUD Operation Type input.');
				
			}
			
		} catch (EaseAmpMysqlException $e) {
			
			echo "\n EaseAmpMysqlException - ", $e->getMessage(), (int)$e->getCode();
			
		}
		
	}
	
	public function isAssoc(array $arr)	{
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
	
	public function getTableRelSinglePrimaryKeyColumnName($table_name) {
		
		$primary_key_column_name = "";
		
		if (is_null(trim($table_name))) {
			
			return $primary_key_column_name;
			
		} else {
						
			$columnDetails = $this->getTableRelSinglePrimaryKeyColumnDetails($table_name);
			
			if (count($columnDetails) > 0) {
				
				$primary_key_column_name = $columnDetails["Field"];
				
			}
			
			return $primary_key_column_name;	
			
		}
		
	}
	
	public function getTableRelSinglePrimaryKeyColumnDetails($table_name) {
		
		$responseArray = [];
		
		if (is_null(trim($table_name))) {
			
			return $responseArray;
			
		} else {
			
			//Describe Query
			$query = "DESCRIBE " . $table_name;

			$valueArray = array();
			
			//echo "All inputs to table details query are Valid\n";
			$table_details_result = $this->executeQuery($query, $valueArray, "describe");
			//$table_details_result = ea_get_table_rel_column_data_types_and_details($table_name);
			
			if (count($table_details_result) > 0) {
		
				foreach($table_details_result as $column){
					
					if ($column["Key"] == "PRI") {
						
						$responseArray = $column;
						break;
						
					}
				}
				
			}
			
			return $responseArray;	
			
		}
		
	}
	
	public function checkIfQueryIsDescribed($dbQueryResult) {
		
		if ((is_array($dbQueryResult)) && (count($dbQueryResult) > 0)) {
			
			foreach($dbQueryResult as $column){
				
				if ((is_array($column)) && (count($column) == "6") && (array_key_exists('Field', $column)) && (array_key_exists('Type', $column)) && (array_key_exists('Null', $column)) && (array_key_exists('Key', $column)) && (array_key_exists('Default', $column)) && (array_key_exists('Extra', $column))) {
					
					$response = "YES";
					break;
					
				} else {
					
					$response = "NO";
					break;
					
				}
			}
			
			return $response;	 
			
		} else {
			
			$response = "NO";
		
			return $response;
			
		}
		
	}
	

	
	//$this->pool->close();
}
?>