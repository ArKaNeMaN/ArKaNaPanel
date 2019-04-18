<?php 
class DataBase {
	private $mysqli;
	private $prefix;
	
	public function __construct( $ip_address = SQL_HOST, $user = SQL_USER, $password = SQL_PASS, $bd = SQL_DB, $prefix = SQL_PREFIX, $codirovka = SQL_ENCODE) {
		$this->prefix = $prefix;
		$this->mysqli = new mysqli( $ip_address, $user, $password, $bd);
		$this->mysqli->query("SET NAMES '{$codirovka}'");
	}
	
	private function query ( $query, $insert = false )
	{
		if( !$insert )
			return $this->mysqli->query( $query );

		$this->mysqli->query( $query );
		return $this->mysqli->insert_id;
	}	
	private function wheres ( $where )
	{
		$wheres =  "";
		foreach ( $where as $field => $value ) {
			$wheres .= "`".$field."`,";
		}
		$wheres = substr( $wheres, 0, -1 );
		$wheres .= " = ";
		foreach ( $where as $value ) {
			$wheres .= "'".$this->real_s( $value )."',";
		}
		$wheres = substr( $wheres, 0, -1 );
		return $wheres;
	}	
	private function andwhere ( $where )
	{
		$wheres =  "";
		foreach ( $where as $field => $value ) {
			$wheres .= "`".$field."` = '".$this->real_s( $value )."' AND ";
		}
		$wheres = substr( $wheres, 0, -4 );
		return $wheres;
	}
	
	private function real_s( $data ) {
		$data = $this->mysqli->real_escape_string( $data );
		return $data;
	}
	
	public function select ( $table_name, $fields, $where = "", $order = "", $up = true, $limit = "", $debug = 0 ) {
		if( $fields != "*" ) {
			for ($i = 0; $i < count($fields); $i++) {
				if((strpos($fields[$i], "(") === false) && ($fields[$i] != "*")) $fields[$i] = "`".$fields[$i]."`";
			}
			$fields = implode(",", $fields);
		}
		$table_name = $this->prefix.$table_name;
		if( is_array( $order ) ) {
			$order = $order[0];
		}
		else if( !$order ) {
			$order = "ORDER BY `id`";
		}
		else {
			if( $order != "RAND()" ) {
				$order = "ORDER BY {$order}";
				if ( !$up ) {
					$order .= " DESC";
				}
			}
			else $order = "ORDER BY {$order}";
		}
		if ($limit) {
			$limit = "LIMIT {$limit}";
		}
		
		if ( is_array( $where ) ){
			$where = $this->andwhere( $where );
			$query = "SELECT {$fields} FROM `{$table_name}` WHERE {$where} {$order} {$limit}";
		}	
		else {
			$where = ( $where != "" ) ? 'WHERE '.$where : "";
			$query = "SELECT {$fields} FROM `{$table_name}` {$where} {$order} {$limit}";
		}
		if( $debug == 1) die( $query );
		elseif( $debug == 2) return $query;
		$result_set = $this->query($query);
		if ( !$result_set )
			return false;
		$i = 0;
		$data = "";
		while ($row = $result_set->fetch_assoc()) {
			$data[$i] = $row;
			$i++;
		}
		$result_set->close();
		$data = is_array( $data ) ? $data : false;
		return $data;
	}
	
	public function insert ( $table_name, $new_values, $debug = 0 ){
		$table_name = $this->prefix.$table_name;
		$query = "INSERT INTO {$table_name} (";
		foreach ($new_values as $field => $value) 
			$query .= "`".$field."`,";
		$query = substr($query, 0, -1);
		$query .= ") VALUES (";
		foreach ( $new_values as $value ) $query .= "'".$this->real_s( $value )."',";
		$query = substr($query, 0, -1);
		$query .= ")";
		if( $debug == 1) die( $query );
		elseif( $debug == 2) return $query;
		return $this->query( $query, TRUE );
	}
	
	public function update ($table_name, $new_values, $where, $debug = false) {
		if( !isset( $table_name ) ||  !is_array( $new_values ) ||  !is_array( $where )) {
			die("Заполните все данные");
			return false;
		}
		
		$table_name = $this->prefix.$table_name;
		$where = $this->andwhere( $where );
		$set = "";
		foreach ($new_values as $field => $value) {
			$set .= " `".$field."` = '".$this->real_s( $value )."',";
		}
		$set = substr($set, 0, -1);
		
		$query = "UPDATE `{$table_name}` SET {$set} WHERE {$where}";
		if( $debug ) {
			echo $query;
			die();
		}
		return $this->query($query);
	}
	
	public function delete( $table_name, $where = "" ) {
		$table_name = $this->prefix.$table_name;
		if ($where) {
			if( is_array( $where ) )
				$where = $this->andwhere( $where );
			$query = "DELETE FROM `{$table_name}` WHERE {$where}";

			return $this->query($query);
		}
		else return false;
	}
	#########################################################
	public function search ($table_name, $words, $fields, $order = 'id') {
		$words = mb_strtolower($words);
		$words = trim($words);
		$words = quotemeta($words);
		if ($words == "") return false;
		$where = "";
		$arraywords = explode(" ", $words);
		$logic = "OR";
		foreach ($arraywords as $key => $value) {
			if (isset ($arraywords[$key - 1])) $where .= $logic;
			for ($i = 0; $i<count($fields); $i++) {
				$where .="`".$fields[$i]."` LIKE'%".$this->real_s($value)."%'";
				if (($i+1) != count($fields)) $where .= "OR";
			}
		}
		$results = $this->select($table_name, array("*"), $where, $order);
		if (!$results) return false;
		$k = 0;
		$data = array();
		for ($i = 0; $i <count($results); $i++) {
			for ($j = 0; $j <count($fields); $j++) {
				$results[$i][$fields[$j]] = mb_strtolower(strip_tags($results[$i][$fields[$j]]));
			}
			$data[$k] = $results[$i];
			$data[$k]["relevant"] = $this->getRelevantForSearch($results[$i], $fields, $words);
			$k++;
		}
		$data = $this->orderResultSearch($data, "relevant");
		return $data;
	}
	
	private function getRelevantForSearch($result, $fields, $words) {
		$relevant = 0;
		$arraywords = explode(" ", $words);
		for ($i = 0; $i < count($fields); $i++) {
			for ($j = 0; $j < count($arraywords); $j++) {
				$relevant += substr_count( $result[$fields[$i]], $arraywords[$j] );
			}
		}
		return $relevant;
	}
	
	private function orderResultSearch($data, $order) {
		for ($i = 0; $i <count($data) - 1; $i++) {
			$k = $i;
			for ($j = $i +1;$j<count($data); $j++) {
				if($data[$j][$order] > $data[$k][$order]) $k = $j;
			}
			$temp = $data[$k];
			$data[$k] = $data[$i];
			$data[$i] = $temp;
		}
		return $data;
	}
	
	public function __destruct() {
		if( $this->mysqli ) $this->mysqli->close( );
	}
}
?>