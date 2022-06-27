<?php

namespace App\Services;

class ACPACService{
 //establish database connection
	private $serverName = "41.59.91.198";
    private $connectionOptions = [
		"Database"=>"ARMSIntegration",
		"Uid"=>"arms",
		"PWD"=>"mnma2o22"
	];
	private $connection;

	public function __construct()
	{
		$this->connect();
	}

	private function connect()
	{
        $this->connection = sqlsrv_connect ($this->serverName,$this->connectionOptions);
        if(!$this->connection){
        	die(sqlsrv_errors());
        }
	}

	public function query($sql)
	{   
		try{
		   $results = sqlsrv_query($this->connection,$sql);
		   return $results;
		}catch(\Exception $e){
			die(sqlsrv_errors());
		}
	}

	public function close()
	{
        if($this->connection){
        	sqlsrv_close($this->connection);
        }
	}
}


