<style>
	.ui-widget-content span { color: #999; }
	#table_title { clear: both;}
	#feedback { font-size: 14px; }
	#clause, #from-clause { color: #005500; }
	.selectablex .ui-selecting { background: #FECA40; }
	.selectablex .ui-selected { background: #F39814; color: white; }
	.selectablex { list-style-type: none; margin: 0; padding: 0;}
	.selectablex li { margin: 2px; padding: 2px; font-size: 11px; height: 18px; cursor:pointer; cursor: hand }

</style>

<script>

	curr = new Array();
	
	function stopped()
	{
				var table = "";
				var result = jQuery( "#select-result" ).empty();
				i = 0
				jQuery(".ui-selected").each(function() 
				{
					var index = jQuery("#select" + (jQuery( "#selectable li" ).index( this )+1)).html();
					var thisTable = jQuery("#select" + (jQuery( "#selectable li" ).index( this )+1)).attr('alt');
					if (jQuery('#clause').html() == "WHERE")
					{
						if (i > 0) result.append(" = ");
					}
					else
					{
						if (i > 0) result.append(", ");
						
					}
					result.append(index);
					i++;
				
				
					if (table.indexOf(thisTable) == -1)
					{
						if (table == "")
						{
							table = thisTable;
						}
						else
						{
							table = table + ", " + thisTable;
						}
					}
				});
				
				if (jQuery('#clause').html() != "WHERE")
				{
					jQuery( "#from-result").html(table);				
				}
	}

	jQuery(function() 
	{
		jQuery( "#selectable" ).selectable(
		{
			selected: function(event, ui) 
			{
				var sp = ", ";
				vl = jQuery( "#select-result" ).html();

				var e = jQuery(ui.selected);

				if (vl == "")
				{
					sp = "" ;
				}
				
				vl = vl + sp + jQuery(e).html();
				jQuery( "#select-result" ).html(vl);
				
			},
			unselected: function(event, ui) 
			{
				vl = jQuery( "#select-result" ).html();
				var e = jQuery(ui.unselected).html();
				if (vl.indexOf(", "+e) != -1)
				{
					vl = vl.replace(", "+e,"");										
				}
				if (vl.indexOf(e) != -1)
				{
					vl = vl.replace(e,"");										
				}
				jQuery( "#select-result" ).html(vl);
			}
		});
	});
</script>

<form>
<fieldset>
<legend>Query Generator</legend>
	
	<p id="feedback">
	<span id="message">Select multiple fields by clicking on them while pressing "CTRL".</span>
	</p>
	
	<div style="display:table">
		<div style="float:left">
			<ol id="selectable" class="selectablex">
			
<?php
$letters = range('a', 'z');
$letters_sub = range('a', 'z');
$cnt = 1;
$tbl = 0;
$tbl_sub = 0;

$sql = "SHOW TABLES FROM ".$_SESSION['boot']->getDatabase();
$result = mysql_query($sql) or myDie($sql);
while ($row = mysql_fetch_row($result)) 
{
    echo "<div style='width: 200px; float:left' id='table_title'>".$row[0]."</div>";
	foreach (db_fetch_columns($row[0],false) as $col)
	{
		echo "<li style='float:left' alt='".$row[0]." as ".$letters[$tbl].$letters_sub[$tbl_sub]."' class='ui-widget-content' id='select$cnt'>".$letters[$tbl].$letters_sub[$tbl_sub].".$col</li>";
		$cnt++;
	}
	$tbl_sub++;

	if ($tbl_sub == 26)
	{
		$tbl_sub = 0;
		$tbl = $tbl+1;
	}
}
?>



			</ol>
		</div>
	</div>
</fieldset>
</form>
	
<BR><BR>
<form id="genform" action="<?php echo $_SESSION['boot']->getAppPublic(); ?>reports/generate" method="POST">
<fieldset>
<legend>Filters</legend>
Name (for saving): <input size="60" type="text" name="db_reports_c_name" value=""><BR><BR>
User Rights Level: <input size="10" type="text" name="db_reports_c_rights" value=""><BR><BR>
<input type="submit" name="submit" value="Generate">
</fieldset>
</form>

