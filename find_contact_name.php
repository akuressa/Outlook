<?php
	include_once('init.php');
	check_login();
	has_privilege();
	if(isset($_POST['client_name']))
	{
		$where_person="";
		$execute_person=array();
		$where_clause="WHERE user_type=:user_type AND status=:status ";
		$execute=array(':user_type'=>'U', ':status'=>'Y');
		if($_POST['client_name']!='')
		{
			$where_person=" AND person_name LIKE :person_name ";
			$execute_person=array(':person_name'=>stripcleantohtml($_POST['client_name'])."%");
		}
		$where_clause.=$where_person;
		$execute=array_merge($execute, $execute_person);
		$find_user= find('all', USERS, 'id, status, person_name', $where_clause." LIMIT 0, 50", $execute);
		$html_data="";
		if(!empty($find_user))
		{
			foreach($find_user as $key=>$values)
			{
				$html_data.="<div class='data_row' onclick='value_send(\"".$values['person_name']."\")'>".$values['person_name']."</div>";
			}
		}
		else
		{
			$html_data.="<div class='no_data_row' onclick='value_send()'>No data found</div>";
		}
		print_r($html_data);
		exit;
	}
?>