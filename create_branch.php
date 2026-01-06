<?php
	include_once('init.php');
	check_login();
	has_privilege();
	if(isset($_POST['create_branch']))
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
			$_SESSION['SET_FLASH'] = 'E-Mail erforderlich.';
		}
		/*else if(isset($_POST['phone_no_1']) && $_POST['phone_no_1']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Phone number is required.';
		}*/
		else
		{
			$find_branch_name=find('first', USERS, 'id, branch_name, email_address', "WHERE branch_name=:branch_name OR email_address=:email_address", array(':branch_name'=>stripcleantohtml($_POST['branch_name']),':email_address'=>stripcleantohtml($_POST['email_address'])));
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
						$picture_field=", picture";
						$picture_value=", :picture";
						$picture_execute=array(':picture'=>$picture_name);
					}
					else
					{
						$have_pic=false;
						$flag_status = false;
						$_SESSION['SET_TYPE'] = 'error';
						$_SESSION['SET_FLASH'] = 'Ung&uuml;ltige Erweiterung für Bild. Bitte laden Sie .jpg oder .jpeg oder .gif oder .png Bild';
					}
				}
				else
				{
					$have_pic=false;
					$flag_status=true;
					$picture_field="";
					$picture_value="";
					$picture_execute=array();
				}
				if($flag_status==true)
				{
					$pass=create_password(6);
					$fields="user_type, branch_name, person_name, company, job_title, email_address, password, display_as, web_page_address, ip_address, phone_no_1, phone_no_1_type, phone_no_2, phone_no_2_type, phone_no_3, phone_no_3_type, phone_no_4, phone_no_4_type, address_type, address, notes, status".$picture_field;
					$values=":user_type, :branch_name, :person_name, :company, :job_title, :email_address, :password, :display_as, :web_page_address, :ip_address, :phone_no_1, :phone_no_1_type, :phone_no_2, :phone_no_2_type, :phone_no_3, :phone_no_3_type, :phone_no_4, :phone_no_4_type, :address_type, :address, :notes, :status".$picture_value;
					$execute=array(':user_type'=>'B',
						':branch_name'=>stripcleantohtml($_POST['branch_name']),
						':person_name'=>stripcleantohtml($_POST['person_name']),
						':company'=>stripcleantohtml($_POST['company']),
						':job_title'=>stripcleantohtml($_POST['job_title']), ':email_address'=>stripcleantohtml($_POST['email_address']),
						':password'=>md5($pass),
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
						':status'=>stripcleantohtml($_POST['status'])
					);
					$execute=array_merge($execute, $picture_execute);
					$add_branch = save(USERS, $fields, $values,$execute);
					if($add_branch > 0)
					{
						if($have_pic==true)
						{
							move_uploaded_file($_FILES['picture']['tmp_name'], 'img/branch_image/'.$picture_name);
						}
						$mail_Body = "Dear ".$_POST['person_name'].",<br/><br/>Thank you for creating an account as branch. Your login details for you account are ,<br/><br/>"."<b>Email: ".$_POST['email_address']."<br/><br/>Password: ".$pass."</b><br/><br/>Regards,<br/>Administrator.";
						Send_HTML_Mail($_POST['email_address'], ADMIN_EMAIL, '', 'New login details', $mail_Body);
						$_SESSION['SET_TYPE'] = 'success';
						$_SESSION['SET_FLASH'] = 'Niederlassung erfolgreich hinzugef&uuml;gt.';
						header('location:'.DOMAIN_NAME_PATH.'branch.php');
						exit;
					}
					else
					{
						$_SESSION['SET_TYPE'] = 'error';
						$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&uuml;ter.';
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
					$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&uuml;ter.';
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
			$("#add_branch").validationEngine();
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
					<h3 class="panel-title">Erstellen neuer Zweig</h3>
				</div>
				<div class=" content">
					<div class="col-md-3">&nbsp;</div>
					<div class="col-md-6">
						<form id="add_branch" name="add_branch" method="post" enctype="multipart/form-data" action="">
							<div class="form-group" style="padding:6px;text-align:left;">
								<font color = "red">*</font> Fields are mandatory.
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Felder sind Pflichtfelder.</label>
							  <input class="form-control validate[required]" placeholder="Name des Zweiges" type="text" id="branch_name" name="branch_name" maxlength="200"  data-errormessage-value-missing="Namen der Branche erforderlich" tabindex="1" value="<?php echo((isset($_POST['branch_name']) && $_POST['branch_name']!='') ? $_POST['branch_name'] : "");?>"/>
							</div>
							<!-- <div class="form-group" style="padding:6px">
							  <label>Name of the authorized person:</label> -->
							  <input class="form-control" placeholder="Name der autorisierten Person" type="hidden" id="person_name" name="person_name" maxlength="200"  data-errormessage-value-missing="Name der autorisierten Person erforderlich" tabindex="2" value="<?php echo((isset($_POST['person_name']) && $_POST['person_name']!='') ? $_POST['person_name'] : "");?>"/>
							<!-- </div> -->
							<!-- <div class="form-group" style="padding:6px">
							  <label>Company:</label> -->
							  <input class="form-control" placeholder="Firma" type="hidden" id="company" name="company" maxlength="200"  data-errormessage-value-missing="Company is required" tabindex="3" value="<?php echo((isset($_POST['company']) && $_POST['company']!='') ? $_POST['company'] : "");?>"/>
							<!-- </div> -->
							<!-- <div class="form-group" style="padding:6px">
							  <label>Job Title:</label> -->
							  <input class="form-control" placeholder="Job Title" type="hidden" id="job_title" name="job_title" maxlength="150" tabindex="4"  value="<?php echo((isset($_POST['job_title']) && $_POST['job_title']!='') ? $_POST['job_title'] : "");?>"/>
							<!-- </div> -->
							<!-- <div class="form-group" style="padding:6px">
							  <label>Picture:</label> -->
							  <input class="form-control" type="hidden" id="picture" name="picture" tabindex="5"/>
							<!-- </div> -->
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> E-Mail-Adresse:</label>
							  <input class="form-control validate[required, custom[email]]" placeholder="E-Mail-Adresse:" type="text" name="email_address" id="email_address" value="<?php echo((isset($_POST['email_address']) && $_POST['email_address']!='') ? $_POST['email_address'] : "");?>" data-errormessage-value-missing="E-Mail ist erforderlich!" maxlength="255" tabindex="6"/>
							</div>
							<!-- <div class="form-group" style="padding:6px">
							  <label>Display as:</label> -->
							  <input class="form-control" placeholder="Display as" type="hidden" name="display_as" id="display_as" value="<?php echo((isset($_POST['display_as']) && $_POST['display_as']!='') ? $_POST['display_as'] : "");?>" maxlength="150" tabindex="7"/>
							<!-- </div> -->
							<div class="form-group" style="padding:6px">
							  <label>Web page Address:</label>
							  <input class="form-control" placeholder="Webseitenadresse" type="text" name="web_page_address" id="web_page_address" value="<?php echo((isset($_POST['web_page_address']) && $_POST['web_page_address']!='') ? $_POST['web_page_address'] : "");?>" tabindex="8"/>
							</div>
							<!-- <div class="form-group" style="padding:6px">
							  <label>IM Address:</label> -->
							  <input class="form-control" placeholder="IM Address" type="hidden" name="ip_address" id="ip_address" value="<?php echo((isset($_POST['ip_address']) && $_POST['ip_address']!='') ? $_POST['ip_address'] : "");?>" tabindex="9" maxlength="100"/>
							<!-- </div> -->
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Telefonnummer:</label>
							  <div class="each_phone_row">
									<div class="new_phone_cls">
										  <select class="form-control" name = "phone_no_1_type" id=""  tabindex="10" id="phone_no_1_type">
											<?php
												foreach($option_array as $opt_key => $opt_val)
												{
											?>
													<option value = "<?php echo $opt_val;?>" <?php echo(isset($_POST['phone_no_1_type']) && $_POST['phone_no_1_type']==$opt_val ? "selected='selected'" : "");?>><?php echo $opt_val;?></option>
											<?php
												}
											?>
										  </select>
									</div>
									<div class="new_phone_cls">
										<input class="form-control validate[required]" placeholder="Telefonnummer" type="text" name="phone_no_1"  tabindex="11" value="<?php echo(isset($_POST['phone_no_1']) && $_POST['phone_no_1']!="" ? $_POST['phone_no_1'] : "");?>" id="phone_no_1" maxlength="20" data-errormessage-value-missing="Telefonnummer ist erforderlich,"/>
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
												<option value = "<?php echo $opt_val;?>" <?php echo(isset($_POST['phone_no_2_type']) && $_POST['phone_no_2_type']==$opt_val ? "selected='selected'" : "");?>><?php echo $opt_val;?></option>
										<?php
											}
										?>
									  </select>
									</div>
									<div class="new_phone_cls">
										<input class="form-control" placeholder="Telefonnummer" type="text" name="phone_no_2"  tabindex="13" value="<?php echo(isset($_POST['phone_no_2']) && $_POST['phone_no_2']!="" ? $_POST['phone_no_2'] : "");?>" id="phone_no_2" maxlength="20"/>
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
												<option value = "<?php echo $opt_val;?>" <?php echo(isset($_POST['phone_no_3_type']) && $_POST['phone_no_3_type']==$opt_val ? "selected='selected'" : "");?>><?php echo $opt_val;?></option>
										<?php
											}
										?>
									  </select>
									</div>
									<div class="new_phone_cls">
										<input class="form-control" placeholder="Telefonnummer" type="text" name="phone_no_3"  tabindex="15" value="<?php echo(isset($_POST['phone_no_3']) && $_POST['phone_no_3']!="" ? $_POST['phone_no_3'] : "");?>" id="phone_no_3" maxlength="20"/>
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
												<option value = "<?php echo $opt_val;?>" <?php echo(isset($_POST['phone_no_4_type']) && $_POST['phone_no_4_type']==$opt_val ? "selected='selected'" : "");?>><?php echo $opt_val;?></option>
										<?php
											}
										?>
									  </select>
									</div>
									<div class="new_phone_cls">
										<input class="form-control" placeholder="Telefonnummer" type="text" name="phone_no_4"  tabindex="17" value="<?php echo(isset($_POST['phone_no_4']) && $_POST['phone_no_4']!="" ? $_POST['phone_no_4'] : "");?>" id="phone_no_4" maxlength="20"/>
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
												<option value = "<?php echo $opt_val;?>" <?php echo(isset($_POST['address_type']) && $_POST['address_type']==$opt_val ? "selected='selected'" : "");?>><?php echo $opt_val;?></option>
										<?php
											}
										?>
									  </select>
									</div>
									<div class="new_phone_cls">
										<textarea name="address" class="form-control address_cls" Placeholder="Adresse:"  name="address"  tabindex="22" id="address"><?php echo(isset($_POST['address']) && $_POST['address']!="" ? $_POST['address'] : "");?></textarea>
									</div>
								  <div class="clearfix"></div>
							   </div>
							</div>
							<div class="form-group" style="padding:6px">
							  <label>Hinweise:</label>
							  <textarea name="notes" class="form-control notes_cls" Placeholder="Hinweise" name="notes"  tabindex="23" id="notes"><?php echo(isset($_POST['notes']) && $_POST['notes']!="" ? $_POST['notes'] : "");?></textarea>
							</div>
							<div class="form-group" style="padding:6px">
							  <label>Status:</label>
							  <select class="form-control" name = "status" id="status" tabindex="24">
								<option value = "Y" <?php echo(isset($_POST['status']) && $_POST['status']=="Y" ? "selected='selected'" : "");?>>Aktiv</option>
								<option value = "N" <?php echo(isset($_POST['status']) && $_POST['status']=="N" ? "selected='selected'" : "");?>>Inaktiv</option>
							  </select>
							</div>
							<div class="form-group" style="padding:3px;text-align:right;">
							  <button class="button" name = "create_branch" tabindex="25"><b>Erstellen neuer Zweig</b></button>
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