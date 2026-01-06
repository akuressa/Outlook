<?php
	include_once('init.php');
	check_login();
	has_privilege();
	$return_string = "";
	if(isset($_GET['id']) && $_GET['id']!='')
	{
		$notice_id=substr(base64_decode($_GET['id']), 0, -5);
		$find_notice= find('first', NOTICE_BOARD, 'id, status', "WHERE id=:id", array(':id'=>$notice_id));
		if(!empty($find_notice))
		{
			if($find_notice['status'] == 'Y')
			{
				$status = 'N';
				$return_string = "no";
			}
			else
			{
				$status = 'Y';
				$return_string = "yes";
			}
			$update_status=update(NOTICE_BOARD, 'status=:status', 'WHERE id=:id', array(':status'=>$status, ':id'=>$notice_id));
			if($update_status==true)
			{
				//ok
			}
			else
			{
				$return_string="error";
			}
		}
		else
		{
			$return_string="error1";
		}
		echo $return_string;
		exit;
	}
?>