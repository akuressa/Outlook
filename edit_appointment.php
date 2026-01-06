<?php
	include_once('init.php');
	check_login();
	has_privilege();
	$branch_list = find('all', USERS, "id, branch_name", "WHERE user_type=:user_type AND status=:status ORDER BY branch_name ASC", array(':user_type'=>'B', ':status'=>'Y'));
	if(isset($_GET['appointment_id']) && $_GET['appointment_id']!='')
	{
		$appointment_where="";$appointment_execute=array();
		if($user_privilege==true)
		{
			$appointment_where=" AND a.user_id=:user_id ";
			$appointment_execute=array(":user_id"=>$_SESSION['logged_user_id']);
		}
		else if($branch_privilege==true)
		{
			$appointment_where=" AND a.branch_id=:branch_id ";
			$appointment_execute=array(":branch_id"=>$_SESSION['logged_user_id']);
		}
		$appointment_id=substr(base64_decode($_GET['appointment_id']), 0, -5);
		$table=APPOINMENTS." as a, ".USERS." as u_1, ".USERS." as u_2";
		$where="WHERE a.id=:id AND a.user_id=u_1.id AND a.branch_id=u_2.id".$appointment_where;
		$fields="a.id, a.branch_id, a.subject, a.location, a.start_date, a.end_date, a.description, a.categories, u_1.person_name, u_1.email_address, u_1.phone_no_1, u_2.branch_name";
		$execute=array(':id'=>$appointment_id);
		$execute=array_merge($execute, $appointment_execute);
		$find_appointment= find('first', $table, $fields, $where, $execute);
		if(!empty($find_appointment))
		{
			//do nothing
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Ung&uuml;ltige Termin-ID.';
			header('location:'.DOMAIN_NAME_PATH.'appointment.php');
			exit;
		}
	}
	else
	{
		$_SESSION['SET_TYPE'] = 'error';
		$_SESSION['SET_FLASH'] = 'Termin-ID fehlt.';
		header('location:'.DOMAIN_NAME_PATH.'appointment.php');
		exit;
	}
	if(isset($_POST['btn_update_appointment']))
	{
		//print_r($_POST);
		if(isset($_POST['parent_id']) && $_POST['parent_id']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Niederlassung erforderlich.';
		}
		else if(isset($_POST['person_name']) && $_POST['person_name']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Kundenname ist erforderlich.';
		}
		/*else if(isset($_POST['email_address']) && $_POST['email_address']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Kunden E-Mail erforderlich.';
		}
		else if(isset($_POST['phone_no_1']) && $_POST['phone_no_1']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Customer phone no is required.';
		}*/
		else if(isset($_POST['subject']) && $_POST['subject']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Betreff wird ben&ouml;tigt.';
		}
		/*else if(isset($_POST['location']) && $_POST['location']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Location is required.';
		}*/
		else if(isset($_POST['categories']) && $_POST['categories']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Kategorie ist erforderlich.';
		}
		else if(isset($_POST['start_date']) && $_POST['start_date']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Startdatum ist erforderlich.';
		}
		else if(isset($_POST['end_date']) && $_POST['end_date']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Enddatum erforderlich.';
		}
		/*else if(isset($_POST['description']) && $_POST['description']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Beschreibung erforderlich.';
		}*/
		else
		{
			$appointment_id=substr(base64_decode($_GET['appointment_id']), 0, -5);
			$find_user_name=find('first', USERS, 'id, person_name', "WHERE person_name=:person_name", array(':person_name'=>stripcleantohtml($_POST['person_name'])));
			if(empty($find_user_name))
			{
				$pass=create_password(6);
				$fields_user="user_type, parent_id, person_name, email_address, password, phone_no_1, status";
				$values_user=":user_type, :parent_id, :person_name, :email_address, :password, :phone_no_1, :status";
				$execute_user=array(':user_type'=>'U',
					':parent_id'=>stripcleantohtml($_POST['parent_id']),
					':person_name'=>stripcleantohtml($_POST['person_name']), ':email_address'=>stripcleantohtml($_POST['email_address']),
					':password'=>md5($pass),
					':phone_no_1'=>stripcleantohtml($_POST['phone_no_1']),
					':status'=>'Y'
				);
				$add_user = save(USERS, $fields_user, $values_user,$execute_user);
				if($add_user > 0)
				{
					$set_values="user_id=:user_id, branch_id=:branch_id, subject=:subject, location=:location, start_date=:start_date, end_date=:end_date, description=:description, categories=:categories";
					$start_date_time_arr=explode(" ", $_POST['start_date']);
					$start_date_arr=explode(".", $start_date_time_arr[0]);
					$start_date_time=$start_date_arr[2]."-".$start_date_arr[1]."-".$start_date_arr[0]." ".$start_date_time_arr[1].":00";
					$end_date_time_arr=explode(" ", $_POST['end_date']);
					$end_date_arr=explode(".", $end_date_time_arr[0]);
					$end_date_time=$end_date_arr[2]."-".$end_date_arr[1]."-".$end_date_arr[0]." ".$end_date_time_arr[1].":00";
					$execute=array(':user_id'=>$add_user,
						':branch_id'=>stripcleantohtml($_POST['parent_id']),
						':subject'=>stripcleantohtml($_POST['subject']),
						':location'=>stripcleantohtml($_POST['location']),
						':start_date'=>$start_date_time,
						':end_date'=>$end_date_time,
						':description'=>stripcleantohtml($_POST['description']),
						':categories'=>stripcleantohtml($_POST['categories']),
						':id'=>$appointment_id
					);
					$update_appointment=update(APPOINMENTS, $set_values, 'WHERE id=:id', $execute);
					if($update_appointment > 0)
					{
						$_SESSION['SET_TYPE'] = 'success';
						$_SESSION['SET_FLASH'] = 'Termin erfolgreich aktualisiert.';
						header('location:'.DOMAIN_NAME_PATH.'view_edit_appointment.php?appointment_id='.$_GET['appointment_id']);
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
				$set_values="user_id=:user_id, branch_id=:branch_id, subject=:subject, location=:location, start_date=:start_date, end_date=:end_date, description=:description, categories=:categories";
				$start_date_time_arr=explode(" ", $_POST['start_date']);
				$start_date_arr=explode(".", $start_date_time_arr[0]);
				$start_date_time=$start_date_arr[2]."-".$start_date_arr[1]."-".$start_date_arr[0]." ".$start_date_time_arr[1].":00";
				$end_date_time_arr=explode(" ", $_POST['end_date']);
				$end_date_arr=explode(".", $end_date_time_arr[0]);
				$end_date_time=$end_date_arr[2]."-".$end_date_arr[1]."-".$end_date_arr[0]." ".$end_date_time_arr[1].":00";
				$execute=array(':user_id'=>$find_user_name['id'],
					':branch_id'=>stripcleantohtml($_POST['parent_id']),
					':subject'=>stripcleantohtml($_POST['subject']),
					':location'=>stripcleantohtml($_POST['location']),
					':start_date'=>$start_date_time,
					':end_date'=>$end_date_time,
					':description'=>stripcleantohtml($_POST['description']),
					':categories'=>stripcleantohtml($_POST['categories']),
					':id'=>$appointment_id
				);
				$update_appointment=update(APPOINMENTS, $set_values, 'WHERE id=:id', $execute);
				if($update_appointment > 0)
				{
					$_SESSION['SET_TYPE'] = 'success';
					$_SESSION['SET_FLASH'] = 'Termin erfolgreich aktualisiert.';
					header('location:'.DOMAIN_NAME_PATH.'view_edit_appointment.php?appointment_id='.$_GET['appointment_id']);
					exit;
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
			$("#edit_appointment_form").validationEngine();
			$('#start_date').datetimepicker({
				formatTime:'H:i',
				//formatDate:'d.m.Y',
				format:'d.m.Y H:i',
				//minTime:0,
				minDate:'-1970/01/01', //yesterday is minimum date(for today use 0 or -1970/01/01)
				//maxDate:'+1970/01/02' // and tommorow is maximum date calendar
				onShow:function( ct ){
					//alert($('#end_date').val().substring(0, 10));alert($('#end_date').val().substring(11));
					this.setOptions({
						//maxDate:($('#end_date').val() && $('#end_date').val()!=""?$('#end_date').val().substring(0, 10):false)
						//maxTime:$('#end_date').val()?$('#end_date').val().substring(11):false
					})
				},
				timepickerScrollbar:false,
				step:30
			});
			$('#end_date').datetimepicker({
				formatTime:'H:i',
				//formatDate:'d.m.Y',
				format:'d.m.Y H:i',
				//minTime:0,
				minDate:'-1970/01/01', //yesterday is minimum date(for today use 0 or -1970/01/01)
				//maxDate:'+1970/01/02' // and tommorow is maximum date calendar
				onShow:function( ct ){
					//alert($('#start_date').val().substring(0, 10));alert($('#start_date').val().substring(11));
					this.setOptions({
						minDate:($('#start_date').val() && $('#start_date').val()!="" ?$('#start_date').val().substring(0, 10): '-1970/01/01')
						//minTime:$('#start_date').val()?$('#start_date').val().substring(11):false
					})
				},
				timepickerScrollbar:false,
				step:30
			});
			$("#all_day").click(function(){
				if($("#all_day").is(":checked"))
				{
					if($('#start_date').val() && $('#start_date').val()!="")
					{
						$('#end_date').val($('#start_date').val().substring(0, 10)+" 23:59");
					}
					else
					{
						$('#end_date').val("");
					}
				}
				else
				{
					$('#end_date').val("");
				}
			});
			$("#person_name").keyup(function(){
				//alert($("#person_name").val());
				$("#person_name").addClass('person_name_rotate');
				$.ajax({
					type:'post',
					url: "<?php echo(DOMAIN_NAME_PATH);?>find_user_name.php",
					//dataType: "json",
					data: {
						person_name:$("#person_name").val()
					},
					success: function( data ) {
						$("#auto_sug_content_div").show();
						$("#auto_sug_content_div").html( data );
						$("#person_name").removeClass('person_name_rotate');

					},
					error: function(){
					}
				});
			});
			/*$("#person_name").focusout(function(){
				$("#auto_sug_content_div").hide();
			});*/
		});
		function value_send(value)
		{
			$("#person_name").val(value);
			$("#auto_sug_content_div").hide();
		}
	//-->
	</script>
	<style type="text/css">
		.person_name_rotate{ background:#F7F7F7 url('img/ajax-loader.gif') right center no-repeat !important; }
		.auto_sugg_cls{max-height:165px;overflow:auto;width:100%;top:65px;position:absolute;border: 1px solid rgb(204, 204, 204);border-radius:2px;background:#FFFFFF;display:none;height:auto;z-index:99999999;}
		.data_row{padding:5px;font-size:16px;cursor:pointer;}
		.data_row:hover{background:rgb(227, 239, 255);}
		.no_data_row{padding:5px;font-size:16px;color:red;text-align:center;}
	</style>
</head>
<body>
    <div class="container"> 
		<?php include_once('includes/navigation.php');?>
		<div class="col-md-8">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Bearbeiten appoinment</h3>
				</div>
				<div class=" content">
					<div class="col-md-3">&nbsp;</div>
					<div class="col-md-6">
						<form method="post" action="" id="edit_appointment_form" name="edit_appointment_form">
							<div class="form-group" style="padding:6px;text-align:left;">
								<font color = "red">*</font> Felder sind Pflichtfelder.
							</div>
							<?php
								if($admin_privilege==true)
								{
							?>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> W&auml;hlen Sie die Branche:</label>
							  <select class="form-control validate[required]" name="parent_id" id="parent_id" data-errormessage-value-missing="Bitte w&auml;hlen Sie eine Branche" tabindex="1">
								<?php
									if(!empty($branch_list))
									{
										foreach($branch_list as $branch_key=>$branch_value)
										{
								?>
								<option value = "<?php echo $branch_value['id'];?>" <?php echo(isset($_POST['parent_id']) && $_POST['parent_id']==$branch_value['id'] ? "selected='selected'" : (isset($find_appointment['branch_id']) && $find_appointment['branch_id']==$branch_value['id'] ? "selected='selected'" : ""));?>><?php echo $branch_value['branch_name'];?></option>
								<?php
										}
									}
								?>
							  </select>
							</div>
							<?php
								}
								else if($branch_privilege==true)
								{
							?>
								<input type="hidden" name="parent_id" id="parent_id" value="<?php echo $_SESSION['logged_user_id'];?>">
							<?php
								}
							?>
							<div class="form-group" style="padding:6px;position:relative;">
							  <label><font color = "red">*</font> Kundenname:</label>
							  <input class="form-control validate[required]" placeholder="Kundenname" type="text" name="person_name" id="person_name" data-errormessage-value-missing="Kundenname ist erforderlich" value="<?php echo((isset($_POST['person_name']) && $_POST['person_name']!='') ? $_POST['person_name'] : (isset($find_appointment['person_name']) && $find_appointment['person_name']!="" ? $find_appointment['person_name'] : ""));?>" maxlength="200" tabindex="2" autocomplete="off"/>
							  <div class="auto_sugg_cls" id="auto_sug_content_div"></div>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><!-- <font color = "red">*</font>  -->Kunden E-Mail-:</label>
							  <input class="form-control" placeholder="Kunden E-Mail-" type="text" name="email_address" id="email_address" value="<?php echo((isset($_POST['email_address']) && $_POST['email_address']!='') ? $_POST['email_address'] : (isset($find_appointment['email_address']) && $find_appointment['email_address']!="" ? $find_appointment['email_address'] : ""));?>" data-errormessage-value-missing="Kunden E-Mail ist erforderlich!" maxlength="255" tabindex="3"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label>Kundentelefonnummer :</label>
							  <input class="form-control" placeholder="Kunde Phone No" type="text" name="phone_no_1"  tabindex="4" value="<?php echo(isset($_POST['phone_no_1']) && $_POST['phone_no_1']!="" ? $_POST['phone_no_1'] : (isset($find_appointment['phone_no_1']) && $find_appointment['phone_no_1']!="" ? $find_appointment['phone_no_1'] : ""));?>" id="phone_no_1" maxlength="20" data-errormessage-value-missing="Kundentelefonnummer ist erforderlich "/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Gegenstand:</label>
							  <input class="form-control validate[required]" placeholder="Gegenstand" type="text" name="subject"  tabindex="5" value="<?php echo(isset($_POST['subject']) && $_POST['subject']!="" ? $_POST['subject'] : (isset($find_appointment['subject']) && $find_appointment['subject']!="" ? $find_appointment['subject'] : ""));?>" id="subject" maxlength="255" data-errormessage-value-missing="Betreff wird ben&auml;tigt"/>
							</div>
							<!-- <div class="form-group" style="padding:6px">
							  <label>Location:</label> -->
							  <input class="form-control" placeholder="Ort" type="hidden" name="location"  tabindex="6" value="<?php echo(isset($_POST['location']) && $_POST['location']!="" ? $_POST['location'] : (isset($find_appointment['location']) && $find_appointment['location']!="" ? $find_appointment['location'] : ""));?>" id="location" maxlength="150" data-errormessage-value-missing="Ort erforderlich"/>
							<!-- </div> -->
							<div class="form-group" style="padding:6px">
							  	  <label><font color = "red">*</font> Kategorie:</label>
								  <select class="form-control validate[required]" name="categories" id="categories"  data-errormessage-value-missing="Kategorie ist erforderlich">
										<option value = "">Kategorie</option>
										<?php
											if(!empty($appointment_categories))
											{
												foreach($appointment_categories as $cat_key=>$cat_value)
												{
										?>
										<option value = "<?php echo $cat_key;?>" <?php echo(isset($_POST['categories']) && $_POST['categories']==$cat_key ? "selected='selected'" : (isset($find_appointment['categories']) && $find_appointment['categories']==$cat_key ? "selected='selected'" : ""));?>><?php echo $cat_value;?></option>
										<?php
												}
											}
										?>
									
								  </select>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Anfangsdatum:</label>
							  <input class="form-control validate[required]" placeholder="Anfangsdatum" type="text" name="start_date" id = "start_date" readonly tabindex="7" value="<?php echo(isset($_POST['start_date']) && $_POST['start_date']!="" &&  $_POST['start_date']!="0000-00-00 00:00:00" ? $_POST['start_date'] : (isset($find_appointment['start_date']) && $find_appointment['start_date']!="" &&  $find_appointment['start_date']!="0000-00-00 00:00:00" ? date("d.m.Y H:i", strtotime($find_appointment['start_date'])) : ""));?>" maxlength="20" data-errormessage-value-missing="Startdatum ist erforderlich"/>
							  <input type="checkbox" id = "all_day" name="all_day" tabindex="8" value="all" <?php echo(isset($_POST['all_day']) && $_POST['all_day']=="all" ? "checked='checked'" : "");?>/> Ganzt&auml;giges Ereignis
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font>Enddatum:</label>
							   <input class="form-control validate[required]" placeholder="Enddatum" type="text" id = "end_date" name="end_date" readonly tabindex="9" value="<?php echo(isset($_POST['end_date']) && $_POST['end_date']!="" &&  $_POST['end_date']!="0000-00-00 00:00:00" ? $_POST['end_date'] : (isset($find_appointment['end_date']) && $find_appointment['end_date']!="" &&  $find_appointment['end_date']!="0000-00-00 00:00:00" ? date("d.m.Y H:i", strtotime($find_appointment['end_date'])) : ""));?>" maxlength="20" data-errormessage-value-missing="Schlussdatum ist erforderlich"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><!-- <font color = "red">*</font>  -->Beschreibung:</label>
							  <textarea name="description" id="description" class="form-control notes_cls" placeholder="Beschreibung" tabindex="10"><?php echo(isset($_POST['description']) && $_POST['description']!="" ? $_POST['description'] : (isset($find_appointment['description']) && $find_appointment['description']!="" ? $find_appointment['description'] : ""));?></textarea>
							</div>
							<div class="form-group" style="padding:3px;text-align:right;">
								<button class="button" type="button" name = "cancel_contact" tabindex="12" onclick="window.location.href='appointment.php'"><b>Abbrechen</b></button>
							  <button class="button" name = "btn_update_appointment"><b>Update f&uuml;r Besichtigung</b></button>
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