<?php
	include_once('init.php');
	check_login();
	has_privilege();
	$return_string = "";
	if(isset($_GET['id']) && $_GET['id']!='')
	{
		$product_id=substr(base64_decode($_GET['id']), 0, -5);
		$find_product= find('first', PRODUCT, 'id, status', "WHERE id=:id", array(':id'=>$product_id));
		if(!empty($find_product))
		{
			if($find_product['status'] == 'Y')
			{
				$status = 'N';
				$return_string = "no";
			}
			else
			{
				$status = 'Y';
				$return_string = "yes";
			}
			$update_status=update(PRODUCT, 'status=:status', 'WHERE id=:id', array(':status'=>$status, ':id'=>$product_id));
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