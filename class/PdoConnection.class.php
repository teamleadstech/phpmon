<?php
/**
 * PdoConnection - Encapsulated pdo that fits current dev habit
 * User: Leo Liu
 * Date: 2016-05-12
 */

class PdoConnection
{
	protected $dsn;
	protected $user;
	protected $password;
	protected $options;

	protected $pdo;

	protected $stmt;

	private $is_connected = false;

	protected $defaultFetchMode = PDO::FETCH_ASSOC;

	public function __construct($host = false, $dbname = false, $user = false, $password = false, $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"))
	{
        if(!$host || !$dbname || !$user || !$password){
			$host = MYSQL_HOST;
			$dbname = MYSQL_DBNAME;
			$user = MYSQL_USER;
			$password = MYSQL_PASSWD;
		}
		$dsn = "mysql:dbname=$dbname;host=$host";
		
		$this->dsn = $dsn;
		$this->user = $user;
		$this->password = $password;
		$this->options = $options;
		$this->pdo = new PDO($dsn, $user, $password, $options);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->is_connected = true;
	}

	public function connect()
	{
		if ( $this->is_connected ) return false;
		$this->pdo = new PDO($this->dsn, $this->user, $this->password, $this->options);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->is_connected = true;
		return true;
	}

	public function close()
	{
		unset($this->pdo);

		$this->is_connected = false;
	}

	public function getPdo()
	{
		return $this->pdo;
	}

	public function getFetchMode()
	{
		return $this->defaultFetchMode;
	}

	public function setFetchMode($fetchMode)
	{
		$this->defaultFetchMode = $fetchMode;
	}

	public function isConnected()
	{
		return $this->is_connected;
	}

	public function errorCode()
	{
		return $this->pdo->errorCode();
	}

	public function errorInfo()
	{
		return $this->pdo->errorInfo();
	}

	private function bindTypedValues($stmt, array $params, array $types)
	{
		// Check whether parameters are positional or named. Mixing is not allowed, just like in PDO.
		if (is_int(key($params))) {
			// Positional parameters
			$typeOffset = array_key_exists(0, $types) ? -1 : 0;
			$bindIndex = 1;
			foreach ($params as $value) {
				$typeIndex = $bindIndex + $typeOffset;
				if (isset($types[$typeIndex])) {
					$stmt->bindValue($bindIndex, $value, $types[$typeIndex]);
				} else {
					$stmt->bindValue($bindIndex, $value);
				}
				++$bindIndex;
			}
		} else {
			// Named parameters
			foreach ($params as $name => $value) {
				if (isset($types[$name])) {
					$stmt->bindValue($name, $value, $types[$name]);
				} else {
					$stmt->bindValue($name, $value);
				}
			}
		}
	}

	public function executeQuery($sql, array $params = array(), $types = array())
	{
		$stmt = null;

		if ($params) {
			$stmt = $this->pdo->prepare($sql);
			if ($types) {
				$this->bindTypedValues($stmt, $params, $types);
				$stmt->execute();
			} else {
				$stmt->execute($params);
			}
		} else {
			$stmt = $this->pdo->query($sql);
		}

		$stmt->setFetchMode($this->defaultFetchMode);

		return $stmt;
	}

	public function fetchAll($sql, array $params = array(), $types = array())
	{
		return $this->executeQuery($sql, $params, $types)->fetchAll();
	}

	public function fetchAssoc($sql, array $params = array())
	{
		return $this->executeQuery($sql, $params)->fetch(PDO::FETCH_ASSOC);
	}

	public function fetchArray($sql, array $params = array())
	{
		return $this->executeQuery($sql, $params)->fetch(PDO::FETCH_NUM);
	}

	public function prepare($sql)
	{
		$stmt = $this->pdo->prepare($sql);
		$stmt->setFetchMode($this->defaultFetchMode);
		return $stmt;
	}

	public function query($sql)
	{
		$this->stmt = $this->pdo->query($sql);
		$this->stmt->setFetchMode($this->defaultFetchMode);
		return $this->stmt;
	}

	public function execute(array $params = array())
	{
		if ( $params ) {
			return $this->stmt->execute($params);
		} else {
			return $this->stmt->execute();
		}
	}

	public function rowCount()
	{
		return $this->stmt->rowCount();
	}

	public function exec($sql)
	{
		return $this->pdo->exec($sql);
	}

	public function lastInsertId($seqName = null)
	{
		return $this->pdo->lastInsertId($seqName);
	}

	public function quote($string)
	{
		return $this->pdo->quote($string);
	}

}


 /*
//== Sample Code by Sam==
 
//intialize the pdo connection 
$db = new PdoConnection();


//check database connection
var_dump($db->isConnected());


//insert/update with query()
$sql = "INSERT INTO `aaa_testable` (`Memo`) VALUES ('I am a record');";
$result = $db->query($sql);
if($result){
	echo $result->rowCount()." rows inserted...\n";
	echo $db->lastInsertId()." last insert Id...\n";
}

$sql = "UPDATE `aaa_testable` SET `Memo`='I am an update' ORDER BY Id DESC LIMIT 2;";
$result = $db->query($sql);
if($result){
	echo $result->rowCount()." rows effected...\n";
}

//use quote for injection protection 
$memo = "' OR '1'='1' --";
$memo = $db->quote($memo);
$sql = "INSERT INTO `aaa_testable` (`Memo`) VALUES ($memo);";
$result = $db->query($sql);
if($result){
	echo $result->rowCount()." rows inserted...\n";
	echo $db->lastInsertId()." last insert Id...\n";
}

//use prepare and value bind
$memo = "I'm a happy record!";
$sql = "INSERT INTO `aaa_testable` (`Memo`) VALUES (:memo);";
$stmt = $db->prepare($sql);
$stmt->bindValue("memo", $memo);
$stmt->execute();
if($stmt){
	echo $stmt->rowCount()." rows inserted...\n";
	echo $db->lastInsertId()." last insert Id...\n";
}


//fetch search results with fetchArray,fetchAssoc,fetchAll
$sql = "SELECT * FROM aaa_testable ORDER BY Id DESC LIMIT 3;";
print_r($db->fetchArray($sql));
print_r($db->fetchAssoc($sql));
print_r($db->fetchAll($sql));
 */
