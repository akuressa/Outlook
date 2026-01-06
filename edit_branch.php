<?php
	include_once('init.php');
	check_login();
	has_privilege();
	
	if(isset($_GET['branch_id']) && $_GET['branch_id']!='')
	{
		$user_id=substr(base64_decode($_GET['branch_id']), 0, -5);
		$find_user= find('first', USERS, '*', "WHERE id=:id", array(':id'=>$user_id));
		if(!empty($find_user))
		{
			//do nothing
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Ung&uuml;ltige Zweig ID.';
			header('location:'.DOMAIN_NAME_PATH.'branch.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
	}
	else
	{
		$_SESSION['SET_TYPE'] = 'error';
		$_SESSION['SET_FLASH'] = 'Zweig ID fehlenden.';
		header('location:'.DOMAIN_NAME_PATH.'branch.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
		exit;
	}
	if(isset($_POST['edit_branch']))
	{
		//print_r($_POST);
		if(isset($_POST['branch_name']) && $_POST['branch_name']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Namen der Branche erforderlich.';
		}
		/*else if(isset($_POST['person_name']) && $_POST['person_name']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Name of the authorized person is required.';
		}
		else if(isset($_POST['company']) && $_POST['company']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Company is required.';
		}*/
		else if(isset($_POST['email_address']) && $_POST['email_address']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'E-Mail ist erforderlich.';
		}
		/*else if(isset($_POST['phone_no_1']) && $_POST['phone_no_1']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Phone number is required.';
		}*/
		else
		{
			$user_id=substr(base64_decode($_GET['branch_id']), 0, -5);
			$find_branch_name=find('first', USERS, 'id, branch_name, email_address', "WHERE (branch_name=:branch_name OR email_address=:email_address) AND id <> :id", array(':branch_name'=>stripcleantohtml($_POST['branch_name']),':email_address'=>stripcleantohtml($_POST['email_address']), ':id'=>$user_id));
			if(empty($find_branch_name))
			{
				if(isset($_FILES['picture']) && $_FILES['picture']['name']!='')
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
						$_SESSION['SET_FLASH'] = 'Ungültige Erweiterung für Bild. Bitte laden Sie .jpg oder .jpeg oder .gif oder .png Bild';
					}
				}
				else
				{
					$have_pic=false;
					$flag_status=true;
					$picture_value="";
					$picture_execute=array();
				}
				if($flag_status==true)
				{
					$pass_val="";$pass_execute=array();
					if(isset($_POST['password']) && $_POST['password']!="")
					{
						$pass_val=", password=:password";
						$pass_execute=array(':password'=>md5($_POST['password']));
					}
					$value_set="branch_name=:branch_name, person_name=:person_name, company=:company, job_title=:job_title, email_address=:email_address, display_as=:display_as, web_page_address=:web_page_address, ip_address=:ip_address, phone_no_1=:phone_no_1, phone_no_1_type=:phone_no_1_type, phone_no_2=:phone_no_2, phone_no_2_type=:phone_no_2_type, phone_no_3=:phone_no_3, phone_no_3_type=:phone_no_3_type, phone_no_4=:phone_no_4, phone_no_4_type=:phone_no_4_type, address_type=:address_type, address=:address, notes=:notes, status=:status".$picture_value.$pass_val;
					$execute=array(
						':branch_name'=>stripcleantohtml($_POST['branch_name']),
						':person_name'=>stripcleantohtml($_POST['person_name']),
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
						':id'=>$user_id
					);
					$execute=array_merge($execute, $picture_execute);
					$execute=array_merge($execute, $pass_execute);
					$update_user=update(USERS, $value_set, 'WHERE id=:id', $execute);
					if($update_user == true)
					{
						if($_POST['prev_img']!='' && file_exists('img/branch_image/'.$_POST['prev_img']))
						{
							unlink('img/branch_image/'.$_POST['prev_img']);
						}
						if($have_pic==true)
						{
							move_uploaded_file($_FILES['picture']['tmp_name'], 'img/branch_image/'.$picture_name);
						}
						$_SESSION['SET_TYPE'] = 'success';
						$_SESSION['SET_FLASH'] = 'Niederlassung erfolgreich aktualisiert.';
						header('location:'.DOMAIN_NAME_PATH.'branch.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
						exit;
					}
					else
					{
						$_SESSION['SET_TYPE'] = 'error';
						$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
					}
				}
			}
			else
			{
				if($find_branch_name['branch_name']==$_POST['branch_name'])
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Namen der Branche ist bereits vorhanden.';
				}
				else if($find_branch_name['email_address']==$_POST['email_address'])
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Diese E-Mail Adresse ist bereits vergeben.';
				}
				else
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
				}
			}
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('includes/header.php');?>
	<script type="text/javascript">
	<!--
		$(function(){
			$("#edit_branch").validationEngine();
		});
	//-->
	</script>
</head>

<body>
    <div class="container"> 
		<?php include_once('includes/navigation.php');?>
		<div class="col-md-8">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Zweig bearbeiten</h3>
				</div>
				<div class=" content">
					<div class="col-md-3">&nbsp;</div>
					<div class="col-md-6">
						<form id="edit_branch" name="edit_branch" method="post" enctype="multipart/form-data" action="">
							<div class="form-group" style="padding:6px;text-align:left;">
								<font color = "red">*</font> Felder sind Pflichtfelder.
							</div>
							<input type="hidden" name="prev_img" id="prev_img" value="<?php echo((isset($find_user['picture']) && $find_user['picture']!="" ? $find_user['picture'] : ""));?>"/>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Name des Zweiges:</label>
							  <input class="form-control validate[required]" placeholder="Name des Zweiges" type="text" id="branch_name" name="branch_name" maxlength="200"  data-errormessage-value-missing="Namen der Branche erforderlich" tabindex="1" value="<?php echo((isset($_POST['branch_name']) && $_POST['branch_name']!='') ? $_POST['branch_name'] : (isset($find_user['branch_name']) && $find_user['branch_name']!="" ? $find_user['branch_name'] : ""));?>"/>
							</div>
							<!-- <div class="form-group" style="padding:6px">
							  <label>Name of the authorized person:</label> -->
							  <input class="form-control" placeholder="Name der autorisierten Person" type="hidden" id="person_name" name="person_name" maxlength="200"  data-errormessage-value-missing="Name der autorisierten Person erforderlich" tabindex="2" value="<?php echo((isset($_POST['person_name']) && $_POST['person_name']!='') ? $_POST['person_name'] : (isset($find_user['person_name']) && $find_user['person_name']!="" ? $find_user['person_name'] : ""));?>"/>
							<!-- </div> -->
							<!-- <div class="form-group" style="padding:6px">
							  <label>Company:</label> -->
							  <input class="form-control" placeholder="Firma" type="hidden" id="company" name="company" maxlength="200"  data-errormessage-value-missing="Gesellschaft verpflichtet" tabindex="3" value="<?php echo((isset($_POST['company']) && $_POST['company']!='') ? $_POST['company'] : (isset($find_user['company']) && $find_user['company']!="" ? $find_user['company'] : ""));?>"/>
							<!-- </div> -->
							<!-- <div class="form-group" style="padding:6px">
							  <label>Job Title:</label> -->
							  <input class="form-control" placeholder="Berufsbezeichnung" type="hidden" id="job_title" name="job_title" maxlength="150" tabindex="4"  value="<?php echo((isset($_POST['job_title']) && $_POST['job_title']!='') ? $_POST['job_title'] : (isset($find_user['job_title']) && $find_user['job_title']!="" ? $find_user['job_title'] : ""));?>"/>
							<!-- </div> -->
							<!-- <div class="form-group" style="padding:6px">
							  <label>Picture:</label> -->
							  <input class="form-control" type="hidden" id="picture" name="picture" tabindex="5"/>
								<!-- <?php
									//if(isset($find_user['picture']) && $find_user['picture']!='' && file_exists('img/branch_image/'.$find_user['picture']))
									//{
								?>
									<img src="<?php echo DOMAIN_NAME_PATH.'img/branch_image/'.$find_user['picture'];?>" alt="Profile Picture" style="width:200px;margin-top:5px;"/>
								<?php
									//}
								?>
							</div> -->
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> E-Mail-Addresse:</label>
							  <input class="form-control validate[required, custom[email]]" placeholder="E-Mail-Addresses" type="text" name="email_address" id="email_address" value="<?php echo((isset($_POST['email_address']) && $_POST['email_address']!='') ? $_POST['email_address'] : (isset($find_user['email_address']) && $find_user['email_address']!="" ? $find_user['email_address'] : ""));?>" data-errormessage-value-missing="E-Mail ist erforderlich!" maxlength="255" tabindex="6"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label>Passwort:</label>
							  <input class="form-control" placeholder="Passwort" type="text" name="password" id="password" value="<?php echo((isset($_POST['password']) && $_POST['password']!='') ? $_POST['password'] : "");?>"/>
							</div>
							<!-- <div class="form-group" style="padding:6px">
							  <label>Display as:</label> -->
							  <input class="form-control" placeholder="Zeige es als" type="hidden" name="display_as" id="display_as" value="<?php echo((isset($_POST['display_as']) && $_POST['display_as']!='') ? $_POST['display_as'] : (isset($find_user['display_as']) && $find_user['display_as']!="" ? $find_user['display_as'] : ""));?>" maxlength="150" tabindex="7"/>
							<!-- </div> -->
							<div class="form-group" style="padding:6px">
							  <label>Webseitenadresse:</label>
							  <input class="form-control" placeholder="Webseitenadresse" type="text" name="web_page_address" id="web_page_address" value="<?php echo((isset($_POST['web_page_address']) && $_POST['web_page_address']!='') ? $_POST['web_page_address'] : (isset($find_user['web_page_address']) && $find_user['web_page_address']="" ? $find_user['web_page_address'] : ""));?>" tabindex="8"/>
							</div>
							<!-- <div class="form-group" style="padding:6px">
							  <label>IM Address:</label> -->
							  <input class="form-control" placeholder="IM-Adresse" type="hidden" name="ip_address" id="ip_address" value="<?php echo((isset($_POST['ip_address']) && $_POST['ip_address']!='') ? $_POST['ip_address'] : (isset($find_user['ip_address']) && $find_user['ip_address']!="" ? $find_user['ip_address'] : ""));?>" tabindex="9" maxlength="100"/>
							<!-- </div> -->
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Telefonnummer:</label>
							  <div class="each_phone_row">
									<div class="new_phone_cls">
										  <select class="form-control" name = "phone_no_1_type" id="" tabindex="10" id="phone_no_1_type">
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
									<div class="new_phone_cls">
										<input class="form-control" placeholder="Telefonnummer" type="text" name="phone_no_1"  tabindex="11" value="<?php echo(isset($_POST['phone_no_1']) && $_POST['phone_no_1']!="" ? $_POST['phone_no_1'] : (isset($find_user['phone_no_1']) && $find_user['phone_no_1']!="" ? $find_user['phone_no_1'] : ""));?>" id="phone_no_1" maxlength="20" data-errormessage-value-missing="Telefonnummer ist erforderlich,"/>
									 </div>
									 <div class="clearfix"></div>
								</div>
								<div class="each_phone_row">
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
									<div class="new_phone_cls">
										<input class="form-control" placeholder="Telefonnummer" type="text" name="phone_no_2"  tabindex="13" value="<?php echo(isset($_POST['phone_no_2']) && $_POST['phone_no_2']!="" ? $_POST['phone_no_2'] : (isset($find_user['phone_no_2']) && $find_user['phone_no_2']!="" ? $find_user['phone_no_2'] : ""));?>" id="phone_no_2" maxlength="20"/>
									 </div>
								  <div class="clearfix"></div>
								</div>
								<div class="each_phone_row">
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
										<input class="form-control" placeholder="Telefonnummer" type="text" name="phone_no_3"  tabindex="15" value="<?php echo(isset($_POST['phone_no_3']) && $_POST['phone_no_3']!="" ? $_POST['phone_no_3'] : (isset($find_user['phone_no_3']) && $find_user['phone_no_3']!="" ? $find_user['phone_no_3'] : ""));?>" id="phone_no_3" maxlength="20"/>
									</div>
								  <div class="clearfix"></div>
								</div>
								<div class="each_phone_row">
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
										<input class="form-control" placeholder="Telefonnummer" type="text" name="phone_no_4"  tabindex="17" value="<?php echo(isset($_POST['phone_no_4']) && $_POST['phone_no_4']!="" ? $_POST['phone_no_4'] : (isset($find_user['phone_no_4']) && $find_user['phone_no_4']!="" ? $find_user['phone_no_4'] : ""));?>" id="phone_no_4" maxlength="20"/>
									</div>
								  <div class="clearfix"></div>
								</div>
							</div>
							<div class="form-group" style="padding:6px">
							  <label>Adresse:</label>
							  <div class="each_phone_row">
								  <div class="new_phone_cls">
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
									<div class="new_phone_cls">
										<textarea name="address" class="form-control address_cls" Placeholder="Adresse:"  name="address"  tabindex="22" id="address"><?php echo(isset($_POST['address']) && $_POST['address']!="" ? $_POST['address'] : (isset($find_user['address']) && $find_user['address']!="" ? $find_user['address'] : ""));?></textarea>
									</div>
								  <div class="clearfix"></div>
							   </div>
							</div>
							<div class="form-group" style="padding:6px">
							  <label>Hinweise:</label>
							  <textarea name="notes" class="form-control notes_cls" Placeholder="Hinweise" name="notes"  tabindex="23" id="notes"><?php echo(isset($_POST['notes']) && $_POST['notes']!="" ? $_POST['notes'] : (isset($find_user['notes']) && $find_user['notes']!="" ? $find_user['notes'] : ""));?></textarea>
							</div>
							<div class="form-group" style="padding:6px">
							  <label>Status:</label>
							  <select class="form-control" name = "status" id="status" tabindex="24">
								<option value = "Y" <?php echo(isset($_POST['status']) && $_POST['status']=="Y" ? "selected='selected'" : (isset($find_user['status']) && $find_user['status']=="Y" ? "selected='selected'" : ""));?>>Aktiv</option>
								<option value = "N" <?php echo(isset($_POST['status']) && $_POST['status']=="N" ? "selected='selected'" : (isset($find_user['status']) && $find_user['status']=="N" ? "selected='selected'" : ""));?>>Inaktiv</option>
							  </select>
							</div>
							<div class="form-group" style="padding:3px;text-align:right;">
							  <button class="button" name = "edit_branch" tabindex="25"><b>Zweig bearbeiten</b></button>
							</div>
						</form>
					</div>
					<div class="col-md-3">&nbsp;</div>
				</div>
			</div>
		</div>
		<?php include_once('includes/right_sidebar.php');?>
	</div>
	<?php include_once('includes/inner_footer.php');?>
</body>
</html>
<?php include_once('includes/footer.php');?>