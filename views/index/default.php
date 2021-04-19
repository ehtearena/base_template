<script>
function selectUpdate(e,el)
{
}
</script>

<?php
	if (!isset($view[0]) or (isset($view[0]) && !isset($view[0]->message) && !isset($view[0]->error)))
	{
		echo $view;
	}
?>
