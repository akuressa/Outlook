<?php
	include_once('init.php');
	check_login();
	has_privilege();
	$branch_list = find('all', USERS, "id, branch_name", "WHERE user_type=:user_type AND status=:status ORDER BY branch_name ASC", array(':user_type'=>'B', ':status'=>'Y'));
	
	if(isset($_POST['edit_contact']))
	{
		//print_r($_POST);
		/*if(isset($_POST['parent_id']) && $_POST['parent_id']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Branch ist erforderlich.';
		}
		else */if(isset($_POST['initial']) && $_POST['initial']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Anrede erforderlich.';
		}
		else if(isset($_POST['person_name']) && $_POST['person_name']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Vollst&auml;ndiger Name erforderlich.';
		}
		else if(isset($_POST['date_of_birth']) && $_POST['date_of_birth']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Geburtsdatum erforderlich.';
		}
		/*else if(isset($_POST['email_address']) && $_POST['email_address']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Email is required.';
		}
		else if(isset($_POST['phone_no_1']) && $_POST['phone_no_1']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Telefonnummer 1 ist erforderlich.';
		}
		else if(isset($_POST['address']) && $_POST['address']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Address is required.';
		}
		else if(isset($_POST['notes']) && $_POST['notes']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Notes is required.';
		}*/
		else
		{
			$user_id=substr(base64_decode($_GET['contact_id']), 0, -5);
			$find_user_name=find('first', USERS, 'id, person_name', "WHERE person_name=:person_name AND id <> :id", array(':person_name'=>ucwords(stripcleantohtml($_POST['person_name'])), ':id'=>$user_id));
			if(empty($find_user_name))
			{
				/*if(isset($_FILES['picture']) && $_FILES['picture']['name']!='')
				{
					$explode_data = explode('.', $_FILES['picture']['name']);
					$extension = strtolower(end($explode_data));
					if(in_array($extension, $img_ext_array))
					{
						$have_pic=true;
						$flag_status=true;
						$picture_name="branch_".date("Y_m_d_h_i_s")."_.".$extension;
						$picture_value=", picture=:picture";
						$picture_execute=array(':picture'=>$picture_name);
					}
					else
					{
						$have_pic=false;
						$flag_status = false;
						$picture_value="";
						$picture_execute=array();
						$_SESSION['SET_TYPE'] = 'error';
						$_SESSION['SET_FLASH'] = 'Ung&uuml;ltige Erweiterung f&uuml;r Bild. Bitte laden Sie .jpg oder .jpeg oder .gif oder .png Bild';
					}
				}
				else
				{*/
					$have_pic=false;
					$flag_status=true;
					$picture_value="";
					$picture_execute=array();
				//}
				if($flag_status==true)
				{
					$date_for_array=explode(".", $_POST['date_of_birth']);
					//$date_for_str=$date_for_array[2]."-".$date_for_array[1]."-".$date_for_array[0];
					$value_set="parent_id=:parent_id, initial=:initial, person_name=:person_name, company=:company, job_title=:job_title, email_address=:email_address, display_as=:display_as, web_page_address=:web_page_address, ip_address=:ip_address, phone_no_1=:phone_no_1, phone_no_1_type=:phone_no_1_type, phone_no_2=:phone_no_2, phone_no_2_type=:phone_no_2_type, phone_no_3=:phone_no_3, phone_no_3_type=:phone_no_3_type, phone_no_4=:phone_no_4, phone_no_4_type=:phone_no_4_type, address_type=:address_type, address=:address, notes=:notes, status=:status, date_of_birth=:date_of_birth".$picture_value;
					$execute=array(
						':parent_id'=>stripcleantohtml($_POST['parent_id']),
						':initial'=>stripcleantohtml($_POST['initial']),
						':person_name'=>ucwords(stripcleantohtml($_POST['person_name'])),
						':company'=>stripcleantohtml($_POST['company']),
						':job_title'=>stripcleantohtml($_POST['job_title']), ':email_address'=>stripcleantohtml($_POST['email_address']),
						':display_as'=>stripcleantohtml($_POST['display_as']),
						':web_page_address'=>stripcleantohtml($_POST['web_page_address']),
						':ip_address'=>stripcleantohtml($_POST['ip_address']),
						':phone_no_1'=>stripcleantohtml($_POST['phone_no_1']),
						':phone_no_1_type'=>stripcleantohtml($_POST['phone_no_1_type']),
						':phone_no_2'=>stripcleantohtml($_POST['phone_no_2']),
						':phone_no_2_type'=>stripcleantohtml($_POST['phone_no_2_type']),
						':phone_no_3'=>stripcleantohtml($_POST['phone_no_3']),
						':phone_no_3_type'=>stripcleantohtml($_POST['phone_no_3_type']),
						':phone_no_4'=>stripcleantohtml($_POST['phone_no_4']),
						':phone_no_4_type'=>stripcleantohtml($_POST['phone_no_4_type']),
						':address_type'=>stripcleantohtml($_POST['address_type']),
						':address'=>stripcleantohtml($_POST['address']),
						':notes'=>stripcleantohtml($_POST['notes']),
						':status'=>stripcleantohtml($_POST['status']),
						':date_of_birth'=>date("Y-m-d", strtotime(stripcleantohtml($_POST['date_of_birth']))),
						':id'=>$user_id
					);
					$execute=array_merge($execute, $picture_execute);
					$update_user=update(USERS, $value_set, 'WHERE id=:id', $execute);
					if($update_user == true)
					{
						/*if($_POST['prev_img']!='' && file_exists('img/user_image/'.$_POST['prev_img']))
						{
							unlink('img/user_image/'.$_POST['prev_img']);
						}
						if($have_pic==true)
						{
							move_uploaded_file($_FILES['picture']['tmp_name'], 'img/user_image/'.$picture_name);
						}*/
						/*if(isset($_POST['treatment_protocal']) && $_POST['treatment_protocal']!="")
						{
							$add_medical_log=save(USER_MEDICAL_LOG, "user_id, medical_log, date", ":user_id, :medical_log, :date", array(':user_id'=>$user_id, ':medical_log'=>$_POST['treatment_protocal'], ':date'=>date("Y-m-d")));
						}*/
						$doc_flag=true;
						if(isset($_FILES['user_document']) && isset($_FILES['user_document']['name']) && isset($_FILES['user_document']['name'][0]) && $_FILES['user_document']['name'][0]!="")
						{
							foreach($_FILES['user_document']['name'] as $each_doc_key=>$each_doc_val)
							{
								$explode_data = explode('.', $each_doc_val);
								$extension = strtolower(end($explode_data));
								if(in_array($extension, $file_ext_array))
								{
									$doc_name=$user_id."_user_".$each_doc_key."_".rand()."_".date("Y_m_d_h_i_s")."_.".$extension;
									$doc_fields="user_id, document_name, modified_name";
									$doc_values=":user_id, :document_name, :modified_name";
									$doc_execute=array(
										':user_id'=>$user_id,
										':document_name'=>stripcleantohtml($each_doc_val),
										':modified_name'=>stripcleantohtml($doc_name)
									);
									$add_user_doc = save(DOCUMENTS, $doc_fields, $doc_values,$doc_execute);
									if($add_user_doc > 0)
									{
										move_uploaded_file($_FILES['user_document']['tmp_name'][$each_doc_key], 'img/user_image/'.$doc_name);
									}
								}
								else
								{
									$doc_flag=false;
									$_SESSION['SET_TYPE'] = 'error';
									$_SESSION['SET_FLASH'] = (isset($_SESSION['SET_FLASH']) && $_SESSION['SET_FLASH']!="" ? $_SESSION['SET_FLASH'].'<br/>' : "").'Ungültige Erweiterung für Dokument '.($each_doc_key+1).'. Bitte laden Sie .jpg, .jpeg, .gif, .png, .pdf, .doc, .docx Erweiterung nur.';
								}
							}
						}
						if($doc_flag==true)
						{
							$_SESSION['SET_TYPE'] = 'success';
							$_SESSION['SET_FLASH'] = 'Kontakt erfolgreich aktualisiert.';
							if(isset($_GET['from_top']) && $_GET['from_top']==1)
							{
								header('location:'.DOMAIN_NAME_PATH.'call-back-list.php'.(isset($_GET["top_page"]) && $_GET["top_page"]!="" ? "?top_page=".$_GET["top_page"] : ""));
							}
							else if(isset($_GET['from_bottom']) && $_GET['from_bottom']==1)
							{
								header('location:'.DOMAIN_NAME_PATH.'call-back-list.php'.(isset($_GET["bottom_page"]) && $_GET["bottom_page"]!="" ? "?bottom_page=".$_GET["bottom_page"] : ""));
							}
							else
							{
								header('location:'.DOMAIN_NAME_PATH.'listing.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
							}
							exit;
						}
					}
					else
					{
						$_SESSION['SET_TYPE'] = 'error';
						$_SESSION['SET_FLASH'] = 'Es ist ein Problem aufgetreten. Bitte versuchen Sie es sp&auml;ter..';
					}
				}
			}
			else
			{
				if($find_user_name['person_name']==$_POST['person_name'])
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Kundenname bereits vorhanden.';
				}
				else
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Es ist ein Problem aufgetreten. Bitte versuch es sp&auml;ter.';
				}
			}
		}
	}
	if(isset($_POST['btn_create_notice'])) { 
		if(isset($_POST['notice']) && $_POST['notice']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Hinweis erforderlich.';
		}
		else
		{
			$user_id=substr(base64_decode($_GET['contact_id']), 0, -5);
			$add_notice=save(USER_NOTICE, "user_id, notice, date", ":user_id, :notice, :date", array(':user_id'=>$user_id, ':notice'=>$_POST['notice'], ':date'=>date("Y-m-d")));
			if($add_notice > 0)
			{
				$_SESSION['SET_TYPE'] = 'success';
				$_SESSION['SET_FLASH'] = 'Hinweis erfolgreich hinzugef&uuml;gt.';
				header('location:'.DOMAIN_NAME_PATH.'edit_contact.php?contact_id='.$_GET['contact_id'].(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "").(isset($_GET["top_page"]) && $_GET["top_page"]!="" ? "&top_page=".$_GET["top_page"] : "").(isset($_GET["bottom_page"]) && $_GET["bottom_page"]!="" ? "&bottom_page=".$_GET["bottom_page"] : ""));
				exit;
			}
			else
			{
				$_SESSION['SET_TYPE'] = 'error';
				$_SESSION['SET_FLASH'] = 'Es ist ein Problem aufgetreten. Bitte versuch es sp&auml;ter.';
			}
		}
	}
	if(isset($_POST['edit_user_notice']))
	{
		if(isset($_POST['edit_notice']) && $_POST['edit_notice']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Hinweis erforderlich.';
		}
		else
		{
			$update_user=update(USER_NOTICE, "notice=:notice, date=:date", 'WHERE id=:id', array(':id'=>$_POST['array_key'], ':notice'=>$_POST['edit_notice'], ':date'=>date("Y-m-d")));
			if($update_user == true)
			{
				$_SESSION['SET_TYPE'] = 'success';
				$_SESSION['SET_FLASH'] = 'Hinweis erfolgreich hinzugef&uuml;gt.';
				header('location:'.DOMAIN_NAME_PATH.'edit_contact.php?contact_id='.$_GET['contact_id'].(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "").(isset($_GET["top_page"]) && $_GET["top_page"]!="" ? "&top_page=".$_GET["top_page"] : "").(isset($_GET["bottom_page"]) && $_GET["bottom_page"]!="" ? "&bottom_page=".$_GET["bottom_page"] : ""));
				exit;
			}
			else
			{
				$_SESSION['SET_TYPE'] = 'error';
				$_SESSION['SET_FLASH'] = 'Es ist ein Problem aufgetreten. Bitte versuch es sp&auml;ter.';
			}
		}
	}
	if(isset($_GET['remove_index']))
	{
		$del_rcd=delete(USER_NOTICE, 'WHERE id=:id', array(':id'=>$_GET['remove_index']));
		if($del_rcd==true)
		{
			$_SESSION['SET_TYPE'] = 'success';
			$_SESSION['SET_FLASH'] = 'Hinweis erfolgreich gel&ouml;scht.';
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Es ist ein Problem aufgetreten. Bitte versuch es sp&auml;ter.';
		}
		header('location:'.DOMAIN_NAME_PATH.'edit_contact.php?contact_id='.$_GET['contact_id'].(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "").(isset($_GET["top_page"]) && $_GET["top_page"]!="" ? "&top_page=".$_GET["top_page"] : "").(isset($_GET["bottom_page"]) && $_GET["bottom_page"]!="" ? "&bottom_page=".$_GET["bottom_page"] : ""));
		exit;
	}
	if(isset($_POST['btn_create_medical_log'])) { 
		if(isset($_POST['medical_log']) && $_POST['medical_log']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Kundenbuchungsverlauf erforderlich.';
		}
		else
		{
			$user_id=substr(base64_decode($_GET['contact_id']), 0, -5);
			$add_medical=save(USER_MEDICAL_LOG, "user_id, medical_log, date", ":user_id, :medical_log, :date", array(':user_id'=>$user_id, ':medical_log'=>$_POST['medical_log'], ':date'=>date("Y-m-d")));
			if($add_medical > 0)
			{
				$_SESSION['SET_TYPE'] = 'success';
				$_SESSION['SET_FLASH'] = 'Kundenbuchungsverlauf erfolgreich hinzugef&uuml;gt.';
				header('location:'.DOMAIN_NAME_PATH.'edit_contact.php?contact_id='.$_GET['contact_id'].(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "").(isset($_GET["top_page"]) && $_GET["top_page"]!="" ? "&top_page=".$_GET["top_page"] : "").(isset($_GET["bottom_page"]) && $_GET["bottom_page"]!="" ? "&bottom_page=".$_GET["bottom_page"] : ""));
				exit;
			}
			else
			{
				$_SESSION['SET_TYPE'] = 'error';
				$_SESSION['SET_FLASH'] = 'Es ist ein Problem aufgetreten. Bitte versuch es sp&auml;ter.';
			}
		}
	}
	if(isset($_POST['edit_user_medical_log']))
	{
		if(isset($_POST['edit_medical_log']) && $_POST['edit_medical_log']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Kundenbuchungsverlauf erforderlich.';
		}
		else
		{
			$update_user=update(USER_MEDICAL_LOG, "medical_log=:medical_log, date=:date", 'WHERE id=:id', array(':id'=>$_POST['array_key'], ':medical_log'=>$_POST['edit_medical_log'], ':date'=>date("Y-m-d")));
			if($update_user == true)
			{
				$_SESSION['SET_TYPE'] = 'success';
				$_SESSION['SET_FLASH'] = 'Kundenbuchungsverlauf erfolgreich aktualisiert.';
				header('location:'.DOMAIN_NAME_PATH.'edit_contact.php?contact_id='.$_GET['contact_id'].(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "").(isset($_GET["top_page"]) && $_GET["top_page"]!="" ? "&top_page=".$_GET["top_page"] : "").(isset($_GET["bottom_page"]) && $_GET["bottom_page"]!="" ? "&bottom_page=".$_GET["bottom_page"] : ""));
				exit;
			}
			else
			{
				$_SESSION['SET_TYPE'] = 'error';
				$_SESSION['SET_FLASH'] = 'Es ist ein Problem aufgetreten. Bitte versuch es sp&auml;ter.';
			}
		}
	}
	if(isset($_GET['medical_remove_index']))
	{
		$del_rcd=delete(USER_MEDICAL_LOG, 'WHERE id=:id', array(':id'=>$_GET['medical_remove_index']));
		if($del_rcd==true)
		{
			$_SESSION['SET_TYPE'] = 'success';
			$_SESSION['SET_FLASH'] = 'Kundenbuchungsverlauf erfolgreich gel&ouml;scht.';
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Es ist ein Problem aufgetreten. Bitte versuch es sp&auml;ter.';
		}
		header('location:'.DOMAIN_NAME_PATH.'edit_contact.php?contact_id='.$_GET['contact_id'].(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "").(isset($_GET["top_page"]) && $_GET["top_page"]!="" ? "&top_page=".$_GET["top_page"] : "").(isset($_GET["bottom_page"]) && $_GET["bottom_page"]!="" ? "&bottom_page=".$_GET["bottom_page"] : ""));
		exit;
	}
	if (isset($_POST['btn_create_call_back'])) { 
		$_POST['call_date']=date("d.m.Y H:i");
		if(isset($_POST['call_description']) && $_POST['call_description']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Beschreibung ist erforderlich.';
		}
		else if(isset($_POST['call_date']) && $_POST['call_date']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Anrufdatum ist erforderlich.';
		}
		else
		{
			$user_id=substr(base64_decode($_GET['contact_id']), 0, -5);
			$call_date_obj=date_create_from_format("d.m.Y H:i", $_POST['call_date']);
			$call_date=date_format($call_date_obj,"Y-m-d H:i:s");
			if($_POST['next_call_date']!="")
			{
				$next_call_date_obj=date_create_from_format("d.m.Y H:i", $_POST['next_call_date']);
				$next_call_date=date_format($next_call_date_obj,"Y-m-d H:i:s");
			}
			else
			{
				$next_call_date=NULL;
			}
			$add_call_note=save(CALL_BACK, "user_id, added_by, call_date, next_call_date, description, call_status", ":user_id, :added_by, :call_date, :next_call_date, :description, :call_status", array(':user_id'=>$user_id, ':added_by'=>$_SESSION['logged_user_id'], ':call_date'=>$call_date, ':next_call_date'=>$next_call_date, ':description'=>$_POST['call_description'], ':call_status'=>$_POST['call_status']));
			if($add_call_note > 0)
			{
				$_SESSION['SET_TYPE'] = 'success';
				$_SESSION['SET_FLASH'] = 'Anrufnotiz erfolgreich hinzugefügt.';
				header('location:'.DOMAIN_NAME_PATH.'edit_contact.php?contact_id='.$_GET['contact_id'].(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "").(isset($_GET["top_page"]) && $_GET["top_page"]!="" ? "&top_page=".$_GET["top_page"] : "").(isset($_GET["bottom_page"]) && $_GET["bottom_page"]!="" ? "&bottom_page=".$_GET["bottom_page"] : ""));
				exit;
			}
			else
			{
				$_SESSION['SET_TYPE'] = 'error';
				$_SESSION['SET_FLASH'] = 'Es ist ein Problem aufgetreten. Bitte versuch es sp&auml;ter.';
			}
		}
	}
	if(isset($_POST['btn_edit_call_back']))
	{
		if(isset($_POST['edit_call_description']) && $_POST['edit_call_description']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Beschreibung ist erforderlich.';
		}
		/*else if(isset($_POST['edit_call_date']) && $_POST['edit_call_date']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Anrufdatum ist erforderlich.';
		}*/
		else
		{
			/*$call_date_obj=date_create_from_format("d.m.Y H:i", $_POST['edit_call_date']);
			$call_date=date_format($call_date_obj,"Y-m-d H:i:s");*/
			if($_POST['edit_next_call_date']!="")
			{
				$next_call_date_obj=date_create_from_format("d.m.Y H:i", $_POST['edit_next_call_date']);
				$next_call_date=date_format($next_call_date_obj,"Y-m-d H:i:s");
			}
			else
			{
				$next_call_date=NULL;
			}
			$update_call_note=update(CALL_BACK, "next_call_date=:next_call_date, description=:description, call_status=:call_status", "WHERE id=:id", array(':id'=>$_POST['call_back_array_key'], ':next_call_date'=>$next_call_date, ':description'=>$_POST['edit_call_description'], ':call_status'=>$_POST['call_status']));
			if($update_call_note > 0)
			{
				$_SESSION['SET_TYPE'] = 'success';
				$_SESSION['SET_FLASH'] = 'Kundenbuchungsverlauf erfolgreich aktualisiert.';
				header('location:'.DOMAIN_NAME_PATH.'edit_contact.php?contact_id='.$_GET['contact_id'].(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "").(isset($_GET["top_page"]) && $_GET["top_page"]!="" ? "&top_page=".$_GET["top_page"] : "").(isset($_GET["bottom_page"]) && $_GET["bottom_page"]!="" ? "&bottom_page=".$_GET["bottom_page"] : ""));
				exit;
			}
			else
			{
				$_SESSION['SET_TYPE'] = 'error';
				$_SESSION['SET_FLASH'] = 'Es ist ein Problem aufgetreten. Bitte versuch es sp&auml;ter.';
			}
		}
	}
	if(isset($_GET['call_back_message_remove_index']))
	{
		$del_rcd=delete(CALL_BACK, 'WHERE id=:id', array(':id'=>$_GET['call_back_message_remove_index']));
		if($del_rcd==true)
		{
			$_SESSION['SET_TYPE'] = 'success';
			$_SESSION['SET_FLASH'] = 'Anrufnotiz erfolgreich gelöscht.';
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Es ist ein Problem aufgetreten. Bitte versuch es sp&auml;ter.';
		}
		header('location:'.DOMAIN_NAME_PATH.'edit_contact.php?contact_id='.$_GET['contact_id'].(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "").(isset($_GET["top_page"]) && $_GET["top_page"]!="" ? "&top_page=".$_GET["top_page"] : "").(isset($_GET["bottom_page"]) && $_GET["bottom_page"]!="" ? "&bottom_page=".$_GET["bottom_page"] : ""));
		exit;
	}
	if(isset($_GET['contact_id']) && $_GET['contact_id']!='')
	{
		$branch_where="";$branch_execute=array();
		if($branch_privilege==true)
		{
			$branch_where=" AND parent_id=:parent_id";
			$branch_execute=array(":parent_id"=>$_SESSION['logged_user_id']);
		}
		$user_id=substr(base64_decode($_GET['contact_id']), 0, -5);
		$execute=array(':id'=>$user_id, ':user_type'=>'U');
		$execute=array_merge($execute, $branch_execute);
		$find_user= find('first', USERS, '*', "WHERE id=:id AND user_type=:user_type".$branch_where, $execute);
		if(!empty($find_user))
		{
			//do nothing
			$find_user_notice=find('all', USER_NOTICE, '*', "WHERE user_id=:user_id ORDER BY date DESC, id DESC", array(':user_id'=>$user_id));
			$find_user_medical_log=find('all', USER_MEDICAL_LOG, '*', "WHERE user_id=:user_id ORDER BY date DESC, id DESC", array(':user_id'=>$user_id));
			$find_user_documents=find('all', DOCUMENTS, '*', "WHERE user_id=:user_id ORDER BY id ASC", array(':user_id'=>$user_id));
			$find_user_call_back=find('all', CALL_BACK, '*', "WHERE user_id=:user_id ORDER BY id ASC", array(':user_id'=>$user_id));
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Ung&uuml;ltige Benutzer-id.';
			if(isset($_GET['from_top']) && $_GET['from_top']==1)
			{
				header('location:'.DOMAIN_NAME_PATH.'call-back-list.php'.(isset($_GET["top_page"]) && $_GET["top_page"]!="" ? "?top_page=".$_GET["top_page"] : ""));
			}
			else if(isset($_GET['from_bottom']) && $_GET['from_bottom']==1)
			{
				header('location:'.DOMAIN_NAME_PATH.'call-back-list.php'.(isset($_GET["bottom_page"]) && $_GET["bottom_page"]!="" ? "?bottom_page=".$_GET["bottom_page"] : ""));
			}
			else
			{
				header('location:'.DOMAIN_NAME_PATH.'listing.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			}
			exit;
		}
	}
	else
	{
		$_SESSION['SET_TYPE'] = 'error';
		$_SESSION['SET_FLASH'] = 'Kontakt id fehlenden.';
		if(isset($_GET['from_top']) && $_GET['from_top']==1)
		{
			header('location:'.DOMAIN_NAME_PATH.'call-back-list.php'.(isset($_GET["top_page"]) && $_GET["top_page"]!="" ? "?top_page=".$_GET["top_page"] : ""));
		}
		else if(isset($_GET['from_bottom']) && $_GET['from_bottom']==1)
		{
			header('location:'.DOMAIN_NAME_PATH.'call-back-list.php'.(isset($_GET["bottom_page"]) && $_GET["bottom_page"]!="" ? "?bottom_page=".$_GET["bottom_page"] : ""));
		}
		else
		{
			header('location:'.DOMAIN_NAME_PATH.'listing.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
		}
		exit;
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('includes/header.php');?>
	<script type="text/javascript">
	<!--	
		$(function(){
			$("#edit_user, #edit_notice_form, #add_notice, #add_medical_log, #edit_medical_log_form, #add_call_back_message_form, #edit_add_call_back_message_form").validationEngine();
			$('[data-toggle="tooltip"]').tooltip();
			$('#date_of_birth').datetimepicker({
				timepicker:false,
				format:'d.m.Y',
				//formatDate:'d.m.Y',
				//minDate:'-1970/01/01', //yesterday is minimum date(for today use 0 or -1970/01/01)
				maxDate:'+1970/01/01' // and tommorow is maximum date calendar
			});
			$('#next_call_date, #edit_next_call_date').datetimepicker({
				timepicker:true,
				format:'d.m.Y H:i',
				//formatDate:'d.m.Y',
				minDate:'-1970/01/01', //yesterday is minimum date(for today use 0 or -1970/01/01)
				//maxDate:'+1970/01/01' // and tommorow is maximum date calendar
			});
			$('#call_date, #edit_call_date').datetimepicker({
				timepicker:true,
				format:'d.m.Y H:i',
				//formatDate:'d.m.Y',
				//minDate:'-1970/01/01', //yesterday is minimum date(for today use 0 or -1970/01/01)
				maxDate:'+1970/01/01' // and tommorow is maximum date calendar
			});
		});
		function delete_notice_index(req_index)
		{
			if(confirm('Sind Sie sicher, dass Sie diesen Datensatz gel\xF6scht werden soll?'))
			{
				window.location.href = '<?php echo(DOMAIN_NAME_PATH)?>edit_contact.php?contact_id=<?php echo $_GET["contact_id"]?><?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>&remove_index='+req_index;
			}
		}
		function delete_medical_index(req_index)
		{
			if(confirm('Sind Sie sicher, dass Sie diesen Datensatz gel\xF6scht werden soll?'))
			{
				window.location.href = '<?php echo(DOMAIN_NAME_PATH)?>edit_contact.php?contact_id=<?php echo $_GET["contact_id"]?><?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>&medical_remove_index='+req_index;
			}
		}
		function delete_call_back_message_index(req_index)
		{
			if(confirm('Sind Sie sicher, dass Sie diesen Datensatz gel\xF6scht werden soll?'))
			{
				window.location.href = '<?php echo(DOMAIN_NAME_PATH)?>edit_contact.php?contact_id=<?php echo $_GET["contact_id"]?><?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>&call_back_message_remove_index='+req_index;
			}
		}
		function addNewDoc()
		{
			var new_row_html='';
			new_row_html+='<div style="margin-bottom:5px">';
				new_row_html+='<input class="" type="file" name="user_document[]" style="display: inline-block;"/>';
				new_row_html+='<a href="javascript:void(0)" onclick="deleteDocRow($(this));" title="löschen"><span class="glyphicon glyphicon-trash"></span></a>';
			new_row_html+='</div>';
			$("#browse_doc_link_div").append(new_row_html);
		}
		function addDelete(cur)
		{
			cur.parent("div").append('<a href="javascript:void(0)" onclick="deleteDocRow($(this));" title="löschen"><span class="glyphicon glyphicon-trash"></span></a>');
		}
		function deleteDocRow(cur)
		{
			cur.parent("div").remove();
		}
		function deleteOldDocRow(cur, doc_id)
		{
			if(confirm('Sind Sie sicher, dass Sie diesen Datensatz gel\xF6scht werden soll?'))
			{
				$.ajax({
					url: '<?php echo(DOMAIN_NAME_PATH)?>delete_user_doc.php',
					dataType: 'json',
					type: 'POST',
					data:{
						doc_id: doc_id
					},
					beforeSend: function() {
						$("#loading_img_bg").show();
					},
					success: function(response){
						//console.log(response);
						$("#loading_img_bg").hide();
						if(response.msg=="success")
						{
							showSuccess('Dokument erfolgreich gel\xF6scht');
							cur.parent("div").remove();
						}
						else
						{
							showError(response.msg);
						}
					},
					error: function(){
						$("#loading_img_bg").hide();
						showError('Wir haben ein Problem. Bitte versuche es erneut');
					}
				});
			}
		}
	//-->
	</script>
</head>
<body>
	<div id="loading_img_bg" ><img src="img/ajax-loader.gif"/></div>
    <div class="container"> 
		<?php include_once('includes/navigation.php');?>
		<div class="col-md-8">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Kontaktdaten bearbeiten</h3>
				</div>
				<div class=" content">
					<div class="col-md-4">
						<div class="left_notice_content">
							<div class="form-group" style="padding:15px 1px;">
							  <a href = "javascript:void(0)" onClick="$('#edit_message_main_div').hide();$('#message_main_div').slideToggle('slow');" style="float:left;font-size:12px;margin: 5px;"><button class="button" name = "btn_create"><b>Kurze Nachricht</b></button></a>
							  <a href = "javascript:void(0)" onClick="$('#edit_medical_main_div').hide();$('#medical_main_div').slideToggle('slow');" style="float:right;font-size:12px;margin: 5px;"><button class="button" name = "btn_create"><b>Kundenbuchungsverlauf</b></button></a>
							  <div class="clearfix"></div>
							</div>
							<div class="panel panel-primary">
								<div class="panel-heading">
									<h3 class="panel-title">Hinweis</h3>
								</div>
							</div>
							<table id="myTable" class="table tablesorter">
								<thead class="add_new">
									<tr>
										<th>Hinweis</th>
										<!-- <th>Date</th> -->
										<?php
											if($admin_privilege==true)
											{
										?>
										<th style="text-align:center">Aktion</th>
										<?php
											}
										?>
									</tr>
								</thead>
								<tbody> 
									 <?php
										if(isset($find_user_notice) && !empty($find_user_notice))
										{
											foreach($find_user_notice as $key=>$value)
											{
									?>
									 <tr>
										<td><?php echo change_date_format($value['date'])."<br/><a href='javascript:void(0)' data-toggle='tooltip' title='".$value['notice']."'>".substr($value['notice'], 0, 30)."</a>";?></td>
										<!-- <td><?php echo change_date_format($value['date']);?></td> -->
										<?php
											if($admin_privilege==true)
											{
										?>
										<td style="text-align:center">
											<a href="javascript:void(0)" onClick="$('#message_main_div, #edit_message_main_div').hide();$('#edit_message_main_div').show('slow');$('#edit_notice').val('<?php echo $value['notice'];?>');$('#array_key').val('<?php echo $value['id'];?>');">
												<span class="glyphicon glyphicon-pencil"></span>
											</a>&nbsp;&nbsp;&nbsp;&nbsp;
											<a href="javascript:void(0)" onclick="delete_notice_index('<?php echo $value['id'];?>')">
												<span class="glyphicon glyphicon-trash"></span>
											</a>
										</td>
										<?php
											}
										?>
									</tr>
									<?php
											}
										}
										else
										{
									?>
											<tr>
												<td colspan="<?php echo($admin_privilege==true ? "3" : "2");?>" class="no_record_cls">keinen Eintrag gefunden</td>
											</tr>
									<?php
										}
									?>
								</tbody>
							</table>
							<!-- <div class="form-group" style="padding:3px;text-align:right;">
							  <a href = "javascript:void(0)" onclick="$('#edit_message_main_div').hide();$('#message_main_div').slideToggle('slow');"><button class="button" name = "btn_create"><b>Add New Notice</b></button></a>
							</div> -->
							<div class="message_main_div" id="message_main_div">
								<div class="col-md-12">
									<form id="add_notice" name="add_notice" method="post" action="">
										<div class="form-group" style="padding:6px;text-align:left;">
											<font color = "red">*</font> Pflichtfeld
										</div>
										<div class="form-group" style="padding:6px">
										  <label><font color = "red">*</font> Hinweis:</label>
										  <textarea name="notice" class="form-control validate[required]" Placeholder="Hinweis" data-errormessage-value-missing="Hinweis ist erforderlich" id="notice"><?php echo(isset($_POST['notice']) && $_POST['notice']!="" ? $_POST['notice'] : "");?></textarea>
										</div>
										<div class="form-group" style="padding:3px;text-align:right;">
										  <button class="button" name = "btn_create_notice"><b>Kurze Nachricht hinzufuegen</b></button>
										</div>
									</form>
								</div>
							</div>
							<div class="message_main_div" id="edit_message_main_div">
								<div class="col-md-12">
									<form id="edit_notice_form" name="edit_notice_form" method="post" action="">
										<div class="form-group" style="padding:6px;text-align:left;">
											<font color = "red">*</font>Pflichtfeld
										</div>
										<div class="form-group" style="padding:6px">
										  <label><font color = "red">*</font> Hinweis:</label>
										  <textarea class="form-control validate[required]" Placeholder="Hinweis" id="edit_notice" name="edit_notice" data-errormessage-value-missing="Hinweis ist erforderlich"><?php echo(isset($_POST['edit_notice']) && $_POST['edit_notice']!="" ? $_POST['edit_notice'] : "");?></textarea>
										  <input type="hidden" name="array_key" id="array_key" value=""/>
										</div>
										<div class="form-group" style="padding:3px;text-align:right;">
										  <button class="button" name = "edit_user_notice"><b>Bearbeiten und Datenschutz</b></button>
										</div>
									</form>
								</div>
							</div>
						</div>
						<div class="clearfix"></div>
						<div class="left_medical_log_content" style="margin-top:10px;">
							<div class="panel panel-primary">
								<div class="panel-heading">
									<h3 class="panel-title">Kundenbuchungsverlauf</h3>
								</div>
							</div>
							<div style="max-height:250px;overflow:auto;">
								<table id="myTable" class="table tablesorter">
									<thead class="add_new">
										<tr>
											<th>Kundenbuchungsverlauf</th>
											<!-- <th>Date</th> -->
											<?php
												if($admin_privilege==true)
												{
											?>
											<th style="text-align:center">Aktion</th>
											<?php
												}
											?>
										</tr>
									</thead>
								
									<tbody> 
										<?php
											if(isset($find_user_medical_log) && !empty($find_user_medical_log))
											{
												foreach($find_user_medical_log as $key=>$value)
												{
										?>
										 <tr>
											<td ><?php echo change_date_format($value['date'])."<br/><a href='javascript:void(0)' data-toggle='tooltip' title='".$value['medical_log']."'>".substr($value['medical_log'], 0, 30)."</a>";?></td>
											<!-- <td><?php echo change_date_format($value['date']);?></td> -->
											<?php
												if($admin_privilege==true)
												{
											?>
											<td style="text-align:center">
												<a href="javascript:void(0)" onClick="$('#medical_main_div, #edit_medical_main_div').hide();$('#edit_medical_main_div').show('slow');$('#edit_medical_log').val('<?php echo $value['medical_log'];?>');$('#medical_array_key').val('<?php echo $value['id'];?>');">
													<span class="glyphicon glyphicon-pencil"></span>
												</a>&nbsp;&nbsp;&nbsp;&nbsp;
												<a href="javascript:void(0)" onclick="delete_medical_index('<?php echo $value['id'];?>')">
													<span class="glyphicon glyphicon-trash"></span>
												</a>
											</td>
											<?php
												}
											?>
										</tr>
										<?php
												}
											}
											else
											{
										?>
												<tr>
													<td colspan="<?php echo($admin_privilege==true ? "3" : "2");?>" class="no_record_cls">keinen Eintrag gefunden</td>
												</tr>
										<?php
											}
										?>
									</tbody>
								</table>
							</div>
							<!-- <div class="form-group" style="padding:3px;text-align:right;">
							  <a href = "javascript:void(0)" onClick="$('#edit_medical_main_div').hide();$('#medical_main_div').slideToggle('slow');"><button class="button" name = "btn_create"><b>Add New Treatement Protocal</b></button></a>
							</div> -->
							<div class="message_main_div" id="medical_main_div">
								<div class="col-md-12">
									<form id="add_medical_log" name="add_medical_log" method="post" action="">
										<div class="form-group" style="padding:6px;text-align:left;">
											<font color = "red">*</font>Pflichtfeld.
										</div>
										<div class="form-group" style="padding:6px">
										  <label><font color = "red">*</font> Kundenbuchungsverlauf:</label>
										  <textarea name="medical_log" class="form-control validate[required]" Placeholder="Kundenbuchungsverlauf" data-errormessage-value-missing="Kundenbuchungsverlauf ist erforderlich" id="medical_log"><?php echo(isset($_POST['medical_log']) && $_POST['medical_log']!="" ? $_POST['medical_log'] : "");?></textarea>
										</div>
										<div class="form-group" style="padding:3px;text-align:right;">
										  <button class="button" name = "btn_create_medical_log"><b>F&uuml;gen Kundenbuchungsverlauf</b></button>
										</div>
									</form>
								</div>
							</div>

							<div class="message_main_div" id="edit_medical_main_div">
								<div class="col-md-12">
									<form id="edit_medical_log_form" name="edit_medical_log_form" method="post" action="">
										<div class="form-group" style="padding:6px;text-align:left;">
											<font color = "red">*</font>Pflichtfeld.
										</div>
										<div class="form-group" style="padding:6px">
										  <label><font color = "red">*</font> Kundenbuchungsverlauf:</label>
										  <textarea class="form-control validate[required]" Placeholder="Kundenbuchungsverlauf" id="edit_medical_log" name="edit_medical_log" data-errormessage-value-missing="Kundenbuchungsverlauf ist erforderlich"><?php echo(isset($_POST['edit_medical_log']) && $_POST['edit_medical_log']!="" ? $_POST['edit_medical_log'] : "");?></textarea>
										  <input type="hidden" name="array_key" id="medical_array_key" value=""/>
										</div>
										<div class="form-group" style="padding:3px;text-align:right;">
										  <button class="button" name = "edit_user_medical_log"><b>Bearbeiten Behandlung Protocal</b></button>
										</div>
									</form>
								</div>
							</div>
						</div>
						<div class="left_call_back_content">
							<div class="form-group" style="padding:15px 1px;">
							  <a href = "javascript:void(0)" onClick="$('#edit_call_back_message_main_div').hide();$('#call_back_message_main_div').slideToggle('slow');" style="float:left;font-size:12px;margin: 5px;"><button class="button" name = "btn_create"><b>Anrufinformationen hinzufuegen</b></button></a>
							  <div class="clearfix"></div>
							</div>
							<div class="panel panel-primary">
								<div class="panel-heading">
									<h3 class="panel-title">Anrufsnotiz</h3>
								</div>
							</div>
							<div style="max-height:250px;overflow:auto;">
								<table id="myTable" class="table tablesorter">
									<thead class="add_new">
										<tr>
											<th>Anrufnotiz</th>
											<?php
												if($admin_privilege==true)
												{
											?>
											<th style="text-align:center">Aktion</th>
											<?php
												}
											?>
										</tr>
									</thead>
								
									<tbody> 
										<?php
											if(isset($find_user_call_back) && !empty($find_user_call_back))
											{
												foreach($find_user_call_back as $call_key=>$call_value)
												{
													$call_date_time=change_date_time_format($call_value['call_date'], "Y-m-d H:i:s");
													$next_call_date_time=change_date_time_format($call_value['next_call_date'], "Y-m-d H:i:s");
													$call_status=$call_value['call_status'];
										?>
										 <tr>
											<td>
												<strong>Anrufdatum:</strong> <?php echo $call_date_time;?><br/>
												<strong>Rückrufdatum:</strong> <?php echo $next_call_date_time;?><br/>
												<strong>Status:</strong> <?php echo $call_status_arr[$call_status];?><br/>
												<a href='javascript:void(0)' data-toggle='tooltip' title='<?php echo $call_value['description'];?>'><?php echo nl2br(substr($call_value['description'], 0, 30));?></a>
											</td>
											<?php
												if($admin_privilege==true)
												{
											?>
											<td style="text-align:center">
												<a href="javascript:void(0)" data-des="<?php echo $call_value['description'];?>" onClick="$('#call_back_message_main_div, #edit_call_back_message_main_div').hide();$('#edit_call_back_message_main_div').show('slow');$('#edit_call_description').val($(this).attr('data-des'));$('#edit_call_date').val('<?php echo $call_date_time!="N/A" ? $call_date_time : "";?>');$('#edit_next_call_date').val('<?php echo $next_call_date_time!="N/A" ? $next_call_date_time : "";?>');$('#edit_call_status').val('<?php echo $call_status;?>');$('#call_back_array_key').val('<?php echo $call_value['id'];?>');">
													<span class="glyphicon glyphicon-pencil"></span>
												</a>&nbsp;&nbsp;&nbsp;&nbsp;
												<a href="javascript:void(0)" onclick="delete_call_back_message_index('<?php echo $call_value['id'];?>')">
													<span class="glyphicon glyphicon-trash"></span>
												</a>
											</td>
											<?php
												}
											?>
										</tr>
										<?php
												}
											}
											else
											{
										?>
												<tr>
													<td colspan="<?php echo($admin_privilege==true ? "2" : "1");?>" class="no_record_cls">Kein Eintrag gefunden</td>
												</tr>
										<?php
											}
										?>
									</tbody>
								</table>
							</div>
							<div class="message_main_div" id="call_back_message_main_div">
								<div class="col-md-12">
									<form id="add_call_back_message_form" name="add_call_back_message_form" method="post" action="">
										<div class="form-group" style="padding:6px;text-align:left;">
											<font color = "red">*</font>Pflichtfeld
										</div>
										<div class="form-group" style="padding:6px">
										  <label><font color = "red">*</font> Beschreibung:</label>
										  <textarea name="call_description" class="form-control validate[required]" Placeholder="Beschreibung" data-errormessage-value-missing="Beschreibung ist erforderlich" id="call_description"><?php echo(isset($_POST['call_description']) && $_POST['call_description']!="" ? $_POST['call_description'] : "");?></textarea>
										</div>
										<div class="form-group" style="padding:6px;display:none;">
										  <label><font color = "red">*</font> Anrufdatum:</label>
										  <input class="form-control" placeholder="Anrufdatum" type="text" id="call_date" name="call_date" data-errormessage-value-missing="Anrufdatum ist erforderlich" value="" />
										</div>
										<div class="form-group" style="padding:6px">
										  <label>Rückrufdatum:</label>
										  <input class="form-control" placeholder="Nächstes Rückrufdatum" type="text" id="next_call_date" name="next_call_date" data-errormessage-value-missing="Nächstes Rückrufdatum ist erforderlich" value="" />
										</div>
										<div class="form-group" style="padding:6px">
										  <label> Status:</label>
										  <select class="form-control " name="call_status" id="call_status" data-errormessage-value-missing="Status ist erforderlich">
											<option value = "N">Normal</option>
											<option value = "U">Dringend</option>
										  </select>
										</div>
										<div class="form-group" style="padding:3px;text-align:right;">
										  <button class="button" name = "btn_create_call_back"><b>Anrufdetails hinzufügen</b></button>
										</div>
									</form>
								</div>
							</div>
							<div class="message_main_div" id="edit_call_back_message_main_div">
								<div class="col-md-12">
									<form id="edit_add_call_back_message_form" name="edit_add_call_back_message_form" method="post" action="">
										<div class="form-group" style="padding:6px;text-align:left;">
											<font color = "red">*</font>Pflichtfeld
										</div>
										<input type="hidden" name="call_back_array_key" id="call_back_array_key" value=""/>
										<div class="form-group" style="padding:6px">
										  <label><font color = "red">*</font> Beschreibung:</label>
										  <textarea name="edit_call_description" class="form-control validate[required]" Placeholder="Beschreibung" data-errormessage-value-missing="Beschreibung ist erforderlich" id="edit_call_description"><?php echo(isset($_POST['edit_call_description']) && $_POST['edit_call_description']!="" ? $_POST['edit_call_description'] : "");?></textarea>
										</div>
										<div class="form-group" style="padding:6px;display:none;">
										  <label><font color = "red">*</font> Anrufdatum:</label>
										  <input class="form-control" placeholder="Anrufdatum" type="text" id="edit_call_date" name="edit_call_date" data-errormessage-value-missing="Anrufdatum ist erforderlich" value="" />
										</div>
										<div class="form-group" style="padding:6px">
										  <label>Rückrufdatum:</label>
										  <input class="form-control" placeholder="Nächstes Rückrufdatum" type="text" id="edit_next_call_date" name="edit_next_call_date" data-errormessage-value-missing="Nächstes Rückrufdatum ist erforderlich" value="" />
										</div>
										<div class="form-group" style="padding:6px">
										  <label> Status:</label>
										  <select class="form-control " name="call_status" id="edit_call_status" data-errormessage-value-missing="Status ist erforderlich">
											<option value = "N">Normal</option>
											<option value = "U">Dringend</option>
										  </select>
										</div>
										<div class="form-group" style="padding:3px;text-align:right;">
										  <button class="button" name = "btn_edit_call_back"><b>Anrufdetails bearbeiten</b></button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-8">
						<form id="edit_user" name="edit_user" method="post" enctype="multipart/form-data" action="">
							<div class="form-group" style="padding:6px;text-align:left;">
								<font color = "red">*</font>Pflichtfeld
							</div>
							<div class="form-group" style="padding:6px">
								<div class="col-md-4">
							  		<label><!-- <font color = "red">*</font> --> W&auml;hlen Sie die Filiale aus:</label>
								</div>
								<!-- <input type="hidden" name="prev_img" id="prev_img" value="<?php echo((isset($find_user['picture']) && $find_user['picture']!="" ? $find_user['picture'] : ""));?>"/> -->
								<div class="col-md-8">
								  <select class="form-control" name="parent_id" id="parent_id"  tabindex="1" data-errormessage-value-missing="Bezeichnung der Filiale erforderlich">
									<option value = "">Filiale ausw&auml;hlen</option>
									<?php
										if(!empty($branch_list))
										{
											foreach($branch_list as $branch_key=>$branch_value)
											{
									?>
									<option value = "<?php echo $branch_value['id'];?>" <?php echo(isset($_POST['parent_id']) && $_POST['parent_id']==$branch_value['id'] ? "selected='selected'" : (isset($find_user['parent_id']) && $find_user['parent_id']==$branch_value['id'] ? "selected='selected'" : ""));?>><?php echo $branch_value['branch_name'];?></option>
									<?php
											}
										}
									?>
								  </select>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:6px">
								<div class="col-md-4">
							  		<label><font color = "red">*</font>Anrede:</label>
								</div>
								<div class="col-md-8">
								  <select class="form-control validate[required]" name="initial" id="initial"  tabindex="2" data-errormessage-value-missing="Anrede erforderlich">
										<option value = "">Anrede ausw&auml;hlen</option>
										<?php
											if(!empty($initial_arr))
											{
												foreach($initial_arr as $initial_key=>$initial_value)
												{
										?>
										<option value = "<?php echo $initial_key;?>" <?php echo(isset($_POST['initial']) && $_POST['initial']==$initial_key ? "selected='selected'" : (isset($find_user['initial']) && $find_user['initial']==$initial_key ? "selected='selected'" : ""));?>><?php echo $initial_value;?></option>
										<?php
												}
											}
										?>
									
								  </select>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:6px">
								<div class="col-md-4">
									<label><font color = "red">*</font> Vorname, Nachname:</label>
								</div>
								<div class="col-md-8">
									  <input class="form-control validate[required]" placeholder="Name of the authorized person" type="text" id="person_name" name="person_name" maxlength="200"  data-errormessage-value-missing="Vollst&auml;ndiger name ist erforderlich" tabindex="3" value="<?php echo((isset($_POST['person_name']) && $_POST['person_name']!='') ? ucwords($_POST['person_name']) : (isset($find_user['person_name']) && $find_user['person_name']!="" ? ucwords($find_user['person_name']) : ""));?>"/>

								</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:6px">
								<div class="col-md-4">
									<label><font color = "red">*</font>Geburtsdatum:</label>
								</div>
								<div class="col-md-8">
								  <input class="form-control validate[required]" placeholder="Geburtsdatum" type="text" id="date_of_birth" name="date_of_birth" maxlength="12"  data-errormessage-value-missing="Geburtsdatum ist erforderlich" tabindex="4" value="<?php echo((isset($_POST['date_of_birth']) && $_POST['date_of_birth']!='') ? $_POST['date_of_birth'] : (isset($find_user['date_of_birth']) && $find_user['date_of_birth']!="" ? change_date_format($find_user['date_of_birth']) : ""));?>" readonly=readonly/>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:6px;display:none;">
								<div class="col-md-4">
									<label>Firma:</label>
								</div>
								<div class="col-md-8">
									<input class="form-control" placeholder="Company" type="hidden" id="company" name="company" maxlength="200" value="<?php echo((isset($_POST['company']) && $_POST['company']!='') ? $_POST['company'] : (isset($find_user['company']) && $find_user['company']!="" ? $find_user['company'] : ""));?>"/>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:6px;display:none;">
								<div class="col-md-4">
									<label>Beruf-Title:</label>
								</div>
								<div class="col-md-8">
									<input class="form-control" placeholder="Job Title" type="hidden" id="job_title" name="job_title" maxlength="150"  value="<?php echo((isset($_POST['job_title']) && $_POST['job_title']!='') ? $_POST['job_title'] : (isset($find_user['job_title']) && $find_user['job_title']!="" ? $find_user['job_title'] : ""));?>"/>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:6px;display:none;">
								<div class="col-md-4">
									<label>Bild:</label>
								</div>
								<div class="col-md-8">
									<input class="form-control" type="file" id="picture" name="picture" tabindex="5"/>
								<?php
									if(isset($find_user['picture']) && $find_user['picture']!='' && file_exists('img/user_image/'.$find_user['picture']))
									{
								?>
									<img src="<?php echo DOMAIN_NAME_PATH.'img/user_image/'.$find_user['picture'];?>" alt="Profile Picture" style="width:200px;margin-top:5px;"/>
								<?php
									}
								?>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:6px">
								<div class="col-md-4">
									<label>Email:</label>
								</div>
								<div class="col-md-8">
									<input class="form-control validate[custom[email]]" placeholder="Email" type="text" name="email_address" id="email_address" value="<?php echo((isset($_POST['email_address']) && $_POST['email_address']!='') ? $_POST['email_address'] : (isset($find_user['email_address']) && $find_user['email_address']!="" ? $find_user['email_address'] : ""));?>" data-errormessage-value-missing="E-Mail ist erforderlich" maxlength="255" tabindex="6"/>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:6px;display:none;">
								<div class="col-md-4">
									<label>Display as:</label>
								</div>
								<div class="col-md-8">
									<input class="form-control" placeholder="Display as" type="hidden" name="display_as" id="display_as" value="<?php echo((isset($_POST['display_as']) && $_POST['display_as']!='') ? $_POST['display_as'] : (isset($find_user['display_as']) && $find_user['display_as']!="" ? $find_user['display_as'] : ""));?>" maxlength="150"/>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:6px;display:none;">
								<div class="col-md-4">
									<label>Web-Adresse:</label>
								</div>
								<div class="col-md-8">
									<input class="form-control" placeholder="Web page Address" type="text" name="web_page_address" id="web_page_address" value="<?php echo((isset($_POST['web_page_address']) && $_POST['web_page_address']!='') ? $_POST['web_page_address'] : (isset($find_user['web_page_address']) && $find_user['web_page_address']="" ? $find_user['web_page_address'] : ""));?>"/>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:6px;display:none;">
								<div class="col-md-4">
									<label>IM Addresse:</label>
								</div>
								<div class="col-md-8">
									<input class="form-control" placeholder="IM Address" type="hidden" name="ip_address" id="ip_address" value="<?php echo((isset($_POST['ip_address']) && $_POST['ip_address']!='') ? $_POST['ip_address'] : (isset($find_user['ip_address']) && $find_user['ip_address']!="" ? $find_user['ip_address'] : ""));?>" maxlength="100"/>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:6px">
								<div class="col-md-4">
									  <label><!-- <font color = "red">*</font>  -->Telefonnummer 1:</label>
								</div>
								<div class="col-md-8">
									<div class="each_phone_row">
										<div class="new_phone_cls" style="display:none;">
											  <select class="form-control validate[required]" name = "phone_no_1_type" id="" id="phone_no_1_type">
												<?php
													foreach($option_array as $opt_key => $opt_val)
													{
												?>
														<option value = "<?php echo $opt_val;?>" <?php echo(isset($_POST['phone_no_1_type']) && $_POST['phone_no_1_type']==$opt_val ? "selected='selected'" : (isset($find_user['phone_no_1_type']) && $find_user['phone_no_1_type']==$opt_val ? "selected='selected'" : ""));?>><?php echo $opt_val;?></option>
												<?php
													}
												?>
											  </select>
										</div>
										<div>
											<input class="form-control" placeholder="Telefonnummer 1" type="text" name="phone_no_1"  tabindex="7" value="<?php echo(isset($_POST['phone_no_1']) && $_POST['phone_no_1']!="" ? $_POST['phone_no_1'] : (isset($find_user['phone_no_1']) && $find_user['phone_no_1']!="" ? $find_user['phone_no_1'] : ""));?>" id="phone_no_1" maxlength="20" data-errormessage-value-missing="Telefonnummer 1 ist erforderlich"/>
										 </div>
										 <div class="clearfix"></div>
									</div>
									<div class="each_phone_row" style="display:none;">
										<div class="new_phone_cls">
										  <select class="form-control" name = "phone_no_2_type" id=""  tabindex="12" id="phone_no_2_type">
											<?php
												foreach($option_array as $opt_key => $opt_val)
												{
											?>
													<option value = "<?php echo $opt_val;?>" <?php echo(isset($_POST['phone_no_2_type']) && $_POST['phone_no_2_type']==$opt_val ? "selected='selected'" : (isset($find_user['phone_no_2_type']) && $find_user['phone_no_2_type']==$opt_val ? "selected='selected'" : ""));?>><?php echo $opt_val;?></option>
											<?php
												}
											?>
										  </select>
										</div>
									  <div class="clearfix"></div>
									</div>
									<div class="each_phone_row" style="display:none;">
										<div class="new_phone_cls">
										  <select class="form-control" name = "phone_no_3_type" id=""  tabindex="14" id="phone_no_3_type">
											<?php
												foreach($option_array as $opt_key => $opt_val)
												{
											?>
													<option value = "<?php echo $opt_val;?>" <?php echo(isset($_POST['phone_no_3_type']) && $_POST['phone_no_3_type']==$opt_val ? "selected='selected'" : (isset($find_user['phone_no_3_type']) && $find_user['phone_no_3_type']==$opt_val ? "selected='selected'" : ""));?>><?php echo $opt_val;?></option>
											<?php
												}
											?>
										  </select>
										</div>
										<div class="new_phone_cls">
											<input class="form-control" placeholder="Phone number" type="text" name="phone_no_3"  tabindex="15" value="<?php echo(isset($_POST['phone_no_3']) && $_POST['phone_no_3']!="" ? $_POST['phone_no_3'] : (isset($find_user['phone_no_3']) && $find_user['phone_no_3']!="" ? $find_user['phone_no_3'] : ""));?>" id="phone_no_3" maxlength="20"/>
										</div>
									  <div class="clearfix"></div>
									</div>
									<div class="each_phone_row" style="display:none;">
										<div class="new_phone_cls">
										  <select class="form-control" name = "phone_no_4_type" id=""  tabindex="16" id="phone_no_4_type">
											<?php
												foreach($option_array as $opt_key => $opt_val)
												{
											?>
													<option value = "<?php echo $opt_val;?>" <?php echo(isset($_POST['phone_no_4_type']) && $_POST['phone_no_4_type']==$opt_val ? "selected='selected'" : (isset($find_user['phone_no_4_type']) && $find_user['phone_no_4_type']==$opt_val ? "selected='selected'" : ""));?>><?php echo $opt_val;?></option>
											<?php
												}
											?>
										  </select>
										</div>
										<div class="new_phone_cls">
											<input class="form-control" placeholder="Phone number" type="text" name="phone_no_4"  tabindex="17" value="<?php echo(isset($_POST['phone_no_4']) && $_POST['phone_no_4']!="" ? $_POST['phone_no_4'] : (isset($find_user['phone_no_4']) && $find_user['phone_no_4']!="" ? $find_user['phone_no_4'] : ""));?>" id="phone_no_4" maxlength="20"/>
										</div>
									  <div class="clearfix"></div>
									</div>
										</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:6px">
								<div class="col-md-4">
							  		<label>Telefonnummer 2:</label>
								</div>
								<div class="col-md-8">
									<div class="each_phone_row">
										<div>
											<input class="form-control" placeholder="Telefonnummer 2" type="text" name="phone_no_2"  tabindex="8" value="<?php echo(isset($_POST['phone_no_2']) && $_POST['phone_no_2']!="" ? $_POST['phone_no_2'] : (isset($find_user['phone_no_2']) && $find_user['phone_no_2']!="" ? $find_user['phone_no_2'] : ""));?>" id="phone_no_2" maxlength="20"/>
										 </div>
									  <div class="clearfix"></div>
									</div>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:6px;">
								<div class="col-md-4">
									<label><!-- <font color = "red">*</font> --> Adresse:</label>
								</div>
								<div class="col-md-8">
									<div class="each_phone_row">
									  <div class="new_phone_cls" style="display:none;">
										  <select class="form-control" name = "address_type" id=""  tabindex="21" id="address_type">
											<?php
												foreach($option_array as $opt_key => $opt_val)
												{
											?>
													<option value = "<?php echo $opt_val;?>" <?php echo(isset($_POST['address_type']) && $_POST['address_type']==$opt_val ? "selected='selected'" : (isset($find_user['address_type']) && $find_user['address_type']==$opt_val ? "selected='selected'" : ""));?>><?php echo $opt_val;?></option>
											<?php
												}
											?>
										  </select>
										</div>
										<div>
											<textarea name="address" class="form-control address_cls" Placeholder="Adresse"  name="address"  tabindex="9" id="address"><?php echo(isset($_POST['address']) && $_POST['address']!="" ? $_POST['address'] : (isset($find_user['address']) && $find_user['address']!="" ? $find_user['address'] : ""));?></textarea>
										</div>
									  <div class="clearfix"></div>
								   </div>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:6px">
								<div class="col-md-4">
									<label>Vereinbarung:</label>
								</div>
								<div class="col-md-8">
									<textarea name="notes" class="form-control notes_cls" Placeholder="Vereinbarung" name="notes"  tabindex="10" id="notes" data-errormessage-value-missing="Hinweis ist erforderlich"><?php echo(isset($_POST['notes']) && $_POST['notes']!="" ? $_POST['notes'] : (isset($find_user['notes']) && $find_user['notes']!="" ? $find_user['notes'] : ""));?></textarea>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:6px;display:none">
								<div class="col-md-4">
							  		<label>Vereinbarung:</label>
								</div>
							  	<div class="col-md-8">
							  		<textarea class="form-control notes_cls" Placeholder="Vereinbarung" name="treatment_protocal" tabindex="11" id="treatment_protocal" data-errormessage-value-missing="Vereinbarung ist erforderlich"><?php echo(isset($_POST['treatment_protocal']) && $_POST['treatment_protocal']!="" ? $_POST['treatment_protocal'] : "");?></textarea>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:6px;">
								<div class="col-md-4">
									<label>Status:</label>
								</div>
								<div class="col-md-8">
									<select class="form-control" name = "status" id="status" tabindex="12">
										<option value = "Y" <?php echo(isset($_POST['status']) && $_POST['status']=="Y" ? "selected='selected'" : (isset($find_user['status']) && $find_user['status']=="Y" ? "selected='selected'" : ""));?>>Aktiv</option>
										<option value = "N" <?php echo(isset($_POST['status']) && $_POST['status']=="N" ? "selected='selected'" : (isset($find_user['status']) && $find_user['status']=="N" ? "selected='selected'" : ""));?>>Inaktiv</option>
									  </select>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:6px;">
								<div class="col-md-4">
								  <label>Dokument hochladen:</label>
								  <br/>
								  <a href="javascript:void(0)" onclick="addNewDoc();">weiteres Dokument hochladen.</a>
								  <br/>
								  <label></label>
								</div>
								<div class="col-md-8" id="browse_doc_link_div">
								<?php
								if(!empty($find_user_documents))
								{
									foreach($find_user_documents as $doc_key=>$doc_val)
									{
										if($doc_val['modified_name']!="" && file_exists('img/user_image/'.$doc_val['modified_name']))
										{
								?>
									<div style="margin-bottom:5px">
										<a href="<?php echo DOMAIN_NAME_PATH.'img/user_image/'.$doc_val['modified_name'];?>" target="_blank"><?php echo $doc_val['document_name'];?></a>
										<a href="javascript:void(0)" onclick="deleteOldDocRow($(this), '<?php echo(base64_encode($doc_val['id'].IDHASH));?>');" title="löschen"><span class="glyphicon glyphicon-trash"></span></a>
									</div>
								<?php
										}
									}
								}
								else
								{
								?>
									<div style="margin-bottom:5px">
										<input class="" type="file" name="user_document[]" style="display: inline-block;" onchange="addDelete($(this))"/>
									</div>
								<?php
								}
								?>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="form-group" style="padding:3px;text-align:right;">
								<button class="button" type="button" name = "cancel_contact" tabindex="14" onclick="window.location.href='listing.php'"><b>Abbrechen</b></button>
								<button class="button" type="submit" name = "edit_contact" tabindex="13"><b>Aenderung speichern</b></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php include_once('includes/right_sidebar.php');?>	
	</div>
	<?php include_once('includes/inner_footer.php');?>
</body>
</html>
<?php include_once('includes/footer.php');?>