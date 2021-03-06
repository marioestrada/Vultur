<?php

class Cr_Table{
	
	public $table;
	protected $db, $id_field, $order, $fields, $last_query;
	protected $last_id = null;
	protected $attributes_blacklist = array();
	protected $attributes_whitelist = array();
	
	public function __construct($table, $db, $id_field = "id", $order = "", $fields = '*')
	{
		$this->db = $db;
		$this->fields = $fields;
		$this->table = $table;
		$this->id_field = $id_field;
		$this->order = empty($order) ? "{$id_field} ASC" : $order;
	}
	
	protected function runPreparedQuery($query, $one = false)
	{
		if(!is_string(key($query[1])))
			$statement = $this->db->prepare($query[0], array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		else
			$statement = $this->db->prepare($query[0]);
		
		$statement->execute($query[1]);
		$this->checkErrors($statement);
		
		$method = $one ? 'fetch' : 'fetchAll';
		$res = $statement->$method(PDO::FETCH_ASSOC);
		
		$this->last_query = $query[0];
		
		return $res;
	}
	
	protected function runQuery($query, $one = false)
	{
		$statement = $this->db->query($query);
		
		$this->checkErrors();
		
		$method = $one ? 'fetch' : 'fetchAll';
		$res = $statement->$method(PDO::FETCH_ASSOC);
		
		$this->last_query = $query;
		
		return $res;
	}
	
	protected function runNonQuery($query)
	{
		$res = $this->db->exec($query);
		
		$this->checkErrors();
		
		$this->last_query = $query;
		$this->last_id = $this->db->lastInsertId();
		
		return $res;
	}
	
	protected function runPreparedNonQuery($query)
	{
		if(!array_key_exists(0, $query[1]))
			$statement = $this->db->prepare($query[0], array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		else
			$statement = $this->db->prepare($query[0]);
		
		$res = $statement->execute($query[1]);
		$this->checkErrors($statement);
		
		$this->last_query = $query[0];
		$this->last_id = $this->db->lastInsertId();
		
		return $res;
	}
	
	private function checkErrors(& $obj = null)
	{
		$obj = is_null($obj) ? $this->db : $obj;
		
		if($obj->errorCode() !== '00000')
		{
			$error = $this->db->errorInfo();
			throw new Exception("[{$error[0]}] {$error[2]}");
		}
	}
	
	public function getById($id, $fields = '*')
	{
		return $this->runPreparedQuery(array("SELECT {$fields} FROM {$this->table} WHERE {$this->id_field} = ?", array($id)), true);
	}
	
	public function getByFullQueryAll($query)
	{
		if(is_array($query))
		{
			return $this->runPreparedQuery($query);
		}
		
		return $this->runQuery($query);
	}
	
	public function getByFullQueryOne($query)
	{
		if(is_array($query))
		{
			return $this->runPreparedQuery($query, true);
		}
		
		return $this->runQuery($query, true);
	}
	
	public function getByQueryOne($query = '', $fields = '*')
	{
		if(is_array($query))
		{
			return $this->runPreparedQuery(array("SELECT {$fields} FROM {$this->table} {$query[0]}", $query[1]), true);
		}
		return $this->runQuery("SELECT {$fields} FROM {$this->table} {$query}", true);
	}
	
	public function getByQueryAll($query = '', $fields = '*')
	{
		if(is_array($query))
		{
			return $this->runPreparedQuery(array("SELECT {$fields} FROM {$this->table} {$query[0]}", $query[1]));
		}
		return $this->runQuery("SELECT {$fields} FROM {$this->table} {$query}");
	}
	
	public function getByQuery($query = '', $fields = '*')
	{
		return $this->getByQueryAll($query, $fields);
	}
	
	public function getAll($order = '', $fields = '*')
	{
		$order_clause = 'ORDER BY ' . (empty($order) ? $this->order : $order);
		
		return $this->runQuery("SELECT {$fields} FROM {$this->table} {$order_clause}");
	}
	
	public function getByPage($page = 1, $limit = 10, $query = '', $fields = '*')
	{
		$page = $page < 1 ? 1 : (int) $page;
		$limit = $limit < 0 ? 0 : (int) $limit;
		
		$offset = ($page - 1) * $limit;
		$limit_clause = "{$offset}, {$limit}";
		
		if(is_array($query))
		{
			$query_do = array("SELECT {$fields} FROM {$this->table} {$query[0]} LIMIT {$limit_clause}", $query[1]);
			return $this->runPreparedQuery($query_do);
		}
		
		$where_clause = empty($query) ? '' : $query;
		$query = "SELECT {$fields} FROM {$this->table} {$where_clause} LIMIT {$limit_clause}";
		
		return $this->runQuery($query);
	}
	
	public function insert($data)
	{
		
		$fields = "";
		$values = "";
		foreach($data as $key => $value)
		{
			$value = $this->prepareValue($value);
			$values .= "{$value},";
			$fields .= "{$key},";
		}
		$fields = substr($fields, 0, -1);
		$values = substr($values, 0, -1);
		
		$query = "INSERT INTO {$this->table}({$fields}) VALUES ({$values})";
		
		return $this->runNonQuery($query);
	}
	
	public function lastId()
	{
		return $this->last_id;
	}
	
	public function lastQuery()
	{
		return $this->last_query;
	}
	
	public function update($data, $where)
	{
		$fields = "";
		foreach($data as $key => $value)
		{
			if(!empty($this->attributes_blacklist) && in_array($key, $this->attributes_blacklist))
				continue;
			
			if(!empty($this->attributes_whitelist) && !in_array($key, $this->attributes_whitelist))
				continue;
			
			$value = $value = $this->prepareValue($value);
			$fields .= "{$key} = {$value},";
		}
		$fields = substr($fields, 0, -1);
		
		$where = !empty($where) ? $where : '';
		
		if(is_array($where))
		{
			$query = "UPDATE {$this->table} SET {$fields} {$where[0]}";
			return $this->runPreparedNonQuery(array($query, $where[1]));
		}
		
		$query = "UPDATE {$this->table} SET {$fields} {$where}";
		
		return $this->runNonQuery($query);
	}
	
	public function updateById($data, $id)
	{
		return $this->update($data, array("WHERE {$this->id_field} = :id", array('id' => $id)));
	}
	
	public function total($query = '')
	{
		$res = $this->getByQueryOne($query, 'COUNT(*) as total');
		
		return $res['total'];
	}
	
	public function delete($query)
	{
		$query = "DELETE FROM {$this->table} WHERE {$query}";
		return $this->runNonQuery($query);
	}
	
	public function deleteById($id)
	{
		$id = $this->db->quote($id);
		return $this->delete("{$this->id_field} = {$id}");
	}
	
	public function prepareValue($value)
	{
		if(!is_object($value))
		{
			$prepared_value = $value !== null ? $this->db->quote(htmlspecialchars(trim($value))) : 'NULL';
		}elseif(get_class($value) == 'Cr_DbExpr'){
			$prepared_value = $value->expresion;
		}else{
			throw new Exception($this->table . ': Invalid data for query.');
		}
		
		return $prepared_value;
	}
	
}