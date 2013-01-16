<?php

class Database
{
	public function connection()
	{
		try
		{
			$databaseOptions = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ);
			return new PDO('sqlite:storage.sqlite', '', '', $databaseOptions);
		} catch(PDOException $exception)
		{
			trigger_error('Database connection failed with message: "' . $exception->getMessage() . '"', E_USER_ERROR);
		}
		return false;
	}
}