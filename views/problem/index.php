<form autocomplete="off" id="problem_form" action='<?php echo $_SESSION['boot']->getAppPublic(); ?>problem/submit' method="post">
<fieldset>
<legend>Report a problem</legend>
<p>Use this form to report only technical issues. For all other queries, send an email to procurement@trademarkea.com.</p>
	<table>
	<tr>
		<td>Email Address:</td><td><input autocomplete="off" type="text" name="email" class="required" value=""></td><td></td>
	</tr>
	<tr>
		<td>Name:</td><td><input autocomplete="off" type="text" name="name" class="required" value=""></td><td></td>
	</tr>
	<tr>
		<td>Message: </td><td></td><td></td>
	</tr>
	<tr>
		<td colspan="3"><textarea cols="50" rows="5" name="message" class="required"></textarea></td>
	</tr>
	<tr>	
		<td colspan="3"><input type="submit" name="submit" value="Submit"></td>
	</tr>
	</table>
</fieldset>
</form>

