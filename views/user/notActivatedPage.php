<form id="activation_form" action='<?php echo $_SESSION['boot']->getAppPublic(); ?>user/resendActivation' method="post">
<input type="hidden" name="zetoken" value="<?php echo $_SESSION['zetoken']; ?>">
<fieldset>
<legend>Activation</legend>
	<table>
	<tr>
		<td>Email Address:</td><td><input type="text" name="emailAddress" class="required email" value=""></td>
	</tr>
	<tr>	
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>	
		<td colspan="2"><input type="submit" name="submit" value="Resend Activation Email"></td>
	</tr>
	</table>
</fieldset>
</form>

