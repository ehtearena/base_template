<form>
<fieldset>
<legend>Reports Portal</legend>
<ul>
<?php 
foreach ($view[1] as $v)
{
	$id = $v['id'];
	echo "<li><a href='".$_SESSION['boot']->getAppPublic()."reports/generateExistingReport?id=$id'>".$v['c_name']."</a>";
}

echo "<li><a target='_blank' href='".$_SESSION['boot']->getAppPublic()."reports/render?custom=1001'>All Processes</a>";
echo "<li><a target='_blank' href='".$_SESSION['boot']->getAppPublic()."reports/render?custom=1002'>Evaluations of contracts and processes</a>";
echo "<li><a target='_blank' href='".$_SESSION['boot']->getAppPublic()."reports/render?custom=1003'>TMEA Advertisement Sources - by Process</a>";
echo "<li><a target='_blank' href='".$_SESSION['boot']->getAppPublic()."reports/render?custom=1004'>TMEA Advertisement Sources - by Referrer</a>";
echo "<li><a target='_blank' href='".$_SESSION['boot']->getAppPublic()."reports/render?custom=1005'>History of Changes on Process</a>";
?>

</ul>
</fieldset>
</form>

<?php 
if (checkRights($_SESSION['user_level'],'SPECIAL','generator')) 
{
?>
<form>
<fieldset>
<legend>Generator</legend>
<?php
echo "<ul><li><a href='".$_SESSION['boot']->getAppPublic()."reports/generator'>Query Generator</a></ul>"; 
?>
</fieldset>
</form>
<?php
}
?>
