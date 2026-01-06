<?php
	include_once('init.php');
	check_login();
	has_privilege();
	$appointment_where="";$appointment_execute=array();
	if($user_privilege==true)
	{
		$appointment_where=" AND user_id=:user_id ";
		$appointment_execute=array(":user_id"=>$_SESSION['logged_user_id']);
	}
	else if($branch_privilege==true)
	{
		$appointment_where=" AND branch_id=:branch_id ";
		$appointment_execute=array(":branch_id"=>$_SESSION['logged_user_id']);
	}
	if(isset($_POST['req_date']) && $_POST['req_date']!="")
	{
		$start_month=date("m", strtotime($_POST['req_date']));
		$start_year=date("Y", strtotime($_POST['req_date']));
		$execute=array(':month'=>$start_month, ':year'=>$start_year);
		$execute=array_merge($execute, $appointment_execute);
		$appointment_list = find('all', APPOINMENTS, "*", "WHERE (MONTH(start_date)= :month AND YEAR(start_date)=:year) OR (MONTH(end_date)>= :month AND (YEAR(end_date)=:year OR (YEAR(end_date)>=:year))) ".$appointment_where, $execute);
		//print_r($appointment_list);
		$tempAppointments = array() ;
		if( isset($appointment_list) && !empty($appointment_list) ){
			foreach( $appointment_list as $appo_key=>$appo_val){
				$tempAppointment  = array() ;
				$tempAppointment['id']     = $appo_val['id'] ;
				$tempAppointment['title']  = $appo_val['subject'];
				$tempAppointment['start']  = $appo_val['start_date'] ;
				$tempAppointment['end']  = $appo_val['end_date'] ; 
				$tempAppointment['color']=$appo_val['categories'];
				$tempAppointment['tip']=$appo_val['description'];
				$tempAppointment['lang']='de';
				//$tempAppointment['textColor']='black';
				$tempAppointment['url']  = 'view_edit_appointment.php?appointment_id='.base64_encode($appo_val['id'].IDHASH);
				array_push( $tempAppointments , $tempAppointment ) ;
			}
		}
		echo json_encode($tempAppointments);
		exit ;
	}
	else if(isset($_GET['selected_date']) && $_GET['selected_date']!="")
	{
		$start_month=date("m", strtotime($_GET['selected_date']));
		$start_year=date("Y", strtotime($_GET['selected_date']));
		$execute=array(':month'=>$start_month, ':year'=>$start_year);
		$execute=array_merge($execute, $appointment_execute);
		$appointment_list = find('all', APPOINMENTS, "*", "WHERE (MONTH(start_date)= :month AND YEAR(start_date)=:year) OR (MONTH(end_date)>= :month AND (YEAR(end_date)=:year OR (YEAR(end_date)>=:year))) ".$appointment_where, $execute);
		//print_r($appointment_list);
		$tempAppointments = array() ;
		if( isset($appointment_list) && !empty($appointment_list) ){
			foreach( $appointment_list as $appo_key=>$appo_val){
				$tempAppointment  = array() ;
				$tempAppointment['id']     = $appo_val['id'] ;
				$tempAppointment['title']  = $appo_val['subject'] ;
				$tempAppointment['start']  = $appo_val['start_date'] ;
				$tempAppointment['end']  = $appo_val['end_date'] ; 
				$tempAppointment['color']=$appo_val['categories'];
				$tempAppointment['tip']=$appo_val['description'];
				$tempAppointment['lang']='de';
				//$tempAppointment['textColor']='black';
				$tempAppointment['url']  = 'view_edit_appointment.php?appointment_id='.base64_encode($appo_val['id'].IDHASH);
				array_push( $tempAppointments , $tempAppointment ) ;
			}
		}
		echo json_encode($tempAppointments);
		exit ;
	}
	else
	{
		$execute=array();
		$execute=array_merge($execute, $appointment_execute);
		$appointment_list = find('all', APPOINMENTS, "*", "WHERE (MONTH(start_date)= MONTH(NOW()) AND YEAR(start_date)=YEAR(NOW())) OR (MONTH(end_date)>= MONTH(NOW()) AND (YEAR(end_date)=YEAR(NOW()) OR YEAR(end_date)>=YEAR(NOW()))) ".$appointment_where, $execute);
		//print_r($appointment_list);
		$tempAppointments = array() ;
		if( isset($appointment_list) && !empty($appointment_list) ){
			foreach( $appointment_list as $appo_key=>$appo_val){
				$tempAppointment  = array() ;
				$tempAppointment['id']     = $appo_val['id'];
				$tempAppointment['title']  = $appo_val['subject'];
				$tempAppointment['start']  = $appo_val['start_date'];
				$tempAppointment['end']  = $appo_val['end_date']; 
				$tempAppointment['color']=$appo_val['categories'];
				$tempAppointment['tip']=$appo_val['description'];
				$tempAppointment['lang']='de';
				//$tempAppointment['textColor']='black';
				$tempAppointment['url']  = 'view_edit_appointment.php?appointment_id='.base64_encode($appo_val['id'].IDHASH);
				array_push( $tempAppointments , $tempAppointment ) ;
			}
		}
		echo json_encode($tempAppointments);
		exit ;
	}
?>