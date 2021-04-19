<?php
if (isset($_GET['forUser']) == FALSE)
{
?>
<form>
<fieldset>
<legend>Management Portal</legend>
<ul>
<?php if (checkRights($_SESSION['user_level'],'process','')) echo "<li><a title='Manage the entire procurement process.' href='".$_SESSION['boot']->getAppPublic()."process'>Process</a>"; ?>
<?php if (checkRights($_SESSION['user_level'],'terms_of_reference','')) echo "<li><a title='Create or Edit a TOR for later use in a process.' href='".$_SESSION['boot']->getAppPublic()."terms_of_reference'>Terms of Reference</a>"; ?>
<?php if (checkRights($_SESSION['user_level'],'specifications_document','')) echo "<li><a title='Create or Edit a Specification Document for later use in a process.' href='".$_SESSION['boot']->getAppPublic()."specifications_document'>Specifications Document</a>"; ?>
<?php if (checkRights($_SESSION['user_level'],'consultant','')) echo "<li><a title='Create or Edit either a Company or Individual consultant for later use in a process.' href='".$_SESSION['boot']->getAppPublic()."consultant'>Consultant/Supplier</a>"; ?>
<?php if (checkRights($_SESSION['user_level'],'contract','')) echo "<li><a title='Create or Edit a Contract for later use in a process.' href='".$_SESSION['boot']->getAppPublic()."contract'>Contract</a>"; ?>
<?php if (checkRights($_SESSION['user_level'],'criteria','')) echo "<li><a title='Set up EOI/ITT shortlist criteria for later use in a process.' href='".$_SESSION['boot']->getAppPublic()."process_criteria'>Criteria</a>"; ?>
<?php if (checkRights($_SESSION['user_level'],'panel','')) echo "<li><a title='Set up EOI/ITT shortlist panel for later use in a process.' href='".$_SESSION['boot']->getAppPublic()."process_panel'>Panel</a>"; ?>
<?php if (checkRights($_SESSION['user_level'],'performance_evaluation','')) echo "<li><a title='Manage performance evaluations for existing contracts.' href='".$_SESSION['boot']->getAppPublic()."performance_evaluation'>Performance Evaluation</a>"; ?>
<?php if (checkRights($_SESSION['user_level'],'roster','')) echo "<li><a title='View/Query the Roster.' href='".$_SESSION['boot']->getAppPublic()."roster'>Roster</a>"; ?>
<?php if (checkRights($_SESSION['user_level'],'admin','index')) echo "<li><a title='Manage system parameters, users, currencies etc.' href='".$_SESSION['boot']->getAppPublic()."admin'>System Administration</a>"; ?>
<?php if (checkRights($_SESSION['user_level'],'reports','')) echo "<li><a title='Generate Reports in either PDF, Excel or XML.' href='".$_SESSION['boot']->getAppPublic()."reports'>Reports</a>"; ?>
<?php if (checkRights($_SESSION['user_level'],'process','')) echo "<li><a title='Active Process Dashboard.' href='".$_SESSION['boot']->getAppPublic()."process/checklist?dashboard=true&submit=View+Dashboard'>Active Process Dashboard</a>"; ?>
<?php if (checkRights($_SESSION['user_level'],'process','')) echo "<li><a title='Archived Process Dashboard.' href='".$_SESSION['boot']->getAppPublic()."process/checklist?dashboard=true&submit=View+Dashboard&archive=1'>Archived Process Dashboard</a>"; ?>


</ul>
</fieldset>
</form>
<?php
}
?>
