<?php
	include_once('init.php');
	check_login();
	has_privilege();
	if(isset($_POST['client_name']))
	{
		$where_person="";
		$execute_person=array();
		$where_clause="WHERE client_name <> :client_name_1 ";
		$execute=array(':client_name_1'=>'');
		if($_POST['client_name']!='')
		{
			$where_person=" AND client_name LIKE :client_name ";
			$execute_person=array(':client_name'=>stripcleantohtml($_POST['client_name'])."%");
		}
		$where_clause.=$where_person;
		$execute=array_merge($execute, $execute_person);
		$find_payment= find('all', PAYMENT, 'id, client_name', $where_clause." LIMIT 0, 50", $execute);
		$html_data="";
		if(!empty($find_payment))
		{
			foreach($find_payment as $key=>$values)
			{
				$html_data.="<div class='data_row' onclick='value_send(\"".$values['client_name']."\")'>".$values['client_name']."</div>";
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