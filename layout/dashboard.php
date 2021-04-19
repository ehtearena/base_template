<?php
date_default_timezone_set('Africa/Nairobi');
?>

<!DOCTYPE HTML>
<html>
<head>
	<title>TradeMark East Africa | Contracts Management</title>
	<meta name="description" content="Procurement Management System" />
	<meta name="keywords" content="procurement management, procurement, TMEA, TradeMark, East Africa" />
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

	<link rel="icon" href="<?php echo $_SESSION['boot']->appPublic;?>favicon.ico" type="image/x-icon">
	<link rel="shortcut icon" href="<?php echo $_SESSION['boot']->appPublic;?>favicon.ico" type="image/x-icon"> 

	<link rel="stylesheet" type="text/css" href="<?php echo $_SESSION['boot']->appPublic;?>css/style.css" media="all" />
	<link rel="stylesheet" type="text/css" href="<?php echo $_SESSION['boot']->appPublic;?>css/redmond/jquery-ui-1.8.18.custom.css" media="all" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $_SESSION['boot']->appPublic;?>css/ui.jqgrid.css" />
	
	<script type="text/javascript" src="<?php echo $_SESSION['boot']->appPublic;?>js/jquery.tools.js"></script>
	
	<script type="text/javascript" src="<?php echo $_SESSION['boot']->appPublic;?>js/jquery-ui-1.8.21.custom.min.js"></script>

	<script type="text/javascript" src="<?php echo $_SESSION['boot']->appPublic;?>js/jquery.form.widget.js"></script>
	<script type="text/javascript" src="<?php echo $_SESSION['boot']->appPublic;?>js/jquery.validate.js"></script>
	<script type="text/javascript" src="<?php echo $_SESSION['boot']->appPublic;?>js/jquery.corner.js"></script>
	
	<script type="text/javascript" src="<?php echo $_SESSION['boot']->appPublic;?>js/i18n/grid.locale-en.js"></script>
	<script type="text/javascript" src="<?php echo $_SESSION['boot']->appPublic;?>js/jquery.jqGrid.min.js"></script>
	
    <script type="text/javascript" src="<?php echo $_SESSION['boot']->appPublic;?>js/fileuploader.js"></script>
	<script type="text/javascript" src="<?php echo $_SESSION['boot']->appPublic;?>ckeditor/ckeditor.js"></script>
	
	<script type="text/javascript">
		function imgError(image) {
		    image.onerror = "";
		    image.src = "<?php echo $_SESSION['boot']->appPublic;?>images/tmea_logo_small.gif";
		    return true;
		}
	
		jQuery(document).ready(function() 
		{
			if (top != self) { top.location.replace(self.location.href); }
			
			jQuery("form").form();
			jQuery(".frm").form();
			jQuery("form").validate();

			jQuery("#wireframe-inner").corner().parent().css('padding', '1px').corner();
			jQuery("form").find(":submit").removeClass('ui-state-disabled').unbind('click');
			jQuery("#footer").corner("bottom");
			
			jQuery("input[type=checkbox]:checked").each(function()
			{
			  jQuery(this).parent().next().addClass("ui-state-active");
			  jQuery(this).parent().next().children().addClass("ui-icon ui-icon-check");
			});
			setTimeout('loadComments()',2000);
			
			jQuery(".jtable th").each(function()
			{
				jQuery(this).addClass("ui-state-default");
			 
			});
			
			jQuery(".jtable td").each(function()
			{
				jQuery(this).addClass("ui-widget-content");
			});

			jQuery(".jtable tr").hover(
			function()
			{
				jQuery(this).children("td").addClass("ui-state-hover").css('font-weight','normal');
			},
			function()
			{
				jQuery(this).children("td").removeClass("ui-state-hover");
			}
			);
			jQuery(".jtable tr").click(function(){
			//	jQuery(this).children("td").toggleClass("ui-state-highlight");
			});
		});
		
		function loadComments()
		{
			jQuery("a[title], form input[title]").tooltip({
				offset: [10, 2],
				effect: 'slide'
			}).dynamic({ bottom: { direction: 'down', bounce: true } });

			jQuery("form div.qq-upload-button[title]").tooltip({
				offset: [10, 2],
				effect: 'slide'
			}).dynamic({ bottom: { direction: 'down', bounce: true } });

		}
	</script>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-45353641-1', 'trademarkea.com');
  ga('send', 'pageview');
</script>	
	
	
	</head>
<body>
		<div id="body">@CONTENT@</div>
		<div id="footer">
			EchoLogic &copy; 2012 <div style='float:right;'><a href='<?php echo $_SESSION['boot']->appPublic;?>problem/' style='text-decoration: underline;margin-right:20px;font-size: 16px;'>Report a problem with this page</a></div>
		</div>
</body>
</html>
	