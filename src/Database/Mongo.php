<?php

namespace PhalApi\Database;

use \MongoDB\GridFS\Bucket;

class Mongo
{
	protected static $INSTANCE;
	protected        $host     = '';
	protected        $port     = '';
	protected        $user     = '';
	protected        $password = '';
	protected        $db       = '';

	protected $connect = '';

	public function __construct($config)
	{
		if (empty($config)) {
			throw(new \Exception('mongo 配置文件错误'));
		}
		$this->host     = isset($config['host']) ? $config['host'] : '';
		$this->port     = isset($config['port']) ? $config['port'] : '';
		$this->user     = isset($config['user']) ? $config['user'] : '';
		$this->password = isset($config['pwd']) ? $config['pwd'] : '';
		$this->db       = isset($config['db']) ? $config['db'] : '';
		if (empty($this->user)) {
			$connConf = sprintf('mongodb://%s:%s/%s', $this->host, $this->port, $this->db);
		} else {
			$connConf = sprintf('mongodb://%s:%s@%s:%s/%s', $this->user, $this->password, $this->host, $this->port, $this->db);
		}

		//$this->connect  = new \MongoClient();
		$manager       = new \MongoDB\Driver\Manager($connConf);
		$this->connect = $manager;
	}

	public function query($collection, $filter, $option = [])
	{
		$query    = new \MongoDB\Driver\Query($filter, $option);
		$datalist = $this->connect->executeQuery($collection, $query);
		return $datalist;
	}

	public function getDb()
	{
		return $this->connect;
	}

	public function getGridfsByName($fileName, $collection = 'batch_register')
	{
		$grid        = new Bucket($this->connect, $this->db, array('bucketName' => $collection));
		$destination = fopen('php://temp', 'w+b');
		try{
			$grid->downloadToStreamByName($fileName, $destination);
		}catch (\Exception $e){
			return [];
		}
		$datalist = stream_get_contents($destination, -1, 0);
		return $datalist;
	}

	public function __call($name, $arguments)
	{
		// TODO: Implement __call() method.
		return call_user_func_array([$this->connect, $name], $arguments);
	}

}