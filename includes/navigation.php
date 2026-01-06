	<div class="col-md-2">
		<div class="panel panel-primary">
		  <div class="panel-heading">
			<h3 class="panel-title">Navigation</h3>
		  </div>
		  <div class=" content">
			<?php
				if($admin_privilege==true || $branch_privilege==true)
				{
			?>
			<div class="add_new">
				Kontakt
			</div>
			<p class="cate <?php echo($current_page_name=="create_user.php" ? "active_menu" : "");?>"><a href = "create_user.php"><img src="img/contact-new.png" width="20" height="20" border="0" alt="">&nbsp;&nbsp;Neuen Kontakt erstellen</a></p>
			<p class="cate <?php echo($current_page_name=="listing.php" ? "active_menu" : "");?>"><a href = "listing.php"><img src="img/user-group-128.png" width="20" height="20" border="0" alt="">&nbsp;&nbsp;Kontakte auflisten</a></p>
			<?php
				}
			?>
			<div class="add_new">
				Bemerkungstafel
			</div>
			<?php
				if($admin_privilege==true || $branch_privilege==true)
				{
			?>
			<p class="cate <?php echo($current_page_name=="create_notice.php" ? "active_menu" : "");?>"><a href = "create_notice.php"><img src="img/contact-new.png" width="20" height="20" border="0" alt="">&nbsp;&nbsp;Neue Bemerkung erstellen</a></p>
			<?php
				}
			?>
			<p class="cate <?php echo($current_page_name=="notice.php" ? "active_menu" : "");?>"><a href = "notice.php"><img src="img/user-group-128.png" width="20" height="20" border="0" alt="">&nbsp;&nbsp;Bemerkung auflisten</a></p>
			<?php
				if($admin_privilege==true)
				{
			?>
			<div class="add_new">
				Filiale
			</div>
			<p class="cate <?php echo($current_page_name=="create_branch.php" ? "active_menu" : "");?>"><a href = "create_branch.php"><img src="img/contact-new.png" width="20" height="20" border="0" alt="">&nbsp;&nbsp;Neue Filiale hinzuf&uuml;gen</a></p>
			<p class="cate <?php echo($current_page_name=="branch.php" ? "active_menu" : "");?>"><a href = "branch.php"><img src="img/user-group-128.png" width="20" height="20" border="0" alt="">&nbsp;&nbsp;Filiale auflisten</a></p>
			<?php
				}
			?>
			<div class="add_new">
				Terminverwaltung
			</div>
			<?php
				if($admin_privilege==true || $branch_privilege==true)
				{
			?>
			<p class="cate <?php echo($current_page_name=="create_appointment.php" ? "active_menu" : "");?>"><a href = "create_appointment.php"><img src="img/Actions-appointment-new-icon.png" width="20" height="20" border="0" alt="">&nbsp;&nbsp;Neue Termin erstellen</a></p>
			<?php
				}
			?>
			<p class="cate <?php echo($current_page_name=="appointment.php" ? "active_menu" : "");?>"><a href = "appointment.php"><img src="img/schedule.png" width="20" height="20" border="0" alt="">&nbsp;&nbsp;Termine auflisten</a></p>
			<!-- <p class="cate"><img src="img/smsindiahub-Bulk-sms-gateway-service.png" width="20" height="20" border="0" alt="">&nbsp;&nbsp;Short Message</p> -->
			<?php
				if($admin_privilege==true)
				{
			?>
			<div class="add_new">
				Verlaufsprotokoll
			</div>
			<p class="cate <?php echo($current_page_name=="log_history.php" ? "active_menu" : "");?>"><a href = "log_history.php"><img src="img/schedule.png" width="20" height="20" border="0" alt="">&nbsp;&nbsp;Listen von Logs</a></p>
			<?php
				}
			?>
			<?php
				if($admin_privilege==true || $branch_privilege==true)
				{
			?>
			<div class="add_new">
				Andere
			</div>
			<?php
				}
			?>
			<?php
				if($admin_privilege==true)
				{
			?>
			<p class="cate <?php echo($current_page_name=="pdf-data-list.php" ? "active_menu" : "");?>"><a href = "pdf-data-list.php"><img src="img/pdf.png" width="20" height="20" border="0" alt="">&nbsp;&nbsp;Rechnung Erstellen</a></p>
			<p class="cate <?php echo($current_page_name=="payment-list.php" ? "active_menu" : "");?>"><a href = "payment-list.php"><img src="img/payment-icon.png" width="20" height="20" border="0" alt="">&nbsp;&nbsp;Offene Zahlungen</a></p>
			<?php
				}
			?>
			<?php
				if($admin_privilege==true || $branch_privilege==true)
				{
			?>
			<p class="cate <?php echo($current_page_name=="call-back-list.php" ? "active_menu" : "");?>"><a href = "call-back-list.php"><img src="img/phone.png" width="20" height="20" border="0" alt="">&nbsp;&nbsp;Anrufnotiz</a></p>
			<?php
				}
			?>
			<?php
				if($admin_privilege==true)
				{
			?>
			<p class="cate <?php echo($current_page_name=="product-list.php" ? "active_menu" : "");?>"><a href = "product-list.php"><img src="img/product-icon.png" width="20" height="20" border="0" alt="">&nbsp;&nbsp;Produkt</a></p>
			<?php
				}
			?>
			<div class="add_new">
				Profil verwalten
			</div>
			<?php
				if($admin_privilege==true)
				{
			?>
			<p class="cate <?php echo($current_page_name=="edit_profile.php" ? "active_menu" : "");?>"><a href = "edit_profile.php"><img src="img/profile.png" width="20" height="20" border="0" alt="">&nbsp;&nbsp;Profil bearbeiten</a></p>
			<?php
				}
			?>
			<p class="cate <?php echo($current_page_name=="logout.php" ? "active_menu" : "");?>"><a href = "logout.php"><img src="img/logout.png" width="20" height="20" border="0" alt="">&nbsp;&nbsp;ausloggen</a></p>
		  </div>
		</div>
	</div>