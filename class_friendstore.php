<?php

/**
 * FriendsRoll Wordpress plugin
 * Copyright (c) 2008 76design/Thornley Fallis Communications
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
*/


class FriendStore
{
	var $_table;
	
	function FriendStore()
	{
		$this->_table = FRIENDSROLL_DB_TABLE;
	}
	
	function insert($f)
	{
		global $wpdb;

		$query = "INSERT INTO $this->_table VALUES ( " .
			"null, '". $wpdb->escape($f->_name) ."', '". $wpdb->escape($f->_email) ."', " .
			"'". $wpdb->escape($f->_url) ."', '". $wpdb->escape($f->_website_name) ."', " .
			"'". $wpdb->escape($f->_status) ."', now(), now()" .
			")";
		
		$wpdb->query($query);
		
		$f->_id = $wpdb->insert_id;
		return $f;
	}
	
	function update($f)
	{
		global $wpdb;
		if ($f->_id == null) return false; // Can't update a object with no id.
		$updates = array();
		foreach ($f as $key => $value)
		{
			if ($key != "_id" && $f->{$key} != null)
			{
				array_push($updates, substr($key,1,strlen($key)) . "='$value'");
			}
		}
		$values = implode(', ', $updates);
		
		$query = "UPDATE $this->_table SET $values WHERE id=$f->_id";
		
		$wpdb->query($query);
	}
	
	function delete($f)
	{
		global $wpdb;
		if ($f->_id == null) return false; // Can't delete an object without an id.
		$query = "DELETE FROM $this->_table WHERE id=$f->_id";
		
	}
	
	function find($id=null)
	{
		global $wpdb;
		if ($id == null) return false; // Can't find an object without an id.
		$query = "SELECT * FROM $this->_table WHERE id=$id LIMIT 1";
		
		$results = $wpdb->get_results($query);
		$f = $this->_createFriendFromResults($results[0]);
    
    	return $f;
	}
	
	function findByEmail($email = null) {
		global $wpdb;
		if ($email == null) return false; // Can't find an object without an id.
		$query = "SELECT * FROM $this->_table WHERE email='" . $wpdb->escape($email) ."' LIMIT 1";
		
		$results = $wpdb->get_results($query);
		if (!empty($results)) {
			return $results;
		}
		return false;
	}
	
	function _createFriendFromResults($results)
	{
		$f = new Friend();
		$f->_id = $results->id;
		$f->_name = $results->name;
	    $f->_email = $results->email;
	    $f->_url = $results->url;
	    $f->_website_name = $results->website_name;
	    $f->_status = $results->status;
	    $f->_created = $results->created;
	    $f->_modified = $results->modified;
	    return $f;
	}
	
	function findAll($status = null, $start=0, $num=5)
	{
		global $wpdb;
		$status_where = '';
		if ($status != null && in_array($status, array('pending','accepted','rejected')))
		{
			$status_where = "AND status='$status'";
		}
		if ($num=="all")
		{
			$num_limit = '';
		}
		else
		{
			$num_limit = 'LIMIT ' . $num;
		}
		$start -= 1;
		$query = "SELECT * FROM $this->_table WHERE id > " . $start . " $status_where ORDER BY status ASC, created DESC $num_limit";
		$results = $wpdb->get_results($query);
		$friends = array();
		foreach($results as $friend)
		{
			$friends[] = $this->_createFriendFromResults($friend);
		}
		return $friends;
	}
	
	function findByPage($num=0, $display=2, $show=1)
	{
		global $wpdb;
		
		$num_limit = "LIMIT " . $num . ", " . $display;
		
		//showme limits for display
		//showme = 0 will list all in DB
		if($show==1){ $showme = " AND status = 'accepted'"; }
		else { $showme = ''; }

		$start -= 1;	
		
		$query = "
			SELECT 
				*
			FROM $this->_table 
			WHERE 1 $showme
			ORDER BY modified DESC
			$num_limit
		";
		
		$results = $wpdb->get_results($query);
		$friends = array();
		foreach($results as $friend)
		{
			$friends[] = $this->_createFriendFromResults($friend);
		}
		return $friends;
	}
	
	function findMaxPage($per_page=5, $show=1)
	{
		global $wpdb;
		
		//showme limits for display
		//showme = 0 will list all in DB
		if($show==1){ $showme = " AND status = 'accepted'"; }
		else { $showme = ''; }

		$start -= 1;	
		
		$query = "
			SELECT 
				*
			FROM $this->_table 
			WHERE 1 $showme
		";
		
		$total = $wpdb->query($query);
		
		return ceil((int)$total/(int)$per_page);
	}
}

class Friend
{
	var $_id = null;
	var $_name = null;
	var $_email = null;
	var $_url = null;
	var $_website_name = null;
	var $_status = null;
	var $_created = null;
	var $_modified = null;
	
	function Friend($initialize = null, $new = true)
	{
		if ($initialize == null && $new) return;
		
		$this->_id = $initialize['friendsroll_id'];
		$this->_name = $initialize['friendsroll_name'];
		$this->_email = $initialize['friendsroll_email'];
		//if (!preg_match("/^/", $initialize['friendsroll_url'])) $initialize['friendsroll_url'] = "http://" . $initialize['friendsroll_url'];
		//$initialize['friendsroll_url'] = "http://" . $initialize['friendsroll_url'];
		$this->_url = $initialize['friendsroll_url'];
		$this->_website_name = $initialize['friendsroll_website_name'];
		$this->_status = "pending";
	}
}
?>