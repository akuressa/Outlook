<?php
	include_once('init.php');
	check_login();
	has_privilege();
	$return_string = "";
	if(isset($_GET['id']) && $_GET['id']!='')
	{
		$user_id=substr(base64_decode($_GET['id']), 0, -5);
		$find_user= find('first', USERS, 'id, status', "WHERE id=:id", array(':id'=>$user_id));
		if(!empty($find_user))
		{
			if($find_user['status'] == 'Y')
			{
				$status = 'N';
				$return_string = "no";
			}
			else
			{
				$status = 'Y';
				$return_string = "yes";
			}
			$update_status=update(USERS, 'status=:status', 'WHERE id=:id', array(':status'=>$status, ':id'=>$user_id));
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