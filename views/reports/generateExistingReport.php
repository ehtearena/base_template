<form id="generic_form" action='<?php echo $_SESSION['boot']->getAppPublic(); ?>reports/render' method="post">

<fieldset>
<legend>Fields</legend>
	<div class="container">
<?php
	$tbl = "";
	$cnt = 0;
	foreach ($view['values'] as $values)
	{
		if ($tbl != $view['tables'][$cnt])
		{
			if ($tbl == "") 
			{
				echo "<div";
			}
			else
			{
				echo "</div><div";
			}
			echo " style='font-size: 11px; padding: 10px; float:left; width: 410px;'>";
			$tbl = $view['tables'][$cnt];
			echo "<h3>".$view['tablenames'][$view['labels'][$cnt]]."</h3>";
		}

		if (!isset($_SESSION['db_relations_bk'][$tbl . "." . $view['columns'][$cnt]]))
		{
			echo "<div style='margin:0px 5px; display:table;'><div style='float:left; width: 185px'><input style='float:left' class='checkboxes' type=checkbox ";
			if (in_array($values, $view['chosen'])) 
			{
			    echo "CHECKED";
			}
			
			echo " name='cols[]' value='".$values."'>".formatFieldName($view['columns'][$cnt])."</input></div>";
	
				echo "<div style='float:right; height:25px;'>";
				echo "<select name='x_filter_".str_replace(".","@",$values)."'>";
				echo "<option value=''>No Filter</option>";
				echo "<option value='='>=</option>";
				echo "<option value='".urlencode("<")."'>&lt;</option>";
				echo "<option value='".urlencode(">")."'>&gt;</option>";
				echo "<option value='".urlencode("<=")."'>&lt;=</option>";
				echo "<option value='".urlencode(">=")."'>&gt;=</option>";
				echo "<option value='LIKE'>Contains</option>";
				echo "</select>";
				echo "<span></span>";
				echo "<span>&nbsp;</span>";
				echo "<input type='textbox' name='x_valuefilter_".str_replace(".","@",$values)."' value=''></div></div>";
		}
			
		$cnt ++;
	};

	
	?>
<BR><BR>

	</div>
</fieldset>
<BR><BR>
<fieldset>
<legend>PDF Orientation</legend>
	<div class="container">
		<select name="orientation">
			<option value="L">Landscape</option>
			<option value="P" selected>Portrait</option>
		</select>
	</div>
</fieldset>

<BR><BR>
<fieldset>
<legend>Output Type</legend>
	<div class="container">
		<select name="type">
			<option value="xml">XML</option>
			<option value="excel">Excel</option>
			<option value="pdf" selected >PDF</option>
		</select>
	</div>
</fieldset>
<BR><BR>
<fieldset>
<legend>Report Parameters</legend>
	<div class="container">
<?php
foreach ($view[1] as $v)
{
	echo "<div class='header del'>";
	echo "<span>".str_replace("_"," ",$v)."</span>"; //Formatting the field name
	echo "</div><div class='del'>";
	echo "<input type='text' name='".$v."' ";

	if (strpos($v, "_Date") !== FALSE) 
	{ 
			echo "class='date' value=''";
	}
	else
	{
		echo " value=''";
	}
	echo "></div><div style='clear:both'></div>";
}
echo "<input type=hidden value='".$view[2]."' name='id'>";
echo "<input type=submit value='Run' name='Run'>";
?>
	</div>
</fieldset>
</form>
