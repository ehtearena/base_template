<form id="passchange_form" action='<?php echo $_SESSION['boot']->getAppPublic(); ?>user/changePass' method="post">
	<input type="hidden" name="zetoken" value="<?php echo $_SESSION['zetoken']; ?>">
<fieldset>
<legend>Change Password</legend>
	<table>
	<tr>
		<td>Old Password:</td><td><input type="password" class="required" name="db_user_password" value=""></td>
	</tr>
	<tr>
		<td>New Password:</td><td><input type="password" class="required" name="password" value=""></td>
	</tr>
	<tr>
		<td>Confirm:</td><td><input type="password" class="required" name="password2" value=""></td>
	</tr>
	<tr>	
		<td colspan="2"><input type="submit" name="submit" value="Change"></td>
	</tr>
	</table>
</fieldset>
</form>

