<?php

class User
{

	public $storage;
	private static $database;

	public function __construct(PDO &$database)
	{
		self::$database = & $database;
		if(empty($_SESSION['user']))
		{
			$_SESSION['user'] = new stdClass();
		}
		$this->storage = & $_SESSION['user'];
	}

	public function isLoggedIn()
	{
		if(!empty($this->storage->id))
		{
			return true;
		}
		return false;
	}

	public function logIn($username, $password)
	{
		$query = self::$database->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
		$query->bindValue(':username', $username, PDO::PARAM_STR);
		$query->bindValue(':password', sha1($password), PDO::PARAM_STR);
		$query->execute();
		$record = $query->fetch();

		if(!empty($record))
		{
			$this->storage->id = $record->id;
			return true;
		}
		return false;
	}

	public function createAccount($username, $password, $timezone)
	{
		$query = self::$database->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
		$query->bindValue(':username', $username, PDO::PARAM_STR);
		$query->execute();
		$exists = $query->fetchColumn();

		if(empty($exists))
		{
			$query = self::$database->prepare("INSERT INTO users (username, password, timezone) VALUES (:username, :password, :timezone)");
			$query->bindValue(':username', $username, PDO::PARAM_STR);
			$query->bindValue(':password', sha1($password), PDO::PARAM_STR);
			$query->bindValue(':timezone', $timezone, PDO::PARAM_STR);
			$query->execute();

			$this->logIn($username, $password);
			return true;
		}
		return false;
	}
}