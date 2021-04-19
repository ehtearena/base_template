<div style='width:100%; display:table;'>
	<div style="float:left">
		<?php
			if (isset($_SESSION['user_level']) && ($_SESSION['user_level'] == 1 or $_SESSION['user_level'] == 2))
			{
		?>
				<a href="<?php echo $_SESSION['boot']->appPublic;?>user">Profile Management </a>&nbsp;
				
				<?php 
				if (isset($_SESSION['consult_ID']))
				{
				?>
				&nbsp;&nbsp;
				<a href="<?php echo $_SESSION['boot']->appPublic."consultant/editRecord?Create=Edit+Profile&id=".$_SESSION['consult_ID']; ?>">Edit Profile</a>&nbsp;
				<?php 
				}	
				?>
				<?php		
			}
			if (isset($_SESSION['user_level']) && ($_SESSION['user_level'] > 2))
			{
		?>
				<a href="<?php echo $_SESSION['boot']->appPublic;?>user">Management Portal</a>&nbsp;
		<?php		
			}
			?>
	</div>
	<div style="float:right">
	<a target="_blank" href="<?php echo $_SESSION['boot']->appPublic;?>help/acronyms">Common Acronyms</span>&nbsp;
	<a target="_blank" href="<?php echo $_SESSION['boot']->appPublic;?>help">Help</a>&nbsp;
	</div>
</div>
