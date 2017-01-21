<?php
require_once 'Include\DB.class.php';
class problemModel extends DB {
	public function __construct() {
		parent::__construct ();
	}
	public function get_problem($problemId) { // 从数据库选择指定ID的题目详情
		$result = parent::query ( "SELECT * FROM problem WHERE pro_id = ? LIMIT 1", $problemId );
		if ($result->rowCount () != 0) {
			return $result->fetch ( PDO::FETCH_NAMED );
		} else {
			return null;
		}
	}
	public function get_submits($problemId) {
		$result = parent::query("SELECT COUNT(*) FROM status LEFT JOIN problem ON (status.pro_id = problem.pro_id) WHERE problem.pro_id = ? AND status = 1", $problemId);
		$aSubmit = $result->fetch(PDO::FETCH_NUM)[0];
		if(!$aSubmit)
			$aSubmit = 0;
		$result = parent::query("SELECT COUNT(*) FROM status WHERE pro_id = ?", $problemId);
		$tSubmit = $result->fetch(PDO::FETCH_NUM)[0];
		if(!$tSubmit)
			$tSubmit = 0;
		
		return array(
			$aSubmit, $tSubmit
		);
	}
	
	public function get_list($listId) {
		$listId *= 50;
		$result = parent::query ( "SELECT pro_id, pro_title FROM problem LIMIT $listId, 50" );
		if ($result->rowCount () != 0) {
			$arr = array ();
			while ( $row = $result->fetch ( PDO::FETCH_NUM ) ) {
				$submits = $this->get_submits($row[0]);
				$arr [] = array (
						$row [0],
						$row [1],
						$submits[0],
						$submits[1]
				);
			}
			return $arr;
		} else {
			return null;
		}
	}
}
?>