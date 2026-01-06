<?php
	include_once('init.php');
	check_login();
	has_privilege();
	$data['msg']="Wir haben ein Problem. Bitte versuche es erneut";
	if(isset($_POST['doc_id']) && $_POST['doc_id']!="")
	{
		$doc_id=$user_id=substr(base64_decode($_POST['doc_id']), 0, -5);
		$find_user_document=find('first', DOCUMENTS, '*', "WHERE id=:doc_id", array(':doc_id'=>$user_id));
		if(!empty($find_user_document))
		{
			$check_del_status=false;
			if($user_privilege==true && $find_user_document['user_id']==$_SESSION['logged_user_id'])
			{
				$check_del_status=true;
			}
			else if($branch_privilege==true)
			{
				$find_user= find('first', USERS, '*', "WHERE id=:id AND user_type=:user_type", array(':id'=>$find_user_document['user_id'], ':user_type'=>'U'));
				if(!empty($find_user))
				{
					if($find_user['parent_id']==$_SESSION['logged_user_id'])
					{
						$check_del_status=true;
					}
				}
			}
			else if($admin_privilege=true)
			{
				$check_del_status=true;
			}
			if($check_del_status==true)
			{
				$del_rcd=delete(DOCUMENTS, 'WHERE id=:id', array(':id'=>$doc_id));
				if($del_rcd==true)
				{
					if($find_user_document['modified_name']!='' && file_exists('img/user_image/'.$find_user_document['modified_name']))
					{
						unlink('img/user_image/'.$find_user_document['modified_name']);
					}
					$data['msg'] = 'success';
				}
				else
				{
					$data['msg']="Wir haben ein Problem. Bitte versuche es erneut";
				}
			}
			else
			{
				$data['msg']="Sie sind nicht berechtigt, diesen Datensatz zu löschen";
			}
		}
		else
		{
			$data['msg']="Ungültige Dokument-ID";
		}
	}
	echo json_encode($data);
	exit ;
?>