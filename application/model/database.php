<?php
class database{
	var $conn;
	function __construct()
	{
		try{
			$this->conn = new PDO('mysql:host=ephesus.cs.cf.ac.uk;dbname=c1340154;','c1340154', 'caffenero');
			$this->conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);	
		} catch(PDOException $e){
			die($e->getMessage());
		}
	}

	function prep_keys($data = array())
	{
		if(is_array($data) &&  !empty($data)){
			foreach($data as $key => $value){
				if($key{0} != ':'){
					$data[':'.$key] = $value;
					unset($data[$key]);
				}
			}
			return $data;
		}
		return array($data);
	}

	function select($sql, $data = array())
	{
		$stmt = $this->conn->prepare($sql);
		$stmt->execute($data);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	function insert($table, $data)
	{
		foreach($data as $key => $value)
		{
			$columns[] = $key;
		}
		$stmt = $this->conn->prepare("INSERT INTO `$table` (`".implode('`, `', $columns)."`) VALUES (".implode(', ', array_keys($this->prep_keys($data))).")");
		$stmt->execute($this->prep_keys($data));
		return $this->conn->lastInsertId();
	}

	function update($table, $data,$where_column,$ids)
	{
		$state = true;
		foreach ($data as $key => $value) 
		{
			$set_data[] = "`$key` = :" . $key; 
		}

		$stmt = $this->conn->prepare("UPDATE `$table` SET ".implode(', ', $set_data)." WHERE `$where_column` = :whereid");

		foreach ($ids as $id) {
			$data['whereid'] = $id;
			$state = $stmt->execute($this->prep_keys($data));
		}
		
		return $state;
	}

	function delete($table,$where_column,$ids = array())
	{
		$stmt = $this->conn->prepare("DELETE FROM `$table` WHERE `$where_column` = :whereid");
		foreach ($ids as $id) {
			$data['whereid'] = $id;
			$state = $stmt->execute($this->prep_keys($data));
		}
		return $stmt->execute();
	}

	function force_disconect()
	{
		$this->conn = null;
	}

	function __destruct()
	{
		$this->conn = null;
	}
}