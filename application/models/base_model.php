<?php

/**
 * @author LongL
 * @copyright 2014
 */

class Category_model extends CI_Model{
    
    protected $table_name;
    
    protected $primary_key = 'id';
    
    protected $fetch_items = array();
    
	function __construct()
	{
	   parent::__construct();
	}
	
	function get($select = "*", $where = "")
	{
        $this->db->cache_off();
		$this->db->select($select);
		if($where && $where != "")
		{
			$this->db->where($where);
		}
		#Query
		$query = $this->db->get("tec_category");
		$result = $query->row();
		$query->free_result();
		return $result;
	}
	
	function fetch($select = "*", $where = "", $order = "category_id", $by = "DESC", $start = -1, $limit = 0)
	{
	    
        $this->db->cache_off();
		$this->db->select($select);
		if($where && $where != "")
		{
			$this->db->where($where, NULL, false);
		}
		if($order && $order != "" && $by && ($by == "DESC" || $by == "ASC"))
		{
            $this->db->order_by($order, $by);
		}
		if((int)$start >= 0 && $limit && (int)$limit > 0)
		{
			$this->db->limit($limit, $start);
		}
		#Query
		$query = $this->db->get("tec_category");
		$result = $query->result();
		$query->free_result();
		return $result;
	}
	
	function fetch_join($select = "*", $join, $table, $on, $where = "", $order = "category_id", $by = "DESC", $start = -1, $limit = 0, $distinct = false)
	{
        $this->db->cache_off();
		$this->db->select($select);
		if($join && ($join == "INNER" || $join == "LEFT" || $join == "RIGHT") && $table && $table != "" && $on && $on != "")
		{
			$this->db->join($table, $on, $join);
		}
		if($where && $where != "")
		{
			$this->db->where($where, NULL, false);
		}
		if($order && $order != "" && $by && ($by == "DESC" || $by == "ASC"))
		{
            $this->db->order_by($order, $by);
		}
		if((int)$start >= 0 && $limit && (int)$limit > 0)
		{
			$this->db->limit($limit, $start);
		}
		if($distinct && $distinct == true)
		{
			$this->db->distinct();
		}
		#Query
		$query = $this->db->get("tec_category");
		$result = $query->result();
		$query->free_result();
		return $result;
	}
    
    function fetch_join3($select = "*"  , $join , $table , $on , $join1 , $table1 , $on1 , $join2 , $table2 , $on2 ,$join3 , $table3 , $on3 , $where = "", $order = "id", $by = "DESC", $start = -1, $limit = 0, $distinct = false)
	{
        $this->db->cache_off();
		$this->db->select($select);
		if($join && ($join == "INNER" || $join == "LEFT" || $join == "RIGHT") && $table && $table != "" && $on && $on != "")
		{
			$this->db->join($table, $on, $join);
		}
        if($join1 && ($join1 == "INNER" || $join1 == "LEFT" || $join1 == "RIGHT") && $table1 && $table1 != "" && $on1 && $on1 != "")
		{
			$this->db->join($table1, $on1, $join1);
		}
        if($join2 && ($join2 == "INNER" || $join2 == "LEFT" || $join2 == "RIGHT") && $table2 && $table2 != "" && $on2 && $on2 != "")
		{
			$this->db->join($table2, $on2, $join2);
		}
        if($join3 && ($join3 == "INNER" || $join3 == "LEFT" || $join3 == "RIGHT") && $table3 && $table3 != "" && $on3 && $on3 != "")
		{
			$this->db->join($table3, $on3, $join3);
		}
		if($where && $where != "")
		{
			$this->db->where($where, NULL, false);
		}
		if($order && $order != "" && $by && ($by == "DESC" || $by == "ASC"))
		{
            $this->db->order_by($order, $by);
		}
		if((int)$start >= 0 && $limit && (int)$limit > 0)
		{
			$this->db->limit($limit, $start);
		}
		if($distinct && $distinct == true)
		{
			$this->db->distinct();
		}
		#Query
		$query = $this->db->get('tec_category');
		$result = $query->result();
		$query->free_result();
		return $result;
	}
	
	function add($data)
	{
		return $this->db->insert("tec_category", $data);
	}
	
	function update($data, $where = "")
	{
    	if($where && $where != "")
    	{
			$this->db->where($where);
    	}
		return $this->db->update("tec_category", $data);
	}
	
	function delete($value, $field = "category_id", $in = true)
    {
		if($in == true)
		{
			$this->db->where_in($field, $value);
		}
		else
		{
            $this->db->where($field, $value);
		}
		return $this->db->delete("tec_category");
    }
}

?>