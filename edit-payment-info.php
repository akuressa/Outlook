<?php
	include_once('init.php');
	check_login();
	has_privilege();
	
	if(isset($_GET['payment_id']) && $_GET['payment_id']!='')
	{
		$payment_id=substr(base64_decode($_GET['payment_id']), 0, -5);
		$find_payment= find('first', PAYMENT, '*', "WHERE id=:id", array(':id'=>$payment_id));
		if(!empty($find_payment))
		{
			$find_extra_payment= find('all', EXTRA_PAYMENT, '*', "WHERE payment_info_master_id=:payment_info_master_id", array(':payment_info_master_id'=>$payment_id));
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Ungültige Zahlungsinformation id.';
			header('location:'.DOMAIN_NAME_PATH.'payment-list.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
	}
	else
	{
		$_SESSION['SET_TYPE'] = 'error';
		$_SESSION['SET_FLASH'] = 'Zahlungsinformation id fehlt.';
		header('location:'.DOMAIN_NAME_PATH.'payment-list.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
		exit;
	}
	if(isset($_POST['edit_payment']))
	{
		//print_r($_POST);
		if(isset($_POST['client_name']) && $_POST['client_name']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Kundenname erforderlich.';
		}
		else if(isset($_POST['amount_due']) && $_POST['amount_due']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Offener Betrag erforderlich.';
		}
		/*else if(isset($_POST['amount_paid']) && $_POST['amount_paid']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Betrag bezahlt erforderlich.';
		}*/
		else if(isset($_POST['description']) && $_POST['description']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Zahlungsdetails erforderlich.';
		}
		else
		{
			$payment_id=substr(base64_decode($_GET['payment_id']), 0, -5);
			/*$find_payment_client_name=find('first', PAYMENT, 'id, client_name', "WHERE client_name=:client_name  AND id <> :id", array(':client_name'=>stripcleantohtml($_POST['client_name']), ':id'=>$payment_id));
			if(empty($find_payment_client_name))
			{*/
				$value_set="client_name=:client_name, amount_due=:amount_due, amount_paid=:amount_paid, description=:description";
				$execute=array(
					':client_name'=>stripcleantohtml($_POST['client_name']),
					':amount_due'=>stripcleantohtml($_POST['amount_due']),
					':amount_paid'=>stripcleantohtml($_POST['amount_paid']),
					':description'=>stripcleantohtml($_POST['description']),
					':id'=>$payment_id
				);
				$update_payment=update(PAYMENT, $value_set, 'WHERE id=:id', $execute);
				if($update_payment == true)
				{
					if(isset($_POST['added_extra_payment']) && !empty($_POST['added_extra_payment']))
					{
						foreach($_POST['added_extra_payment'] as $ext_key=>$ext_val)
						{
							if($ext_val!="" && isset($_POST['added_amount_due']) && isset($_POST['added_amount_due'][$ext_key]) && isset($_POST['added_reason']) && isset($_POST['added_reason'][$ext_key]))
							{
								$ext_where="WHERE id=:id";
								$ext_values="amount=:amount, reason=:reason";
								$ext_execute=array(
									':id'=>$ext_val,
									':amount'=>stripcleantohtml($_POST['added_amount_due'][$ext_key]),
									':reason'=>stripcleantohtml($_POST['added_reason'][$ext_key])
								);
								$add_ext_payment = update(EXTRA_PAYMENT, $ext_values, $ext_where,$ext_execute);
							}
						}
					}
					if(isset($_POST['positive_amount_due']) && !empty($_POST['positive_amount_due']))
					{
						foreach($_POST['positive_amount_due'] as $pos_key=>$pos_val)
						{
							if($pos_val!="" && isset($_POST['positive_reason']) && isset($_POST['positive_reason'][$pos_key]))
							{
								$ext_fields="payment_info_master_id, payment_type, amount, reason, created";
								$ext_values=":payment_info_master_id, :payment_type, :amount, :reason, :created";
								$ext_execute=array(
									':payment_info_master_id'=>$payment_id,
									':payment_type'=>"P",
									':amount'=>stripcleantohtml($pos_val),
									':reason'=>stripcleantohtml($_POST['positive_reason'][$pos_key]),
									':created'=>date("Y-m-d H:i:s")
								);
								$add_ext_payment = save(EXTRA_PAYMENT, $ext_fields, $ext_values,$ext_execute);
							}
						}
					}
					if(isset($_POST['negative_amount_due']) && !empty($_POST['negative_amount_due']))
					{
						foreach($_POST['negative_amount_due'] as $neg_key=>$neg_val)
						{
							if($neg_val!="" && isset($_POST['negative_reason']) && isset($_POST['negative_reason'][$neg_key]))
							{
								$ext_fields="payment_info_master_id, payment_type, amount, reason, created";
								$ext_values=":payment_info_master_id, :payment_type, :amount, :reason, :created";
								$ext_execute=array(
									':payment_info_master_id'=>$payment_id,
									':payment_type'=>"N",
									':amount'=>stripcleantohtml($neg_val),
									':reason'=>stripcleantohtml($_POST['positive_reason'][$neg_key]),
									':created'=>date("Y-m-d H:i:s")
								);
								$add_ext_payment = save(EXTRA_PAYMENT, $ext_fields, $ext_values,$ext_execute);
							}
						}
					}
					$_SESSION['SET_TYPE'] = 'success';
					$_SESSION['SET_FLASH'] = 'Zahlungsinformationen erfolgreich aktualisiert.';
					header('location:'.DOMAIN_NAME_PATH.'payment-list.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
					exit;
				}
				else
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
				}
			/*}
			else
			{
				if($find_payment_client_name['client_name']==$_POST['client_name'])
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Kundenname ist bereits vorhanden.';
				}
				else
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
				}
			}*/
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<?php include_once('includes/header.php');?>
	<script type="text/javascript">
	<!--
		$(function(){
			$("#edit_payment_form").validationEngine();
			$("#client_name").keyup(function(){
				$("#client_name").addClass('person_name_rotate');
				$.ajax({
					type:'post',
					url: "<?php echo(DOMAIN_NAME_PATH);?>find_contact_name.php",
					//dataType: "json",
					data: {
						client_name:$("#client_name").val()
					},
					success: function( data ) {
						$("#auto_sug_content_div").show();
						$("#auto_sug_content_div").html( data );
						$("#client_name").removeClass('person_name_rotate');

					},
					error: function(){
					}
				});
			});
		});
		function value_send(value)
		{
			$("#client_name").val(value);
			$("#auto_sug_content_div").hide();
			$("#client_name").focus();
		}
		function AddNewRow(cur, type)
		{
			if(type=="P" || type=="N")
			{
				var add_html='';
				add_html+='<div class="each_row_div '+(type=="P" ? "" : "negative_row")+'">';
					add_html+='<table>';
						add_html+='<tr>';
							add_html+='<td>'+(type=="P" ? "+" : "-")+'</td>';
							add_html+='<td>';
								add_html+='<input class="form-control validate[custom[number]]" placeholder="Offener Betrag" type="text" name="'+(type=="P" ? "positive" : "negative")+'_amount_due[]" maxlength="13"  data-errormessage-value-missing="Offener Betrag erforderlich" value=""/>';
								add_html+='<textarea name="'+(type=="P" ? "positive" : "negative")+'_reason[]" class="form-control " Placeholder="Grund" data-errormessage-value-missing="Grund ist erforderlich"></textarea>';
							add_html+='</td>';
							/*add_html+='<td class="del_td">';
								add_html+='<a href="javascript:void(0)" onclick="NewRowDelete($(this));" title="löschen"><span class="glyphicon glyphicon-trash"></span></a>';
							add_html+='</td>';*/
						add_html+='</tr>';
					add_html+='</table>';
				add_html+='</div>';
				$("#all_new_rwo_div").append(add_html);
			}
			else
			{
				showError('Invalid type');
			}
		}
		/*function NewRowDelete(cur)
		{
			if(confirm("Sind Sie sicher, dass Sie diese Zeile löschen möchten?"))
				cur.parents(".each_row_div").remove();
		}
		function AddedRowDelete(cur, ext_id)
		{
			if(confirm('Sind Sie sicher, dass Sie diese Zeile löschen möchten?'))
			{
				$.ajax({
					url: '<?php echo(DOMAIN_NAME_PATH)?>delete_extra_payment.php',
					dataType: 'json',
					type: 'POST',
					data:{
						ext_id: ext_id
					},
					beforeSend: function() {
						$("#loading_img_bg").show();
					},
					success: function(response){
						//console.log(response);
						$("#loading_img_bg").hide();
						if(response.msg=="success")
						{
							showSuccess('Zahlungsdetails erfolgreich gelöscht');
							cur.parents(".each_row_div").remove();
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
		}*/
	//-->
	</script>
	<style type="text/css">
		.person_name_rotate{ background:#F7F7F7 url('img/ajax-loader.gif') right center no-repeat !important; }
		.auto_sugg_cls{max-height:165px;overflow:auto;width:97%;top:62px;position:absolute;border: 1px solid rgb(204, 204, 204);border-radius:2px;background:#FFFFFF;display:none;height:auto;z-index:99999999;}
		.data_row{padding:5px;font-size:16px;cursor:pointer;}
		.data_row:hover{background:rgb(227, 239, 255);}
		.no_data_row{padding:5px;font-size:16px;color:red;text-align:center;}
		.each_row_div{margin:10px 0px;border: 1px solid #d6d6d6;padding: 5px;}
		.each_row_div table{width:100%;}
		.each_row_div input{margin-bottom:5px;}
		.each_row_div table tr td:first-child{font-size: 30px;text-align: center;}
		.negative_row table tr td:first-child{font-size: 52px;text-align: center;}
		.creation_time{font-size:15px;width:100px;text-align:center;margin: 0px auto;}
		.del_td{text-align:center;}
	</style>
</head>

<body>
    <div class="container"> 
		<?php include_once('includes/navigation.php');?>
		<div class="col-md-8">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">Zahlungsinformationen bearbeiten</h3>
				</div>
				<div class=" content">
					<div class="col-md-3">&nbsp;</div>
					<div class="col-md-6">
						<form id="edit_payment_form" name="edit_payment_form" method="post" enctype="multipart/form-data" action="">
							<div class="form-group" style="padding:6px;text-align:left;">
								<font color = "red">*</font> Felder sind Pflichtfelder.
							</div>
							<div class="form-group" style="padding:6px;position:relative;">
							  <label><font color = "red">*</font> Kundenname:</label>
							  <input class="form-control validate[required]" placeholder="Kundenname" type="text" id="client_name" name="client_name" maxlength="255"  data-errormessage-value-missing="Kundenname erforderlich" tabindex="1" value="<?php echo((isset($_POST['client_name']) && $_POST['client_name']!='') ? $_POST['client_name'] : (isset($find_payment['client_name']) && $find_payment['client_name']!="" ? $find_payment['client_name'] : ""));?>" autocomplete="off"/>
							  <div class="auto_sugg_cls" id="auto_sug_content_div"></div>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Offener Betrag:</label><br/>
							  <input class="form-control  validate[required, custom[number]]" placeholder="Offener Betrag" type="text" id="amount_due" name="amount_due" maxlength="13"  data-errormessage-value-missing="Offener Betrag erforderlich" tabindex="2" value="<?php echo((isset($_POST['amount_due']) && $_POST['amount_due']!='') ? $_POST['amount_due'] : (isset($find_payment['amount_due']) && $find_payment['amount_due']!="" ? $find_payment['amount_due'] : ""));?>" style="width:250px;display:inline-block;"/>
							  <a href="javascript:void(0)" onclick="AddNewRow($(this), 'P');" title="Positive Zahlung hinzufügen" style="color:green;display:inline-block;font-size: 20px;margin: 0px 15px;"><span class="glyphicon glyphicon-plus"></span></a>
							  <a href="javascript:void(0)" onclick="AddNewRow($(this), 'N');" title="Negative Zahlung hinzufügen" style="color:red;display:inline-block;font-size: 20px;"><span class="glyphicon glyphicon-minus"></span></a><br/>
								<?php
								if(isset($find_extra_payment) && !empty($find_extra_payment))
								{
									foreach($find_extra_payment as $pay_key=>$pay_val)
									{
								?>
								<div class="each_row_div <?php echo($pay_val['payment_type']=="N" ? "negative_row" : "");?>">
									<table>
										<tr>
											<td>
												<?php echo($pay_val['payment_type']=="N" ? "-" : "+");?>
												<div class="creation_time">Created: <?php echo change_date_time_format($pay_val['created'], "Y-m-d H:i:s");?></div>
											</td>
											<td>
												<input type="hidden" name="added_extra_payment[]" value="<?php echo $pay_val['id'];?>">
												<input class="form-control validate[custom[number]]" placeholder="Offener Betrag" type="text" name="added_amount_due[]" maxlength="13"  data-errormessage-value-missing="Offener Betrag erforderlich" value="<?php echo $pay_val['amount'];?>"/>
												<textarea name="added_reason[]" class="form-control " Placeholder="Grund" data-errormessage-value-missing="Grund ist erforderlich"><?php echo $pay_val['reason'];?></textarea>
											</td>
											<!-- <td class="del_td">
												<a href="javascript:void(0)" onclick="AddedRowDelete($(this), '<?php echo(base64_encode($pay_val['id'].IDHASH));?>');" title="löschen"><span class="glyphicon glyphicon-trash"></span></a>
											</td> -->
										</tr>
									</table>
								</div>
								<?php
									}
								}
								?>
								<div id="all_new_rwo_div">
									<?php
									if(isset($_POST['positive_amount_due']) && !empty($_POST['positive_amount_due']))
									{
										foreach($_POST['positive_amount_due'] as $pos_key=>$pos_val)
										{
											if($pos_val!="" && isset($_POST['positive_reason']) && isset($_POST['positive_reason'][$pos_key]))
											{
									?>
									<div class="each_row_div">
										<table>
											<tr>
												<td>+</td>
												<td>
													<input class="form-control validate[custom[number]]" placeholder="Offener Betrag" type="text" name="positive_amount_due[]" maxlength="13"  data-errormessage-value-missing="Offener Betrag erforderlich" value="<?php echo $pos_val;?>"/>
													<textarea name="reason[]" class="form-control " Placeholder="Grund" data-errormessage-value-missing="Grund ist erforderlich"><?php echo $_POST['positive_reason'][$pos_key];?></textarea>
												</td>
											</tr>											
											<!-- <td class="del_td">
												<a href="javascript:void(0)" onclick="NewRowDelete($(this));" title="löschen"><span class="glyphicon glyphicon-trash"></span></a>
											</td> -->
										</table>
									</div>
									<?php
											}
										}
									}
									if(isset($_POST['negative_amount_due']) && !empty($_POST['negative_amount_due']))
									{
										foreach($_POST['negative_amount_due'] as $neg_key=>$neg_val)
										{
											if($neg_val!="" && isset($_POST['negative_reason']) && isset($_POST['negative_reason'][$neg_key]))
											{
									?>
									<div class="each_row_div negative_row">
										<table>
											<tr>
												<td>-</td>
												<td>
													<input class="form-control validate[custom[number]]" placeholder="Offener Betrag" type="text" name="positive_amount_due[]" maxlength="13"  data-errormessage-value-missing="Offener Betrag erforderlich" value="<?php echo $neg_val;?>"/>
													<textarea name="reason[]" class="form-control " Placeholder="Grund" data-errormessage-value-missing="Grund ist erforderlich"><?php echo $_POST['negative_reason'][$neg_key];?></textarea>
												</td>
												<!-- <td class="del_td">
													<a href="javascript:void(0)" onclick="NewRowDelete($(this));" title="löschen"><span class="glyphicon glyphicon-trash"></span></a>
												</td> -->
											</tr>
										</table>
									</div>
									<?php
											}
										}
									}
									?>
								</div>
							</div>
							<div class="form-group" style="padding:6px">
							  <label> Betrag bezahlt:</label>
							  <input class="form-control validate[custom[number]]" placeholder="Betrag bezahlt" type="text" id="amount_paid" name="amount_paid" maxlength="13"  data-errormessage-value-missing="Betrag bezahlt erforderlich" tabindex="3" value="<?php echo((isset($_POST['amount_paid']) && $_POST['amount_paid']!='') ? $_POST['amount_paid'] : (isset($find_payment['amount_paid']) && $find_payment['amount_paid']!="" ? $find_payment['amount_paid'] : ""));?>"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Zahlungsdetails:</label>
							  <textarea name="description" class="form-control validate[required]" Placeholder="Zahlungsdetails" data-errormessage-value-missing="Zahlungsdetails ist erforderlich" id="description" tabindex="4"><?php echo(isset($_POST['description']) && $_POST['description']!="" ? $_POST['description'] : (isset($find_payment['description']) && $find_payment['description']!="" ? $find_payment['description'] : ""));?></textarea>
							</div>
							<div class="form-group" style="padding:3px;text-align:right;">
							  <button class="button" name = "edit_payment" tabindex="5"><b>Zahlungsinformationen bearbeiten</b></button>
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