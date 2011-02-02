<?php

class Cr_Table{
	
	public $table;
	protected $db, $id_field, $order, $fields, $last_query;
	protected $last_id = null;
	
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
		
		$this->checkErrors();
		
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
		if(!is_string($query[1]))
			$statement = $this->db->prepare($query[0], array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		else
			$statement = $this->db->prepare($query[0]);
		
		$res = $this->db->execute($query[1]);
		
		$this->checkErrors();
		
		$this->last_query = $query[0];
		$this->last_id = $this->db->lastInsertId();
		
		return $res;
	}
	
	private function checkErrors()
	{
		if($this->db->errorCode() !== "00000")
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
	
	public function getByQueryOne($query = "", $fields = '*')
	{
		if(is_array($query))
		{
			return $this->runPreparedQuery(array("SELECT {$fields} FROM {$this->table} {$query[0]}", $query[1]), true);
		}
		return $this->runQuery("SELECT {$fields} FROM {$this->table} {$query}", true);
	}
	
	public function getByQueryAll($query = "", $fields = '*')
	{
		if(is_array($query))
		{
			return $this->runPreparedQuery(array("SELECT {$fields} FROM {$this->table} {$query[0]}", $query[1]));
		}
		return $this->runQuery("SELECT {$fields} FROM {$this->table} {$query}");
	}
	
	public function getByQuery($query)
	{
		return $this->getByQueryAll($query);
	}
	
	public function getAll($order = "", $fields = '*')
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
			$query = array("SELECT {$fields} FROM {$this->table} {$query[0]} LIMIT {$limit_clause}", $query[1]);
			
			return $this->runQuery($query);
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
			$value = $value = $this->prepareValue($value);
			$fields .= "{$key} = {$value},";
		}
		$fields = substr($fields, 0, -1);
		
		$where_clause = !empty($where) ? $where : '';
		
		$query = "UPDATE {$this->table} SET {$fields} {$where_clause}";
		
		return $this->runNonQuery($query);
	}
	
	public function updateById($data, $id)
	{
		$id = $this->db->quote($id);
		return $this->update($data, "WHERE {$this->id_field} = {$id}");
	}
	
	public function total($query = "")
	{
		$query = "SELECT COUNT(*) as total FROM {$this->table} {$query}";
		$res = $this->runQuery($query);
		return $res[0]['total'];
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
			$prepared_value = $value !== '' ? $this->db->quote(htmlspecialchars(trim($value))) : 'NULL';
		}elseif(get_class($value) == 'Cr_DbExpr'){
			$prepared_value = $value->expresion;
		}else{
			throw new Exception($this->table . ': Invalid data for query.');
		}
		
		return $prepared_value;
	}
	
}