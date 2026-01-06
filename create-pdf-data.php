<?php
	include_once('init.php');
	include("mpdf6/mpdf.php");//mpdf 6
	//require_once __DIR__ . '/mpdf/vendor/autoload.php';	//mpdf 7
	check_login();
	has_privilege();
	if(isset($_POST['create_pdf']))
	{
		//print_r($_POST);
		if(isset($_POST['client_name']) && $_POST['client_name']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Kundenname erforderlich.';
		}
		else if(isset($_POST['left_address']) && $_POST['left_address']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Adresse erforderlich.';
		}
		/*else if(isset($_POST['reference_no']) && $_POST['reference_no']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Rechnung Nr erforderlich.';
		}*/
		else if(isset($_POST['pdf_date']) && $_POST['pdf_date']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Datum erforderlich.';
		}
		else if(isset($_POST['no_of_pages']) && $_POST['no_of_pages']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Seite erforderlich.';
		}
		else if(isset($_POST['editor_name']) && $_POST['editor_name']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Herausgeber erforderlich.';
		}
		else if(isset($_POST['mid_content']) && $_POST['mid_content']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Inhalt erforderlich.';
		}
		else if(!isset($_POST['product_details']) || (isset($_POST['product_details']) && empty($_POST['product_details'])))
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Produkt erforderlich.';
		}
		else if(!isset($_POST['payment_type']) || (isset($_POST['payment_type']) && empty($_POST['payment_type'])))
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Paymenttyp erforderlich.';
		}
		else
		{
			//$find_pdf_reference_no=find('first', PDF, 'id, reference_no', "WHERE reference_no=:reference_no", array(':reference_no'=>stripcleantohtml($_POST['reference_no'])));
			//if(empty($find_pdf_reference_no))
			//{
				//print_r($_POST);
				$product_ids=implode(",", $_POST['product_details']);
				$selected_product_list=find('all', PRODUCT, '*', "WHERE status=:status AND id IN (".$product_ids.") ORDER BY id ASC ", array(':status'=>'Y'));
				$added_product_list=array();
				if(!empty($selected_product_list))
				{
					foreach($selected_product_list as $sel_key=>$sel_val)
					{
						if(isset($_POST['product_qty'][$sel_val['id']]) && $_POST['product_qty'][$sel_val['id']]!="")
						{
							$selected_product_list[$sel_key]['qty']=$_POST['product_qty'][$sel_val['id']];
							$added_product_list[$sel_val['id']]=$selected_product_list[$sel_key];
						}
					}
				}
				$date_obj=date_create_from_format("d.m.Y", $_POST['pdf_date']);
				$pdf_date=date_format($date_obj, "Y-m-d");
				$product_details=json_encode($added_product_list, JSON_UNESCAPED_UNICODE);
				$payment_type=implode(", ", $_POST['payment_type']);
				$fields="reference_no, pdf_date, no_of_pages, editor_name, client_name, left_address, mid_content, product_details, payment_type, product_total, tax_total, grand_total";
				$values=":reference_no, :pdf_date, :no_of_pages, :editor_name, :client_name, :left_address, :mid_content, :product_details, :payment_type, :product_total, :tax_total, :grand_total";
				$execute=array(
					':reference_no'=>stripcleantohtml($_POST['reference_no']),
					':pdf_date'=>$pdf_date,
					':no_of_pages'=>stripcleantohtml($_POST['no_of_pages']),
					':editor_name'=>($_POST['editor_name']),
					':client_name'=>($_POST['client_name']),
					':left_address'=>($_POST['left_address']),
					':mid_content'=>stripcleantohtml($_POST['mid_content']),
					':product_details'=>$product_details,
					':payment_type'=>$payment_type,
					':product_total'=>stripcleantohtml($_POST['hidden_product_total']),
					':tax_total'=>stripcleantohtml($_POST['hidden_tax_total']),
					':grand_total'=>stripcleantohtml($_POST['hidden_grand_total'])
				);
				$add_pdf = save(PDF, $fields, $values,$execute);
				if($add_pdf > 0)
				{
					$invoice_no=generateinvoiceid($add_pdf);
					$update_pdf=update(PDF, "invoice_no=:invoice_no", 'WHERE id=:id', array(':id'=>$add_pdf, ':invoice_no'=>$invoice_no));
					$find_pdf= find('first', PDF, '*', "WHERE id=:id", array(':id'=>$add_pdf));
					$added_product_array=json_decode($find_pdf['product_details'], true);
					ob_start();
?>
					<table style="border:0px solid #999;vertical-align: top;font-size:14px;margin-top:100px;width:100%;font-family: Calibri;">
						<tr style="border-bottom: 0px solid #000;padding-bottom:20px;">
							<td style="font-size:14px;vertical-align: top;">
								Lorem Ipsum is simply dummy text of the printing and typesetting industry<br/><br/>
								<?php echo $find_pdf['client_name'];?><br/>
								<?php echo nl2br($find_pdf['left_address']);?>
							</td>
							<td align="left" style="font-size:14px;vertical-align: top;width:300px;text-align:left;">
								<h3>Rechnung</h3> 
								<table style="border:0px solid #999;vertical-align: top;width:100%;text-align:left;">
									<!-- <tr style="display:none;">
										<td style="padding-right:10px;">Rechnung Nr.:</td>
										<td><?php //echo $find_pdf['reference_no'];?></td>
									</tr> -->
									<tr>
										<td style="padding-right:10px;">Datum:</td>
										<td><?php echo change_date_format($find_pdf['pdf_date']);?></td>
									</tr>
									<tr>
										<td style="padding-right:10px;">Seite:</td>
										<td><?php echo $find_pdf['no_of_pages'];?></td>
									</tr>
									<tr>
										<td style="padding-right:10px;">Kunden-Nr.:</td>
										<td><?php echo $find_pdf['invoice_no'];?></td>
									</tr>
									<tr>
										<td style="padding-right:10px;font-size:16px;"><strong>Name of editor:</strong></td>
										<td style="font-size:16px;"><?php echo $find_pdf['editor_name'];?></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div style="height:70px">&nbsp;<br/></div>
								<?php echo $find_pdf['mid_content'];?>
								<div style="height:30px">&nbsp;<br/></div>
							</td>
						</tr>
						<?php
						if(!empty($added_product_array))
						{
						?>
						<tr>
							<td colspan="2">
								<table style="border:0px solid #999;vertical-align: top;width:100%;text-align:left;">
									<tr>
										<td style="border-bottom:1px solid #999;font-weight:bold;">POS</td>
										<td style="border-bottom:1px solid #999;font-weight:bold;">Bezeichung</td>
										<td style="border-bottom:1px solid #999;font-weight:bold;">Menge me</td>
										<td style="border-bottom:1px solid #999;font-weight:bold;">EP</td>
										<td style="border-bottom:1px solid #999;font-weight:bold;">Gssmt</td>
										<td style="border-bottom:1px solid #999;font-weight:bold;">Mwst</td>
									</tr>
						<?php
							$sl_no=0;
							foreach($added_product_array as $product_key=>$product_val)
							{
								$sl_no++;
								$each_product_total=$product_val['qty']*$product_val['price'];
						?>
									<tr>
										<td style="padding:10px 0px;"><?php echo $sl_no;?></td>
										<td style="padding:10px 0px;">
											Artikel-Nr.: <?php echo $product_val['item_no'];?><br/>
											<?php echo $product_val['item_name'];?>
										</td>
										<td style="padding:10px 0px;"><?php echo $product_val['qty']."stck";?></td>
										<td style="padding:10px 0px;">&euro;<?php echo number_format($product_val['price'], 2, ",", "");?></td>
										<td style="padding:10px 0px;">&euro;<?php echo number_format($each_product_total, 2, ",", "");?></td>
										<td style="padding:10px 0px;">
											<?php echo ($product_val['tax_type']=="F" ? "&euro;" : "").$product_val['tax'].($product_val['tax_type']=="P" ? "%" : "");?>
										</td>
									</tr>
						<?php
							}
						?>
									<tr>
										<td colspan="4" style="text-align:right;font-weight:bold;padding:10px 0px;">Gesamtsumme netto:</td>
										<td colspan="2" style="border-top:1px solid #999;padding:10px 0px;text-align:right;"><?php echo number_format($find_pdf['product_total'], 2, ",", "");?> EUR</td>
									</tr>
									<tr>
										<td colspan="4" style="text-align:right;font-weight:bold;padding:10px 0px;">Mwst:</td>
										<td colspan="2" style="padding:10px 0px;text-align:right;"><?php echo number_format($find_pdf['tax_total'], 2, ",", "");?> EUR</td>
									</tr>
									<tr>
										<td colspan="4" style="text-align:right;font-weight:bold;padding:10px 0px;">Gesamtsumme brutto:</td>
										<td colspan="2" style="border-top:1px solid #999;border-bottom:1px solid #999;padding:10px 0px 0px;text-align:right;">
											<?php echo number_format($find_pdf['grand_total'], 2, ",", "");?> EUR
											<hr style="border-color: #999;margin-top:10px;margin-bottom:2px;">
										</td>
								</table>
							</td>
						</tr>
						<?php
						}
						?>
						<tr>
							<td colspan="2" style="font-size:18px;">
								<div style="height:60px">&nbsp;</div>
								<strong>Paymenttyp:</strong> <?php echo $find_pdf['payment_type'];?>
								<div style="height:30px">&nbsp;</div>
							</td>
						</tr>
					</table>
<?php
					$html=ob_get_clean();
					$mpdf=new mPDF('c','', 0, '', 10, 10, 16, 16, 10, 10, ''); //mpdf 6
					//$mpdf=new \Mpdf\Mpdf(array('','', 0, '', 5, 5, 16, 16, 0, 0, ''));//mpdf 7
					$mpdf->setAutoTopMargin='stretch';
					$mpdf->setAutoBottomMargin='stretch';
					$mpdf->SetHTMLHeader($pdf_header);
					$mpdf->SetHTMLFooter ($pdf_footer);
					$mpdf->WriteHTML($html);
					$mpdf->Output('pdf_folder/pdf_'.$find_pdf['id'].'.pdf','F');
					$_SESSION['SET_TYPE'] = 'success';
					$_SESSION['SET_FLASH'] = 'Rechnungsdaten erfolgreich hinzugefügt.';
					header('location:'.DOMAIN_NAME_PATH.'pdf-data-list.php');
					exit;
				}
				else
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&uuml;ter.';
				}
			/*}
			else
			{
				if($find_pdf_reference_no['reference_no']==$_POST['reference_no'])
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Rechnung Nr. ist bereits vorhanden.';
				}
				else
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&uuml;ter.';
				}
			}*/
		}
	}
	$payment_type_array=array("Cash", "E-cash", "Bank Transfer");
	$find_product_list=find('all', PRODUCT, '*', "WHERE status=:status ORDER BY item_name ASC ", array(':status'=>'Y'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('includes/header.php');?>
	<script src="<?php echo DOMAIN_NAME_PATH;?>ckeditor/ckeditor.js"></script>
	<script type="text/javascript">
		CKEDITOR.config.autoParagraph = false;
		CKEDITOR.config.protectedSource.push(/<i[^>]*><\/i>/g);
		CKEDITOR.config.allowedContent = true;
	</script>
	<script type="text/javascript">
	<!--
		$(function(){
			$("#add_pdf").validationEngine();
			$('#pdf_date').datetimepicker({
				timepicker:false,
				format:'d.m.Y',
				//formatDate:'d.m.Y',
				//minDate:'-1970/01/01', //yesterday is minimum date(for today use 0 or -1970/01/01)
				//maxDate:'+1970/01/01' // and tommorow is maximum date calendar
			});
			$(".product_checkbox").click(function(){
				total_calculation_fun();
			});
			$("#client_name").keyup(function(){
				$("#client_name").addClass('person_name_rotate');
				$.ajax({
					type:'post',
					url: "<?php echo(DOMAIN_NAME_PATH);?>find_pdf_contact_name.php",
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
		function total_calculation_fun()
		{
			var product_total=0.00;
			var tax_total=0.00;
			var grand_total=0.00;
			$('input:checkbox.product_checkbox').each(function () {
				if(this.checked && $(this).val()!="")
				{
					var product_id = (this.checked ? $(this).val() : "");
					var product_price = (this.checked ? $(this).attr("data-price") : "0.00");
					var product_tax_type = (this.checked ? $(this).attr("data-tax_type") : "P");
					var product_tax = (this.checked ? $(this).attr("data-tax") : "0.00");
					var product_qty=($('#qty_'+product_id).val() ? $('#qty_'+product_id).val() : 1);
					var each_product_price=eval(product_price)*eval(product_qty);
					if(product_tax_type=="F")
					{
						var each_tax_price=eval(product_tax);
					}
					else
					{
						var each_tax_price=eval(each_product_price)*eval(product_tax)/100;
					}
					var each_grand_price=eval(each_tax_price)+eval(each_product_price);
				}
				else
				{
					var each_product_price=0.00;
					var each_tax_price=0.00;
					var each_grand_price=0.00;
				}
				product_total=eval(product_total)+eval(each_product_price);
				tax_total=eval(tax_total)+eval(each_tax_price);
				grand_total=eval(grand_total)+eval(each_grand_price);
			});
			$("#product_total").html('&euro;'+product_total.toFixed(2));
			$("#tax_total").html('&euro;'+tax_total.toFixed(2));
			$("#grand_total").html('&euro;'+grand_total.toFixed(2));
			$("#hidden_product_total").val(product_total.toFixed(2));
			$("#hidden_tax_total").val(tax_total.toFixed(2));
			$("#hidden_grand_total").val(grand_total.toFixed(2));
		}
		function product_qty_fun(cur)
		{
			if(cur.val()!="" && !isNaN(cur.val()))
			{
				total_calculation_fun();
			}
		}
		function value_send(value, address)
		{
			$("#client_name").val(value);
			$("#left_address").val(address.replace(/@@/g, "\r\n"));
			$("#auto_sug_content_div").hide();
			$("#client_name").focus();
		}
	//-->
	</script>
	<style type="text/css">
		.person_name_rotate{ background:#F7F7F7 url('img/ajax-loader.gif') right center no-repeat !important; }
		.auto_sugg_cls{max-height:165px;overflow:auto;width:97%;top:62px;position:absolute;border: 1px solid rgb(204, 204, 204);border-radius:2px;background:#FFFFFF;display:none;height:auto;z-index:99999999;}
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
					<h3 class="panel-title">Neues Rechnungsdaten erstellen</h3>
				</div>
				<div class=" content">
					<div class="col-md-3">&nbsp;</div>
					<div class="col-md-6">
						<form id="add_pdf" name="add_pdf" method="post" enctype="multipart/form-data" action="">
							<div class="form-group" style="padding:6px;text-align:left;">
								<font color = "red">*</font> Felder sind Pflichtfelder.
							</div>
							<div class="form-group" style="padding:6px;position:relative;">
							  <label><font color = "red">*</font> Kundenname:</label>
							  <input class="form-control validate[required]" placeholder="Kundenname" type="text" id="client_name" name="client_name" maxlength="255"  data-errormessage-value-missing="Kundenname ist erforderlich" value="<?php echo((isset($_POST['client_name']) && $_POST['client_name']!='') ? $_POST['client_name'] : "");?>" autocomplete="off"/>
							  <div class="auto_sugg_cls" id="auto_sug_content_div"></div>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Adresse:</label>
							  <textarea name="left_address" class="notes_cls form-control validate[required]" Placeholder="Adresse" data-errormessage-value-missing="Adresse ist erforderlich" id="left_address" tabindex="1"><?php echo(isset($_POST['left_address']) && $_POST['left_address']!="" ? $_POST['left_address'] : "");?></textarea>
							</div>
							<div class="form-group" style="padding:6px;display:none;">
							  <label><font color = "red">*</font> Rechnung Nr.:</label>
							  <input class="form-control validate[required]" placeholder="Rechnung Nr." type="text" id="reference_no" name="reference_no" maxlength="255"  data-errormessage-value-missing="Rechnung Nr. erforderlich" tabindex="2" value="<?php echo((isset($_POST['reference_no']) && $_POST['reference_no']!='') ? $_POST['reference_no'] : "");?>"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Datum:</label>
							  <input class="form-control validate[required]" placeholder="Datum" type="text" id="pdf_date" name="pdf_date" maxlength="10"  data-errormessage-value-missing="Datum erforderlich" tabindex="3" value="<?php echo((isset($_POST['pdf_date']) && $_POST['pdf_date']!='') ? $_POST['pdf_date'] : date("d.m.Y"));?>" readonly/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Seite:</label>
							  <input class="form-control validate[required, custom[integer]]" placeholder="Seite" type="text" id="no_of_pages" name="no_of_pages" maxlength="11"  data-errormessage-value-missing="Seite erforderlich" tabindex="4" value="<?php echo((isset($_POST['no_of_pages']) && $_POST['no_of_pages']!='') ? $_POST['no_of_pages'] : "");?>"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Herausgeber:</label>
							  <input class="form-control validate[required]" placeholder="Herausgeber" type="text" id="editor_name" name="editor_name" maxlength="255"  data-errormessage-value-missing="Herausgeber erforderlich" tabindex="5" value="<?php echo((isset($_POST['editor_name']) && $_POST['editor_name']!='') ? $_POST['editor_name'] : "");?>"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Inhalt:</label>
							  <textarea type="text" name="mid_content" id="mid_content" class="ckeditor" style="width:100%;padding:10px;height: 100px;" tabindex="6"><?php echo((isset($_POST['mid_content']) && $_POST['mid_content']!='') ? $_POST['mid_content'] : "");?></textarea>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Produkt:</label>
							<?php
								foreach($find_product_list as $product_list_key=>$product_list_val)
								{
							?>
							  <div id="" class="">
									<div class="col-md-4" style="padding-top: 10px;">
										<input type="checkbox" id="product_<?php echo $product_list_val['id'];?>" name="product_details[<?php echo $product_list_val['id'];?>]" value="<?php echo $product_list_val['id'];?>" <?php echo(isset($_POST['product_details']) && in_array($product_list_val['id'], $_POST['product_details']) ? "checked='checked'" : "");?> style="margin-top:0px;" class="product_checkbox validate[groupRequired[product]]" data-price="<?php echo(number_format($product_list_val['price'],2));?>" data-tax_type="<?php echo $product_list_val['tax_type'];?>" data-tax="<?php echo $product_list_val['tax'];?>">&nbsp;&nbsp;<em><?php echo $product_list_val['item_no'].". ".$product_list_val['item_name'];?></em>
									</div>
									<div class="col-md-3" style="padding-top: 10px;">€&nbsp;<?php echo(number_format($product_list_val['price'],2));?></div>
									<div class="col-md-5">
										Anzahl: <input type="text" id="qty_<?php echo $product_list_val['id'];?>" name="product_qty[<?php echo $product_list_val['id'];?>]" value="<?php echo(isset($_POST['product_qty']) && isset($_POST['product_qty'][$product_list_val['id']]) && $_POST['product_qty'][$product_list_val['id']]!="" ? $_POST['product_qty'][$product_list_val['id']] : 1);?>" class="form-control" style="width:60px;display: inline-block;" onchange="product_qty_fun($(this))">
									</div>
									<div class="clearfix"></div>
							  </div>
							<?php
								}
							?>
							</div>
							<div class="form-group" style="padding:6px">
								<label>
									Gesamtsumme netto: 
									<span id="product_total"><?php echo((isset($_POST['hidden_product_total']) && $_POST['hidden_product_total']!='') ? "&euro;".$_POST['hidden_product_total'] : "");?></span>
									<input type="hidden" name="hidden_product_total" id="hidden_product_total" value="<?php echo((isset($_POST['hidden_product_total']) && $_POST['hidden_product_total']!='') ? $_POST['hidden_product_total'] : "");?>">
								</label><br/>
								<label>
									Mwst: 
									<span id="tax_total"><?php echo((isset($_POST['hidden_tax_total']) && $_POST['hidden_tax_total']!='') ? "&euro;".$_POST['hidden_tax_total'] : "");?></span>
									<input type="hidden" name="hidden_tax_total" id="hidden_tax_total" value="<?php echo((isset($_POST['hidden_tax_total']) && $_POST['hidden_tax_total']!='') ? $_POST['hidden_tax_total'] : "");?>">
								</label><br/>
								<label>
									Gesamtsumme brutto: 
									<span id="grand_total"><?php echo((isset($_POST['hidden_grand_total']) && $_POST['hidden_grand_total']!='') ? "&euro;".$_POST['hidden_grand_total'] : "");?></span>
									<input type="hidden" name="hidden_grand_total" id="hidden_grand_total" value="<?php echo((isset($_POST['hidden_grand_total']) && $_POST['hidden_grand_total']!='') ? $_POST['hidden_grand_total'] : "");?>">
								</label><br/>
							</div>
							<div class="form-group" style="padding:6px">
								<label><font color = "red">*</font> Paymenttyp:</label><br/>
								<?php
									foreach($payment_type_array as $type_key=>$type_val)
									{
								?>
								<input type="checkbox" id="payment_type_<?php echo $type_key;?>" name="payment_type[]" value="<?php echo $type_val;?>" <?php echo(isset($_POST['payment_type']) && in_array($type_val, $_POST['payment_type']) ? "checked='checked'" : "");?> style="margin-top:0px;" class="validate[groupRequired[paymenttype]]">&nbsp;&nbsp;<em><?php echo $type_val;?></em>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<?php
									}
								?>
							</div>
							<div class="form-group" style="padding:3px;text-align:right;">
							  <button class="button" name = "create_pdf" tabindex="7"><b>Erstellen neuer Rechnungsdaten</b></button>
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