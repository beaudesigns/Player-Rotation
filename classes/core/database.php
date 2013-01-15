<?php

class Database
{
	public function connection()
	{
		try
		{
			$databaseOptions = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
			return new PDO('sqlite:storage.sq3', '', '', $databaseOptions);
		} catch(PDOException $exception)
		{
			trigger_error('Database connection failed with message: "' . $exception->getMessage() . '"', E_USER_ERROR);
		}
		return false;
	}
}