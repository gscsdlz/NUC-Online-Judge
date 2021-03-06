<?php

class loginModel extends DB {
	public function __construct() {
		parent::__construct ();
	}
	
	private function get_privilege($user_id) {
		$res = parent::query("SELECT contest_id FROM contest WHERE user_id = ?", $user_id);
		$args = array();
		while($row = $res->fetch(PDO::FETCH_NUM)) {
			$args[$row[0]] = 1;
		}
		return $args;
	}
	
	public function login($username, $password) {
		if (! empty ( $username ) && ! empty ( $password )) {
			$res = parent::query ( "SELECT password, user_id, privilege, username FROM users WHERE username=?", $username );
			$arr = $res->fetch ( PDO::FETCH_NUM );
			
			if ($res->rowCount () != 0  && $arr[3] == $username && sha1 ( $password ) == $arr [0]) { //通过修改username字段为binary类型 解决
				parent::update("UPDATE users SET lasttime = ?, lastip = ? WHERE user_id = ?", time(), $_SERVER['REMOTE_ADDR'], $arr[1]);
				if($arr[2] == -1)
					return array($arr[1], $this->get_privilege($arr[1]));
				return array($arr [1], 1);
			}
		}
		return null;
	}
	public function register($username, $password, $password2, $nickname, $email) {
		if (! empty ( $username ) && ! empty ( $password ) && $password == $password2 && ! empty ( $email )) {
			$res = parent::query ( "SELECT user_id FROM users WHERE username=?", $username );
			if ($res->rowCount () != 0) {
				return - 1; // username has already been used
			}
			$res = parent::query ( "SELECT user_id FROM users WHERE email=?", $email );
			if ($res->rowCount () != 0) {
				return - 2; // email has already been used
			}
			parent::insert( "INSERT INTO users (user_id, username, password, nickname, email) VALUES (NULL, ?, ?, ? ,?)", $username, sha1 ( $password ), $nickname, $email);
			return 0;
		}
		return 1;
	} 
	public function updateInfo($userid, $password, $password2, $nickname, $email, $qq, $motto, $group) {
		if ($password && $password == $password2) {
			parent::update ( "UPDATE users SET password = sha1(?)	 WHERE user_id = ? LIMIT 1", $password, $userid );
		}
		$res = parent::query ( "SELECT user_id FROM users WHERE email=? AND user_id != ?", $email, $userid );
		if ($res->rowCount () != 0) {
			return - 1; // 邮箱已经被使用过了
		}
		$res = parent::query ( "SELECT * FROM `group` WHERE group_id = ?", $group );
		if ($res->rowCount () == 0) {
			return - 2; // groupID不合法
		}		
		return parent::update ( "UPDATE users SET nickname=?, email=?, qq=?, motto=?, group_id = ? WHERE user_id = ? LIMIT 1", $nickname, $email,$qq,$motto, $group, $userid);
	}
}