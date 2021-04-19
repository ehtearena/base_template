<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>
            Home | <?php echo $_SESSION['boot']->appName; ?>
        </title>
        <meta name="description" content="Home">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no, minimal-ui">
        <!-- Call App Mode on ios devices -->
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <!-- Remove Tap Highlight on Windows Phone IE -->
        <meta name="msapplication-tap-highlight" content="no">
        <!-- base css -->
        <link id="vendorsbundle" rel="stylesheet" media="screen, print" href="/css/vendors.bundle.css">
        <link id="appbundle" rel="stylesheet" media="screen, print" href="/css/app.bundle.css">
        <link id="myskin" rel="stylesheet" media="screen, print" href="/css/skins/skin-master.css">
        <link id="customskin" rel="stylesheet" media="screen, print" href="/css/themes/cust-theme-12.css">

        <!-- Place favicon.ico in the root directory -->
        <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon/apple-touch-icon.png">
        <link rel="stylesheet" type="text/css" media="screen" href="/css/ui.jqgrid.css" />
        <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon/favicon-32x32.png">
        <link rel="mask-icon" href="/img/favicon/safari-pinned-tab.svg" color="#5bbad5">
        <link rel="stylesheet" media="screen, print" href="/css/handsontable.full.min.css">
        <link rel="stylesheet" media="screen, print" href="/css/formplugins/select2/select2.bundle.css">
        <link rel="stylesheet" media="screen, print" href="/css/formplugins/summernote/summernote.css">

        <!--link rel="stylesheet" media="screen, print" href="/css/bootstrap.min.css"-->
        <link rel="stylesheet" href="/css/blueimp-gallery.min.css" />
        <link rel="stylesheet" media="screen, print" href="/css/jquery.fileupload.css">
        <link rel="stylesheet" media="screen, print" href="/css/jquery.fileupload-ui.css">
        <!-- You can add your own stylesheet here to override any styles that comes before it
		<link rel="stylesheet" media="screen, print" href="css/your_styles.css">-->
        <script src="/js/fine-uploader.core.js"></script>
        <script src="/js/jquery-custom.js"></script>
        <script src="/js/handsontable.full.min.js"></script>
        <script src="/js/formplugins/select2/select2.bundle.js"></script>
        <!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
           <script src="/js/jquery.ui.widget.js"></script>
           <!-- The Templates plugin is included to render the upload/download listings -->
           <script src="/js/tmpl.min.js"></script>
           <!-- The Load Image plugin is included for the preview images and image resizing functionality -->
           <script src="/js/load-image.all.min.js"></script>
           <!-- The Canvas to Blob plugin is included for image resizing functionality -->
           <script src="/js/canvas-to-blob.min.js"></script>
           <!-- blueimp Gallery script -->
           <script src="/js/jquery.blueimp-gallery.min.js"></script>
           <!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
           <script src="/js/jquery.iframe-transport.js"></script>
           <!-- The basic File Upload plugin -->
           <script src="/js/jquery.fileupload.js"></script>
           <!-- The File Upload processing plugin -->
           <script src="/js/jquery.fileupload-process.js"></script>
           <!-- The File Upload image preview & resize plugin -->
           <script src="/js/jquery.fileupload-image.js"></script>
           <!-- The File Upload audio preview plugin -->
           <script src="/js/jquery.fileupload-audio.js"></script>
           <!-- The File Upload video preview plugin -->
           <script src="/js/jquery.fileupload-video.js"></script>
           <!-- The File Upload validation plugin -->
           <script src="/js/jquery.fileupload-validate.js"></script>
           <!-- The File Upload user interface plugin -->
           <script src="/js/jquery.fileupload-ui.js"></script>
           <!-- The main application script -->
           <script src="/js/demo.js"></script>
           <script src="/js/formplugins/summernote/summernote.js"></script>
           <script>
           (function(fn){
               if (!fn.map) fn.map=function(f){var r=[];for(var i=0;i<this.length;i++)r.push(f(this[i]));return r}
               if (!fn.filter) fn.filter=function(f){var r=[];for(var i=0;i<this.length;i++)if(f(this[i]))r.push(this[i]);return r}
           })(Array.prototype);
           </script>

    </head>

    <style>

    .was-validated .form-control:invalid + .select2-container > span > .select2-selection{
    	border-color:red !important;
    }

    .intable {
      height: 15px;padding: 0px 15px; margin-left:2px; font-size: 11px;
    }
    </style>
    <!-- The template to display files available for upload -->
    <script id="template-upload" type="text/x-tmpl">
      {% for (var i=0, file; file=o.files[i]; i++) { %}
          <tr class="template-upload {%=o.options.loadImageFileTypes.test(file.type)?' image':''%}">
              <td>
                  <span class="preview"></span>
              </td>
              <td>
                  <p class="name">{%=file.name%}</p>
                  <strong class="error text-danger"></strong>
              </td>
              <td>
                  <p class="size">Processing...</p>
                  <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
              </td>
              <td>
                  {% if (!o.options.autoUpload && o.options.edit && o.options.loadImageFileTypes.test(file.type)) { %}
                    <button class="btn btn-success edit" data-index="{%=i%}" disabled>
                        <i class="glyphicon glyphicon-edit"></i>
                        <span>Edit</span>
                    </button>
                  {% } %}
                  {% if (!i && !o.options.autoUpload) { %}
                      <button class="btn btn-primary start" disabled>
                          <i class="glyphicon glyphicon-upload"></i>
                          <span onmousedown='triggerListener(this)'>Start</span>
                      </button>
                  {% } %}
                  {% if (!i) { %}
                      <button class="btn btn-warning cancel">
                          <i class="glyphicon glyphicon-ban-circle"></i>
                          <span>Cancel</span>
                      </button>
                  {% } %}
              </td>
          </tr>
      {% } %}
    </script>
    <!-- The template to display files available for download -->
    <script id="template-download" type="text/x-tmpl">
      {% for (var i=0, file; file=o.files[i]; i++) { %}

      {% if (file.id)
          {
            var xel = $("#x" + $("#latestUploadEl").val()).val();
            console.log("Existing value in " + "#x" + $("#latestUploadEl").val() + ": " +  xel);

            if (xel != "")
            {
              $("#x" + $("#latestUploadEl").val()).val(xel + "," + file.id);
            }
            else
            {
              $("#x" + $("#latestUploadEl").val()).val(file.id);
            };

            var xel = $("#x" + $("#latestUploadEl").val()).val();
            console.log("New value in " + "#x" + $("#latestUploadEl").val() + ": " +  xel);

          }
        %}

          <tr class="template-download {%=file.thumbnailUrl?' image':''%}">
              <td>
                  <span class="preview">
                      {% if (file.thumbnailUrl) { %}
                          <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
                      {% } %}
                  </span>
              </td>
              <td>
                  <p class="name">
                      {% if (file.url) { %}
                          <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                      {% } else { %}
                          <span>{%=file.name%}</span>
                      {% } %}
                  </p>
                  {% if (file.error) { %}
                      <div><span class="label label-danger">Error</span> {%=file.error%}</div>
                  {% } %}
              </td>
              <td>
                  <span class="size">{%=o.formatFileSize(file.size)%}</span>
              </td>
          </tr>
      {% } %}
    </script>
    <!-- BEGIN Body -->
    <!-- Possible Classes

		* 'header-function-fixed'         - header is in a fixed at all times
		* 'nav-function-fixed'            - left panel is fixed
		* 'nav-function-minify'			  - skew nav to maximize space
		* 'nav-function-hidden'           - roll mouse on edge to reveal
		* 'nav-function-top'              - relocate left pane to top
		* 'mod-main-boxed'                - encapsulates to a container
		* 'nav-mobile-push'               - content pushed on menu reveal
		* 'nav-mobile-no-overlay'         - removes mesh on menu reveal
		* 'nav-mobile-slide-out'          - content overlaps menu
		* 'mod-bigger-font'               - content fonts are bigger for readability
		* 'mod-high-contrast'             - 4.5:1 text contrast ratio
		* 'mod-color-blind'               - color vision deficiency
		* 'mod-pace-custom'               - preloader will be inside content
		* 'mod-clean-page-bg'             - adds more whitespace
		* 'mod-hide-nav-icons'            - invisible navigation icons
		* 'mod-disable-animation'         - disables css based animations
		* 'mod-hide-info-card'            - hides info card from left panel
		* 'mod-lean-subheader'            - distinguished page header
		* 'mod-nav-link'                  - clear breakdown of nav links

		>>> more settings are described inside documentation page >>>
	-->
    <body class="mod-bg-1 ">
        <!-- DOC: script to save and load page settings -->

        <script>

        function showValidationWindow()
        {
          //#validationMessages
          jQuery("#ValidationModal").modal("show");
        }

        function customSelectItem(obj, term)
        {
          //console.log ("["+term+"]");
          if (term.replaceAll(" ","") != "")
          {
            var option1 = new Option(term, term, true, true);
            jQuery(obj).append(option1);
            jQuery(obj).trigger("change");
            jQuery(".select2-search__field").val("");
          }
        }
        </script>

        <script>


            /**
             *	This script should be placed right after the body tag for fast execution
             *	Note: the script is written in pure javascript and does not depend on thirdparty library
             **/
            'use strict';

            var classHolder = document.getElementsByTagName("BODY")[0],
                /**
                 * Load from localstorage
                 **/
                themeSettings = (localStorage.getItem('themeSettings')) ? JSON.parse(localStorage.getItem('themeSettings')) :
                {},
                themeURL = themeSettings.themeURL || '',
                themeOptions = themeSettings.themeOptions || '';
            /**
             * Load theme options
             **/
            if (themeSettings.themeOptions)
            {
                classHolder.className = themeSettings.themeOptions;
                console.log("%c✔ Theme settings loaded", "color: #148f32");
            }
            else
            {
                console.log("%c✔ Heads up! Theme settings is empty or does not exist, loading default settings...", "color: #ed1c24");
            }
            if (themeSettings.themeURL && themeSettings.themeURL != "#" && themeSettings.themeURL != "" && !document.getElementById('mytheme'))
            {
                var cssfile = document.createElement('link');
                cssfile.id = 'mytheme';
                cssfile.rel = 'stylesheet';
                cssfile.href = themeURL;
                document.getElementsByTagName('head')[0].appendChild(cssfile);

            }
            else if (themeSettings.themeURL && document.getElementById('mytheme'))
            {
                document.getElementById('mytheme').href = themeSettings.themeURL;
            }
            /**
             * Save to localstorage
             **/
            var saveSettings = function()
            {
                themeSettings.themeOptions = String(classHolder.className).split(/[^\w-]+/).filter(function(item)
                {
                    return /^(nav|header|footer|mod|display)-/i.test(item);
                }).join(' ');
                if (document.getElementById('mytheme'))
                {
                    themeSettings.themeURL = document.getElementById('mytheme').getAttribute("href");
                };
                localStorage.setItem('themeSettings', JSON.stringify(themeSettings));
            }
            /**
             * Reset settings
             **/
            var resetSettings = function()
            {
                localStorage.setItem("themeSettings", "");
            }

        </script>

        <!-- BEGIN Page Wrapper -->
        <div class="page-wrapper">
            <div class="page-inner">
                <!-- BEGIN Left Aside -->
                <aside class="page-sidebar">
                    <div class="page-logo">
                        <a href="#" class="page-logo-link press-scale-down d-flex align-items-center position-relative" data-toggle="modal" data-target="#modal-shortcut">
                            <img style="width:55px" src="/img/logo.png" alt="<?php echo $_SESSION['boot']->appName; ?>" aria-roledescription="logo">
                            <span class="page-logo-text mr-1">CRM</span>
                            <span class="position-absolute text-white opacity-50 small pos-top pos-right mr-2 mt-n2"></span>
                            <i class="fal fa-angle-down d-inline-block ml-1 fs-lg color-primary-300"></i>
                        </a>
                    </div>
                    <!-- BEGIN PRIMARY NAVIGATION -->
                    <nav id="js-primary-nav" class="primary-nav" role="navigation">
                        <div class="nav-filter">
                            <div class="position-relative">
                                <input type="text" id="nav_filter_input" placeholder="Filter menu" class="form-control" tabindex="0">
                                <a href="#" onclick="return false;" class="btn-primary btn-search-close js-waves-off" data-action="toggle" data-class="list-filter-active" data-target=".page-sidebar">
                                    <i class="fal fa-chevron-up"></i>
                                </a>
                            </div>
                        </div>
                        <div class="info-card">
                            <div class="info-card-text">
                                <a href="#" class="d-flex align-items-center text-white">
                                    <span class="d-inline-block">
                                        <?php echo $_SESSION['user_fname']." ".$_SESSION['user_lname']; ?>
                                    </span>
                                </a>
                                <span class="d-inline-block" style="font-style: italic"><?php
switch ($_SESSION['user_level'])
{
  case 1:
    echo "Registrant";
  break;
  case 3:
    echo "ADED";
  break;
  case 5:
    echo "Assistant Registrar";
  break;
  case 6:
    echo "Registrar";
  break;
  case 9:
    echo "Administrator";
  break;
}

                                 ?></span>
                            </div>
                            <img src="/img/card-backgrounds/cover-2-lg.png" class="cover" alt="cover">
                            <a href="#" onclick="return false;" class="pull-trigger-btn" data-action="toggle" data-class="list-filter-active" data-target=".page-sidebar" data-focus="nav_filter_input">
                                <i class="fal fa-angle-down"></i>
                            </a>
                        </div>
                        <!--
						TIP: The menu items are not auto translated. You must have a residing lang file associated with the menu saved inside dist/media/data with reference to each 'data-i18n' attribute.
						-->
                        <ul id="js-nav-menu" class="nav-menu">
                            <li class="active">
                                <a href="/" title="Home Page" data-filter-tags="home page">
                                    <i class="fal fa-globe"></i>
                                    <span class="nav-link-text" data-i18n="nav.blankpage">Home</span>
                                </a>
                            </li>
                            <li class="nav-title">Registrar Services</li>
																<li>
																		<a href="javascript:void(0);" title="Application" data-filter-tags="utilities menu child sublevel item">
                                        <i class="fal fa-search"></i>
																				<span class="nav-link-text" data-i18n="nav.utilities_menu_child_sublevel_item">Application</span>
																		</a>
																		<ul>
																			  <li>
																				 	  <a href="/application/newRecord" title="New Application" data-filter-tags="utilities menu child another item">
																							<span class="nav-link-text" data-i18n="nav.utilities_menu_child_another_item">New Application</span>
																			 		  </a>
 																			  </li>
																				<li>
																						<a href="/application/" title="Manage Existing Application" data-filter-tags="utilities menu child sublevel item">
																								<span class="nav-link-text" data-i18n="nav.utilities_menu_child_sublevel_item">Manage Existing</span>
																						</a>
																				</li>
																		</ul>
																</li>
<?php if ($_SESSION['user_level'] == 9) { ?>
                                <li class="nav-title">System Administration</li>
                                <li>
                                    <a href="/admin/" title="Administration" data-filter-tags="adminstration">
                                        <i class="fal fa-cogs"></i>
                                        <span class="nav-link-text" data-i18n="nav.blankpage">Administration</span>
                                    </a>
                                </li>
<?php } ?>
                        </ul>
                        <div class="filter-message js-filter-message bg-success-600"></div>
                    </nav>
                    <!-- END PRIMARY NAVIGATION -->
                    <!-- NAV FOOTER -->
                    <!--div class="nav-footer shadow-top">
                        <a href="#" onclick="return false;" data-action="toggle" data-class="nav-function-minify" class="hidden-md-down">
                            <i class="ni ni-chevron-right"></i>
                            <i class="ni ni-chevron-right"></i>
                        </a>
                        <ul class="list-table m-auto nav-footer-buttons">
                            <li>
                                <a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Chat logs">
                                    <i class="fal fa-comments"></i>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Support Chat">
                                    <i class="fal fa-life-ring"></i>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Make a call">
                                    <i class="fal fa-phone"></i>
                                </a>
                            </li>
                        </ul>
                    </div--> <!-- END NAV FOOTER -->
                </aside>
                <!-- END Left Aside -->
                <div class="page-content-wrapper">
                    <!-- BEGIN Page Header -->
                    <header class="page-header" role="banner">
                        <!-- we need this logo when user switches to nav-function-top -->
                        <div class="page-logo">
                            <a href="#" class="page-logo-link press-scale-down d-flex align-items-center position-relative" data-toggle="modal" data-target="#modal-shortcut">
                                <img src="/img/logo.png" alt="<?php echo $_SESSION['boot']->appName; ?>" aria-roledescription="logo">
                                <span class="page-logo-text mr-1"><?php echo $_SESSION['boot']->appName; ?></span>
                                <span class="position-absolute text-white opacity-50 small pos-top pos-right mr-2 mt-n2"></span>
                                <i class="fal fa-angle-down d-inline-block ml-1 fs-lg color-primary-300"></i>
                            </a>
                        </div>
                        <!-- DOC: nav menu layout change shortcut -->
                        <div class="hidden-md-down dropdown-icon-menu position-relative">
                            <a href="#" class="header-btn btn js-waves-off" data-action="toggle" data-class="nav-function-hidden" title="Hide Navigation">
                                <i class="ni ni-menu"></i>
                            </a>
                            <ul>
                                <li>
                                    <a href="#" class="btn js-waves-off" data-action="toggle" data-class="nav-function-minify" title="Minify Navigation">
                                        <i class="ni ni-minify-nav"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="btn js-waves-off" data-action="toggle" data-class="nav-function-fixed" title="Lock Navigation">
                                        <i class="ni ni-lock-nav"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- DOC: mobile button appears during mobile width -->
                        <div class="hidden-lg-up">
                            <a href="#" class="header-btn btn press-scale-down" data-action="toggle" data-class="mobile-nav-on">
                                <i class="ni ni-menu"></i>
                            </a>
                        </div>
                        <!--div class="search">
                            <form class="app-forms hidden-xs-down" role="search" action="page_search.html" autocomplete="off">
                                <input type="text" id="search-field" placeholder="Search for anything" class="form-control" tabindex="1">
                                <a href="#" onclick="return false;" class="btn-danger btn-search-close js-waves-off d-none" data-action="toggle" data-class="mobile-search-on">
                                    <i class="fal fa-times"></i>
                                </a>
                            </form>
                        </div-->
                        <div class="ml-auto d-flex">
                            <!-- activate app search icon (mobile) -->
                            <!--div class="hidden-sm-up">
                                <a href="#" class="header-icon" data-action="toggle" data-class="mobile-search-on" data-focus="search-field" title="Search">
                                    <i class="fal fa-search"></i>
                                </a>
                            </div-->
                            <!-- app shortcuts -->
                            <div>
                                <a href="#" class="header-icon" data-toggle="dropdown" title="My Apps">
                                    <i class="fal fa-cube"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-animated w-auto h-auto">
                                    <div class="dropdown-header bg-trans-gradient d-flex justify-content-center align-items-center rounded-top">
                                        <h4 class="m-0 text-center color-white">
                                            Quick Shortcut
                                            <small class="mb-0 opacity-80">Registrar Services</small>
                                        </h4>
                                    </div>
                                    <div class="custom-scroll h-100">
                                        <ul class="app-list">
                                            <li>
                                                <a href="#" class="app-list-item hover-white">
                                                    <span class="icon-stack">
                                                        <i class="base-2 icon-stack-3x color-primary-600"></i>
                                                        <i class="base-3 icon-stack-2x color-primary-700"></i>
                                                        <i class="ni ni-settings icon-stack-1x text-white fs-lg"></i>
                                                    </span>
                                                    <span class="app-list-name">
                                                        Name Search
                                                    </span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#" class="app-list-item hover-white">
                                                    <span class="icon-stack">
                                                        <i class="base-2 icon-stack-3x color-primary-400"></i>
                                                        <i class="base-10 text-white icon-stack-1x"></i>
                                                        <i class="ni md-profile color-primary-800 icon-stack-2x"></i>
                                                    </span>
                                                    <span class="app-list-name">
                                                        Application
                                                    </span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#" class="app-list-item hover-white">
                                                    <span class="icon-stack">
                                                        <i class="base-9 icon-stack-3x color-success-400"></i>
                                                        <i class="base-2 icon-stack-2x color-success-500"></i>
                                                        <i class="ni ni-shield icon-stack-1x text-white"></i>
                                                    </span>
                                                    <span class="app-list-name">
                                                        Change of Particulars
                                                    </span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#" class="app-list-item hover-white">
                                                    <span class="icon-stack">
                                                        <i class="base-18 icon-stack-3x color-info-700"></i>
                                                        <span class="position-absolute pos-top pos-left pos-right color-white fs-md mt-2 fw-400">28</span>
                                                    </span>
                                                    <span class="app-list-name">
                                                        Annual Returns
                                                    </span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <!-- app message -->
                            <!--a href="#" class="header-icon" data-toggle="modal" data-target=".js-modal-messenger">
                                <i class="fal fa-globe"></i>
                                <span class="badge badge-icon">!</span>
                            </a-->
                            <!-- app notification -->
<?php

if (!isset($_SESSION['user_level']) or $_SESSION['user_level'] == "")
{
  $notif = array();
}
else
{
  $link = db();
//  $sql = "SELECT n.* FROM notifications n LEFT JOIN official o ON o.registration_HD = n.document_id AND n.document_name = 'registration' AND o.email = '".$_SESSION['user_email']."' WHERE ";
//  $sql .= "(IF (".$_SESSION['user_level']." = 1, IF (document_owner = '".$_SESSION['user_logged']."',1,0),1) = 1 AND audience REGEXP '\\\b".$_SESSION['user_level']."\\\b') OR (o.id IS NOT NULL AND audience REGEXP '\\\b1\\\b') order by n.id desc LIMIT 25";

  if ($_SESSION['user_county'] == "") $_SESSION['user_county'] = 0;

  $sql = "SELECT n.* FROM notifications n ";
  $sql .= " LEFT JOIN application r ON r.id = n.document_ID WHERE ";
  $sql .= " (IF (".$_SESSION['user_level']." = 1, IF (document_owner = '".$_SESSION['user_logged']."',1,0),1) = 1 AND audience REGEXP '\\\b".$_SESSION['user_level']."\\\b')";
  $sql .= " OR (audience REGEXP '\\\b1\\\b') order by n.id desc LIMIT 25";

  $notifs = mysqli_query($link, $sql);
  $notif = db_fetch("","","","","","","",true, false, $notifs);
}

 ?>

                            <div>
                                <a href="#" class="header-icon" data-toggle="dropdown" title="You got 11 notifications">
                                    <i class="fal fa-bell"></i>
                                    <span class="badge badge-icon"><?php echo sizeOf($notif); ?></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-animated dropdown-xl">
                                    <div class="dropdown-header bg-trans-gradient d-flex justify-content-center align-items-center rounded-top mb-2">
                                        <h4 class="m-0 text-center color-white">
                                            <?php echo sizeOf($notif); ?>
                                            <small class="mb-0 opacity-80">Notification(s)</small>
                                        </h4>
                                    </div>
                                    <ul class="nav nav-tabs nav-tabs-clean" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link px-4 fs-md js-waves-on fw-500" data-toggle="tab" href="#tab-messages" data-i18n="drpdwn.messages">Messages</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content tab-notification">
                                        <div class="tab-pane active p-3 text-center">
                                            <h5 class="mt-4 pt-4 fw-500">
                                                <span class="d-block fa-3x pb-4 text-muted">
                                                    <i class="ni ni-arrow-up text-gradient opacity-70"></i>
                                                </span> Select a tab above to activate
                                                <small class="mt-3 fs-b fw-400 text-muted">
                                                    This blank page message helps protect your privacy.
                                                </small>
                                            </h5>
                                        </div>
                                        <div class="tab-pane" id="tab-messages" role="tabpanel">
                                            <div class="custom-scroll h-100">
                                                <ul class="notification">
                                                  <?php
                                                  foreach ($notif as $not)
                                                  {
                                                    ?>
                                                      <li class="unread">
                                                              <span class="d-flex flex-column flex-1 ml-1">
                                                                <a style="display:block !important" href="/<?php echo $not['document_name']."/editRecord?id=".$not['document_id'] ?>" class="d-flex align-items-center">
                                                                  <div class="name"><?php echo $not['action_by']; ?><span style="margin: 10px 24px 0px 0px !important" class="badge badge-primary fw-n position-absolute pos-top pos-right mt-1"><?php echo $not['document_name']." - #".$not['document_id']; ?></span></div>
                                                                  <span class="msg-a fs-sm"><?php echo $not['message']; ?></span>
                                                                </a>
                                                                  <span class="msg-b fs-xs"><?php echo $not['notes']; ?>
                                                                    <?php
                                                                    if (isset($not['url']) && $not['url'] != "")
                                                                    {
                                                                    echo '&nbsp;<a style="display:inline !important;text-decoration:none !important" target="_blank" href="'.$not['url'].'" class="d-flex align-items-center"><i class="fal fa-link"></i> Document</a>';
                                                                    }
                                                                    ?>
                                                                  </span>
                                                                  <span class="fs-nano text-muted mt-1"><?php echo $not['createdAt']." - ".get_time_ago (strtotime($not['createdAt'])); ?>.</span>
                                                              </span>
                                                      </li>
                                                      <?php
                                                  }
                                                    ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="py-2 px-3 bg-faded d-block rounded-bottom text-right border-faded border-bottom-0 border-right-0 border-left-0">
                                    </div>
                                </div>
                            </div>
                            <!-- app user menu -->
                            <div>
                                <a href="#" data-toggle="dropdown" title="<?php echo $_SESSION['user_email']; ?>" class="header-icon d-flex align-items-center justify-content-center ml-2">
  																	<i class="fal fa-user"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-animated dropdown-lg">
                                    <div class="dropdown-divider m-0"></div>
                                    <a href="#" class="dropdown-item" data-action="app-reset">
                                        <span data-i18n="drpdwn.reset_layout">Reset Layout</span>
                                    </a>
                                    <a href="#" class="dropdown-item" data-toggle="modal" data-target=".js-modal-settings">
                                        <span data-i18n="drpdwn.settings">Settings</span>
                                    </a>
                                    <div class="dropdown-divider m-0"></div>
                                    <a href="#" class="dropdown-item" data-action="app-fullscreen">
                                        <span data-i18n="drpdwn.fullscreen">Fullscreen</span>
                                        <i class="float-right text-muted fw-n">F11</i>
                                    </a>
                                    <div class="dropdown-divider m-0"></div>
																		<a href="#" class="dropdown-item" data-action="app-reset">
                                        <span data-i18n="drpdwn.reset_layout">Change Password</span>
                                    </a>
																		<div class="dropdown-divider m-0"></div>
                                    <a class="dropdown-item fw-500 pt-3 pb-3" href="/user/logout">
                                        <span data-i18n="drpdwn.page-logout">Logout</span>
                                        <span class="float-right fw-n"><?php echo $_SESSION['user_email']; ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </header>
                    <!-- END Page Header -->
                    <!-- BEGIN Page Content -->
                    <!-- the #js-page-content id is needed for some plugins to initialize -->
                    <main id="js-page-content" role="main" class="page-content">
                      @CONTENT@
                   </main>
                    <!-- this overlay is activated only when mobile menu is triggered -->
                    <div class="page-content-overlay" data-action="toggle" data-class="mobile-nav-on"></div> <!-- END Page Content -->
                    <!-- BEGIN Page Footer -->
                    <footer class="page-footer" role="contentinfo">
                        <div class="d-flex align-items-center flex-1 text-muted">
                            <span class="hidden-md-down fw-700"><?php echo date("Y"); ?> &copy; EvoCRM</span>
                        </div>
                        <div>
                            <span style="font-size: 11px;color: #aaa; font-style: italic;">Provided <a target='_blank'  style='font-size: 10px; text-decoration:none !important'  href='https://thinktakentsolutions.com/'>Think Talent Solutions</a>
                        </div>
                    </footer>
                    <!-- END Page Footer -->
                    <!-- BEGIN Shortcuts -->
                    <div class="modal fade modal-backdrop-transparent" id="modal-shortcut" tabindex="-1" role="dialog" aria-labelledby="modal-shortcut" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-top modal-transparent" role="document">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <ul class="app-list w-auto h-auto p-0 text-left">
                                        <li>
                                            <a href="intel_introduction.html" class="app-list-item text-white border-0 m-0">
                                                <div class="icon-stack">
                                                    <i class="base base-7 icon-stack-3x opacity-100 color-primary-500 "></i>
                                                    <i class="base base-7 icon-stack-2x opacity-100 color-primary-300 "></i>
                                                    <i class="fal fa-home icon-stack-1x opacity-100 color-white"></i>
                                                </div>
                                                <span class="app-list-name">
                                                    Home
                                                </span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="page_inbox_general.html" class="app-list-item text-white border-0 m-0">
                                                <div class="icon-stack">
                                                    <i class="base base-7 icon-stack-3x opacity-100 color-success-500 "></i>
                                                    <i class="base base-7 icon-stack-2x opacity-100 color-success-300 "></i>
                                                    <i class="ni ni-envelope icon-stack-1x text-white"></i>
                                                </div>
                                                <span class="app-list-name">
                                                    Inbox
                                                </span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="intel_introduction.html" class="app-list-item text-white border-0 m-0">
                                                <div class="icon-stack">
                                                    <i class="base base-7 icon-stack-2x opacity-100 color-primary-300 "></i>
                                                    <i class="fal fa-plus icon-stack-1x opacity-100 color-white"></i>
                                                </div>
                                                <span class="app-list-name">
                                                    Add More
                                                </span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END Shortcuts -->
                    <!-- BEGIN Color profile -->
                    <!-- this area is hidden and will not be seen on screens or screen readers -->
                    <!-- we use this only for CSS color refernce for JS stuff -->
                    <p id="js-color-profile" class="d-none">
                        <span class="color-primary-50"></span>
                        <span class="color-primary-100"></span>
                        <span class="color-primary-200"></span>
                        <span class="color-primary-300"></span>
                        <span class="color-primary-400"></span>
                        <span class="color-primary-500"></span>
                        <span class="color-primary-600"></span>
                        <span class="color-primary-700"></span>
                        <span class="color-primary-800"></span>
                        <span class="color-primary-900"></span>
                        <span class="color-info-50"></span>
                        <span class="color-info-100"></span>
                        <span class="color-info-200"></span>
                        <span class="color-info-300"></span>
                        <span class="color-info-400"></span>
                        <span class="color-info-500"></span>
                        <span class="color-info-600"></span>
                        <span class="color-info-700"></span>
                        <span class="color-info-800"></span>
                        <span class="color-info-900"></span>
                        <span class="color-danger-50"></span>
                        <span class="color-danger-100"></span>
                        <span class="color-danger-200"></span>
                        <span class="color-danger-300"></span>
                        <span class="color-danger-400"></span>
                        <span class="color-danger-500"></span>
                        <span class="color-danger-600"></span>
                        <span class="color-danger-700"></span>
                        <span class="color-danger-800"></span>
                        <span class="color-danger-900"></span>
                        <span class="color-warning-50"></span>
                        <span class="color-warning-100"></span>
                        <span class="color-warning-200"></span>
                        <span class="color-warning-300"></span>
                        <span class="color-warning-400"></span>
                        <span class="color-warning-500"></span>
                        <span class="color-warning-600"></span>
                        <span class="color-warning-700"></span>
                        <span class="color-warning-800"></span>
                        <span class="color-warning-900"></span>
                        <span class="color-success-50"></span>
                        <span class="color-success-100"></span>
                        <span class="color-success-200"></span>
                        <span class="color-success-300"></span>
                        <span class="color-success-400"></span>
                        <span class="color-success-500"></span>
                        <span class="color-success-600"></span>
                        <span class="color-success-700"></span>
                        <span class="color-success-800"></span>
                        <span class="color-success-900"></span>
                        <span class="color-fusion-50"></span>
                        <span class="color-fusion-100"></span>
                        <span class="color-fusion-200"></span>
                        <span class="color-fusion-300"></span>
                        <span class="color-fusion-400"></span>
                        <span class="color-fusion-500"></span>
                        <span class="color-fusion-600"></span>
                        <span class="color-fusion-700"></span>
                        <span class="color-fusion-800"></span>
                        <span class="color-fusion-900"></span>
                    </p>
                    <!-- END Color profile -->
                </div>
            </div>
        </div>
        <!-- END Page Wrapper -->
        <!-- BEGIN Quick Menu -->
        <!-- to add more items, please make sure to change the variable '$menu-items: number;' in your _page-components-shortcut.scss -->
        <nav class="shortcut-menu d-none d-sm-block">
            <input type="checkbox" class="menu-open" name="menu-open" id="menu_open" />
            <label for="menu_open" class="menu-open-button ">
                <span class="app-shortcut-icon d-block"></span>
            </label>
            <a href="#" class="menu-item btn" data-toggle="tooltip" data-placement="left" title="Scroll Top">
                <i class="fal fa-arrow-up"></i>
            </a>
            <a href="/user/logout" class="menu-item btn" data-toggle="tooltip" data-placement="left" title="Logout">
                <i class="fal fa-sign-out"></i>
            </a>
            <a href="#" class="menu-item btn" data-action="app-fullscreen" data-toggle="tooltip" data-placement="left" title="Full Screen">
                <i class="fal fa-expand"></i>
            </a>
            <a href="#" class="menu-item btn" data-action="app-print" data-toggle="tooltip" data-placement="left" title="Print page">
                <i class="fal fa-print"></i>
            </a>
        </nav>
        <!-- END Quick Menu -->
        <!-- BEGIN Page Settings -->
        <div class="modal fade js-modal-settings modal-backdrop-transparent" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-right modal-md">
                <div class="modal-content">
                    <div class="dropdown-header bg-trans-gradient d-flex justify-content-center align-items-center w-100">
                        <h4 class="m-0 text-center color-white">
                            Layout Settings
                            <small class="mb-0 opacity-80">User Interface Settings</small>
                        </h4>
                        <button type="button" class="close text-white position-absolute pos-top pos-right p-2 m-1 mr-2" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="fal fa-times"></i></span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="settings-panel">
                            <div class="mt-4 d-table w-100 px-5">
                                <div class="d-table-cell align-middle">
                                    <h5 class="p-0">
                                        App Layout
                                    </h5>
                                </div>
                            </div>
                            <div class="list" id="fh">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="header-function-fixed"></a>
                                <span class="onoffswitch-title">Fixed Header</span>
                                <span class="onoffswitch-title-desc">header is in a fixed at all times</span>
                            </div>
                            <div class="list" id="nff">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="nav-function-fixed"></a>
                                <span class="onoffswitch-title">Fixed Navigation</span>
                                <span class="onoffswitch-title-desc">left panel is fixed</span>
                            </div>
                            <div class="list" id="nfm">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="nav-function-minify"></a>
                                <span class="onoffswitch-title">Minify Navigation</span>
                                <span class="onoffswitch-title-desc">Skew nav to maximize space</span>
                            </div>
                            <div class="list" id="nfh">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="nav-function-hidden"></a>
                                <span class="onoffswitch-title">Hide Navigation</span>
                                <span class="onoffswitch-title-desc">roll mouse on edge to reveal</span>
                            </div>
                            <div class="list" id="nft">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="nav-function-top"></a>
                                <span class="onoffswitch-title">Top Navigation</span>
                                <span class="onoffswitch-title-desc">Relocate left pane to top</span>
                            </div>
                            <div class="list" id="fff">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="footer-function-fixed"></a>
                                <span class="onoffswitch-title">Fixed Footer</span>
                                <span class="onoffswitch-title-desc">page footer is fixed</span>
                            </div>
                            <div class="list" id="mmb">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="mod-main-boxed"></a>
                                <span class="onoffswitch-title">Boxed Layout</span>
                                <span class="onoffswitch-title-desc">Encapsulates to a container</span>
                            </div>
                            <div class="expanded">
                                <ul class="mb-3 mt-1">
                                    <li>
                                        <div class="bg-fusion-50" data-action="toggle" data-class="mod-bg-1"></div>
                                    </li>
                                    <li>
                                        <div class="bg-warning-200" data-action="toggle" data-class="mod-bg-2"></div>
                                    </li>
                                    <li>
                                        <div class="bg-primary-200" data-action="toggle" data-class="mod-bg-3"></div>
                                    </li>
                                    <li>
                                        <div class="bg-success-300" data-action="toggle" data-class="mod-bg-4"></div>
                                    </li>
                                    <li>
                                        <div class="bg-white border" data-action="toggle" data-class="mod-bg-none"></div>
                                    </li>
                                </ul>
                                <div class="list" id="mbgf">
                                    <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="mod-fixed-bg"></a>
                                    <span class="onoffswitch-title">Fixed Background</span>
                                </div>
                            </div>
                            <div class="mt-4 d-table w-100 px-5">
                                <div class="d-table-cell align-middle">
                                    <h5 class="p-0">
                                        Mobile Menu
                                    </h5>
                                </div>
                            </div>
                            <div class="list" id="nmp">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="nav-mobile-push"></a>
                                <span class="onoffswitch-title">Push Content</span>
                                <span class="onoffswitch-title-desc">Content pushed on menu reveal</span>
                            </div>
                            <div class="list" id="nmno">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="nav-mobile-no-overlay"></a>
                                <span class="onoffswitch-title">No Overlay</span>
                                <span class="onoffswitch-title-desc">Removes mesh on menu reveal</span>
                            </div>
                            <div class="list" id="sldo">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="nav-mobile-slide-out"></a>
                                <span class="onoffswitch-title">Off-Canvas <sup>(beta)</sup></span>
                                <span class="onoffswitch-title-desc">Content overlaps menu</span>
                            </div>
                            <div class="mt-4 d-table w-100 px-5">
                                <div class="d-table-cell align-middle">
                                    <h5 class="p-0">
                                        Accessibility
                                    </h5>
                                </div>
                            </div>
                            <div class="list" id="mbf">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="mod-bigger-font"></a>
                                <span class="onoffswitch-title">Bigger Content Font</span>
                                <span class="onoffswitch-title-desc">content fonts are bigger for readability</span>
                            </div>
                            <div class="list" id="mhc">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="mod-high-contrast"></a>
                                <span class="onoffswitch-title">High Contrast Text (WCAG 2 AA)</span>
                                <span class="onoffswitch-title-desc">4.5:1 text contrast ratio</span>
                            </div>
                            <div class="list" id="mcb">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="mod-color-blind"></a>
                                <span class="onoffswitch-title">Daltonism <sup>(beta)</sup> </span>
                                <span class="onoffswitch-title-desc">color vision deficiency</span>
                            </div>
                            <div class="list" id="mpc">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="mod-pace-custom"></a>
                                <span class="onoffswitch-title">Preloader Inside</span>
                                <span class="onoffswitch-title-desc">preloader will be inside content</span>
                            </div>
                            <div class="list" id="mpi">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="mod-panel-icon"></a>
                                <span class="onoffswitch-title">SmartPanel Icons (not Panels)</span>
                                <span class="onoffswitch-title-desc">smartpanel buttons will appear as icons</span>
                            </div>
                            <div class="mt-4 d-table w-100 px-5">
                                <div class="d-table-cell align-middle">
                                    <h5 class="p-0">
                                        Global Modifications
                                    </h5>
                                </div>
                            </div>
                            <div class="list" id="mcbg">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="mod-clean-page-bg"></a>
                                <span class="onoffswitch-title">Clean Page Background</span>
                                <span class="onoffswitch-title-desc">adds more whitespace</span>
                            </div>
                            <div class="list" id="mhni">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="mod-hide-nav-icons"></a>
                                <span class="onoffswitch-title">Hide Navigation Icons</span>
                                <span class="onoffswitch-title-desc">invisible navigation icons</span>
                            </div>
                            <div class="list" id="dan">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="mod-disable-animation"></a>
                                <span class="onoffswitch-title">Disable CSS Animation</span>
                                <span class="onoffswitch-title-desc">Disables CSS based animations</span>
                            </div>
                            <div class="list" id="mhic">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="mod-hide-info-card"></a>
                                <span class="onoffswitch-title">Hide Info Card</span>
                                <span class="onoffswitch-title-desc">Hides info card from left panel</span>
                            </div>
                            <div class="list" id="mlph">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="mod-lean-subheader"></a>
                                <span class="onoffswitch-title">Lean Subheader</span>
                                <span class="onoffswitch-title-desc">distinguished page header</span>
                            </div>
                            <div class="list" id="mnl">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="mod-nav-link"></a>
                                <span class="onoffswitch-title">Hierarchical Navigation</span>
                                <span class="onoffswitch-title-desc">Clear breakdown of nav links</span>
                            </div>
                            <div class="list" id="mdn">
                                <a href="#" onclick="return false;" class="btn btn-switch" data-action="toggle" data-class="mod-nav-dark"></a>
                                <span class="onoffswitch-title">Dark Navigation</span>
                                <span class="onoffswitch-title-desc">Navigation background is darkend</span>
                            </div>
                            <hr class="mb-0 mt-4">
                            <div class="mt-4 d-table w-100 pl-5 pr-3">
                                <div class="d-table-cell align-middle">
                                    <h5 class="p-0">
                                        Global Font Size
                                    </h5>
                                </div>
                            </div>
                            <div class="list mt-1">
                                <div class="btn-group btn-group-sm btn-group-toggle my-2" data-toggle="buttons">
                                    <label class="btn btn-default btn-sm" data-action="toggle-swap" data-class="root-text-sm" data-target="html">
                                        <input type="radio" name="changeFrontSize"> SM
                                    </label>
                                    <label class="btn btn-default btn-sm" data-action="toggle-swap" data-class="root-text" data-target="html">
                                        <input type="radio" name="changeFrontSize" checked=""> MD
                                    </label>
                                    <label class="btn btn-default btn-sm" data-action="toggle-swap" data-class="root-text-lg" data-target="html">
                                        <input type="radio" name="changeFrontSize"> LG
                                    </label>
                                    <label class="btn btn-default btn-sm" data-action="toggle-swap" data-class="root-text-xl" data-target="html">
                                        <input type="radio" name="changeFrontSize"> XL
                                    </label>
                                </div>
                                <span class="onoffswitch-title-desc d-block mb-0">Change <strong>root</strong> font size to effect rem
                                    values (resets on page refresh)</span>
                            </div>
                            <hr class="mb-0 mt-4">
                            <div class="pl-5 pr-3 py-3 bg-faded">
                                <div class="row no-gutters">
                                    <div class="col-6 pr-1">
                                        <a href="#" class="btn btn-outline-danger fw-500 btn-block" data-action="app-reset">Reset Settings</a>
                                    </div>
                                    <div class="col-6 pl-1">
                                        <a href="#" class="btn btn-danger fw-500 btn-block" data-action="factory-reset">Factory Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div> <span id="saving"></span>
                    </div>
                </div>
            </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="nameSearchFeeModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="notesModalLabel">Name Search</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div class="form-group">
                  <div id="panel-5" class="panel">
                                                      <div class="panel-container show">
                                                          <div class="panel-content">
                                                              <div class="panel-tag">
                                                                  View the steps below to understand how name search works.
                                                              </div>
                                                              <div class="accordion accordion-hover" id="js_demo_accordion-5">
                                                                  <div class="card">
                                                                      <div class="card-header">
                                                                          <a href="javascript:void(0);" class="card-title collapsed" data-toggle="collapse" data-target="#js_demo_accordion-5a" aria-expanded="false">
                                                                              <i class="fal fa-cog width-2 fs-xl"></i>
                                                                              Step 1: Apply for a name search
                                                                              <span class="ml-auto">
                                                                                  <span class="collapsed-reveal">
                                                                                      <i class="fal fa-chevron-up fs-xl"></i>
                                                                                  </span>
                                                                                  <span class="collapsed-hidden">
                                                                                      <i class="fal fa-chevron-down fs-xl"></i>
                                                                                  </span>
                                                                              </span>
                                                                          </a>
                                                                      </div>
                                                                      <div id="js_demo_accordion-5a" class="collapse" data-parent="#js_demo_accordion-5" style="">
                                                                          <div class="card-body">
                                                                            After you click "Apply" below, you will be presented with a form to provide the 3 names you prefer. Submit the form to proceed to make the payment for the name search. See fees in the table below.
                                                                            <table cellpadding="5" bordercolor="#AAAAAA" border="1" style="width:100%;">
                                                                              <tr style="background-color:#efefef">
                                                                                <th><b>Service</b></th>
                                                                                <th><b>Fee (Kshs)</b></th>
                                                                              </tr>
                                                                              <tr>
                                                                                <td>Name Search</td>
                                                                                <td style="text-align:right">100</td>
                                                                              </tr>
                                                                              <tr>
                                                                                <th style="text-align:right"><b>Total</b></th>
                                                                                <th style="text-align:right"><b>100</b></th>
                                                                              </tr>
                                                                            </table>
                                                                          </div>
                                                                      </div>
                                                                  </div>
                                                                  <div class="card">
                                                                      <div class="card-header">
                                                                          <a href="javascript:void(0);" class="card-title collapsed" data-toggle="collapse" data-target="#js_demo_accordion-5b" aria-expanded="false">
                                                                              <i class="fal fa-code-merge width-2 fs-xl"></i>
                                                                              Step 2: Receive application result
                                                                              <span class="ml-auto">
                                                                                  <span class="collapsed-reveal">
                                                                                      <i class="fal fa-chevron-up fs-xl"></i>
                                                                                  </span>
                                                                                  <span class="collapsed-hidden">
                                                                                      <i class="fal fa-chevron-down fs-xl"></i>
                                                                                  </span>
                                                                              </span>
                                                                          </a>
                                                                      </div>
                                                                      <div id="js_demo_accordion-5b" class="collapse" data-parent="#js_demo_accordion-5" style="">
                                                                          <div class="card-body">
                                                                              Once you have completed payment, you will be notified when the results of the search are ready. If any of the proposed names are available, you may proceed to apply for registration. If none of the proposed names are available, you will be required to submit a new name search.
                                                                          </div>
                                                                      </div>
                                                                  </div>
                                                                  <div class="card">
                                                                      <div class="card-header">
                                                                          <a href="javascript:void(0);" class="card-title collapsed" data-toggle="collapse" data-target="#js_demo_accordion-5c" aria-expanded="true">
                                                                              <i class="fal fa-cloud-upload-alt width-2 fs-xl"></i>
                                                                              Step 3: Apply for registration
                                                                              <span class="ml-auto">
                                                                                  <span class="collapsed-reveal">
                                                                                      <i class="fal fa-chevron-up fs-xl"></i>
                                                                                  </span>
                                                                                  <span class="collapsed-hidden">
                                                                                      <i class="fal fa-chevron-down fs-xl"></i>
                                                                                  </span>
                                                                              </span>
                                                                          </a>
                                                                      </div>
                                                                      <div id="js_demo_accordion-5c" class="collapse" data-parent="#js_demo_accordion-5" style="">
                                                                          <div class="card-body">
                                                                            If any of the proposed names are available, you may proceed to apply for registration of an Association, Umbrella Organization or MSE, based on what you selected when filling the name search form. </div>
                                                                      </div>
                                                                  </div>
                                                              </div>
                                                          </div>
                                                      </div>
                                                  </div>


                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="window.location = '/search/newRecord';" data-dismiss="modal">Apply Now</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="registrationFeeModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="notesModalLabel">Registration Application</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div class="form-group">
                  <div id="panel-5" class="panel">
                                                      <div class="panel-container show">
                                                          <div class="panel-content">
                                                              <div class="panel-tag">
                                                                  View the steps below to understand how registration works.
                                                              </div>
                                                              <div class="accordion accordion-hover" id="js_demo_accordion-5">
                                                                  <div class="card">
                                                                      <div class="card-header">
                                                                          <a href="javascript:void(0);" class="card-title collapsed" data-toggle="collapse" data-target="#js_demo_accordion-5a" aria-expanded="false">
                                                                              <i class="fal fa-cog width-2 fs-xl"></i>
                                                                              Step 1: Apply for registration
                                                                              <span class="ml-auto">
                                                                                  <span class="collapsed-reveal">
                                                                                      <i class="fal fa-chevron-up fs-xl"></i>
                                                                                  </span>
                                                                                  <span class="collapsed-hidden">
                                                                                      <i class="fal fa-chevron-down fs-xl"></i>
                                                                                  </span>
                                                                              </span>
                                                                          </a>
                                                                      </div>
                                                                      <div id="js_demo_accordion-5a" class="collapse" data-parent="#js_demo_accordion-5" style="">
                                                                          <div class="card-body">
                                                                            After you click "Apply" below, you will be presented with a form to provide details of the registration.
                                                                            <ul>
                                                                              <li>For Associations and Umbrella Organizations, you will be required to provide details of at least 3 officials (Chairperson, Treasurer, Secretary).
                                                                              <li>For Associations, you need to enter details for at least 35 members. There is no minimum number of members for Umbrella Organizations.
                                                                              <li>Every member has to fill in the member form before you can submit the application.
                                                                              <li>You will be provided with buttons to notify one or all members to submit their details.
                                                                              <li>Each member gets a unique link in their email and can update their member profile by clicking on the link.
                                                                              <li>You will be required to upload current constitution of the association/umbrella organization in PDF format.
                                                                              <li>Once all members have filled in their details, you will be able to submit the application form.
                                                                              <li>Until then, you may save the application as a draft.
                                                                            </ul>
                                                                          </div>
                                                                      </div>
                                                                  </div>
                                                                  <div class="card">
                                                                      <div class="card-header">
                                                                          <a href="javascript:void(0);" class="card-title collapsed" data-toggle="collapse" data-target="#js_demo_accordion-5b" aria-expanded="false">
                                                                              <i class="fal fa-code-merge width-2 fs-xl"></i>
                                                                              Step 2:  Submit the applicaiton form
                                                                              <span class="ml-auto">
                                                                                  <span class="collapsed-reveal">
                                                                                      <i class="fal fa-chevron-up fs-xl"></i>
                                                                                  </span>
                                                                                  <span class="collapsed-hidden">
                                                                                      <i class="fal fa-chevron-down fs-xl"></i>
                                                                                  </span>
                                                                              </span>
                                                                          </a>
                                                                      </div>
                                                                      <div id="js_demo_accordion-5b" class="collapse" data-parent="#js_demo_accordion-5" style="">
                                                                          <div class="card-body">
                                                                              Once you submit the application, you will be required to make the payment for the registration process. See fees in the table below
                                                                              <table cellpadding="5" bordercolor="#AAAAAA" border="1" style="width:100%;">
                                                                                <tr style="background-color:#efefef">
                                                                                  <th><b>Service</b></th>
                                                                                  <th><b>Fee (Kshs)</b></th>
                                                                                </tr>
                                                                                <tr>
                                                                                  <td>Registration</td>
                                                                                  <td style="text-align:right">2,000</td>
                                                                                </tr>
                                                                                <tr>
                                                                                  <th style="text-align:right"><b>Total</b></th>
                                                                                  <th style="text-align:right"><b>2,000</b></th>
                                                                                </tr>
                                                                              </table>
                                                                            </div>
                                                                      </div>
                                                                  </div>
                                                                  <div class="card">
                                                                      <div class="card-header">
                                                                          <a href="javascript:void(0);" class="card-title collapsed" data-toggle="collapse" data-target="#js_demo_accordion-5c" aria-expanded="true">
                                                                              <i class="fal fa-cloud-upload-alt width-2 fs-xl"></i>
                                                                              Step 3: Official Approval
                                                                              <span class="ml-auto">
                                                                                  <span class="collapsed-reveal">
                                                                                      <i class="fal fa-chevron-up fs-xl"></i>
                                                                                  </span>
                                                                                  <span class="collapsed-hidden">
                                                                                      <i class="fal fa-chevron-down fs-xl"></i>
                                                                                  </span>
                                                                              </span>
                                                                          </a>
                                                                      </div>
                                                                      <div id="js_demo_accordion-5c" class="collapse" data-parent="#js_demo_accordion-5" style="">
                                                                          <div class="card-body">
                                                                            After you submit the payment for registration, all the officials listed in the application will have to approve the application before MSEA are able to review the submission.
                                                                            <ul>
                                                                              <li>Officials will receive an email notification to login and approve the application.
                                                                              <li>If the official does not have a login, they will first need to register on the portal.
                                                                              <li>Once all three officials have verified, MSEA officials will review the application.
                                                                              <li>If there are any correction to be made, the application will be returned for review.
                                                                              <li>If all is in order, you will be awarded the certificate of registration.
                                                                            </ul>
                                                                      </div>
                                                                  </div>
                                                              </div>
                                                          </div>
                                                      </div>
                                                  </div>


                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="reglink" onclick="window.location = '/registration/newRecord';" data-dismiss="modal">Apply Now</button>
              </div>
            </div>
          </div>
        </div>
      </div>

        <!-- END Page Settings -->
        <!-- base vendor bundle:
    	 DOC: if you remove pace.js from core please note on Internet Explorer some CSS animations may execute before a page is fully loaded, resulting 'jump' animations
    				+ pace.js (recommended)
    				+ jquery.js (core)
    				+ jquery-ui-cust.js (core)
    				+ popper.js (core)
    				+ bootstrap.js (core)
    				+ slimscroll.js (extension)
    				+ app.navigation.js (core)
    				+ ba-throttle-debounce.js (core)
    				+ waves.js (extension)
    				+ smartpanels.js (extension)
    				+ src/../jquery-snippets.js (core) -->
    		<script src="/js/vendors.bundle.js"></script>
    		<script src="/js/app.bundle.js"></script>
        <script>
          function triggerListener(el)
          {
              elf = $(el).closest( "form" ).attr('data-element');
              console.log("form de:" + elf);
              $("#latestUploadEl").val(elf);

          }

          $(document).ready(function()
                      {
                          //init default
                          $('.summernote-js').summernote(
                          {
                              height: 600,
                              tabsize: 2,
                              placeholder: "Type here...",
                              dialogsFade: true,
                              toolbar: [
                                  ['style', ['style']],
                                  ['font', ['strikethrough', 'superscript', 'subscript']],
                                  ['font', ['bold', 'italic', 'underline', 'clear']],
                                  ['fontsize', ['fontsize']],
                                  ['fontname', ['fontname']],
                                  ['color', ['color']],
                                  ['para', ['ul', 'ol', 'paragraph']],
                                  ['height', ['height']]
                                  ['table', ['table']],
                                  ['insert', ['link', 'picture', 'video']],
                                  ['view', ['fullscreen', 'codeview', 'help']]
                              ],
                              callbacks:
                              {
                                  //restore from localStorage
                                  onInit: function(e)
                                  {
//                                      $('.summernote-js').summernote("code", localStorage.getItem("summernoteData"));
                                  },
                                  onChange: function(contents, $editable)
                                  {
                                  }
                              }
                          });
                        });
        </script>
       		<!--This page contains the basic JS and CSS files to get started on your project. If you need aditional addon's or plugins please see scripts located at the bottom of each page in order to find out which JS/CSS files to add.-->

          <!-- Modal -->
          <div class="modal fade" id="ValidationModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="notesModalLabel">Form Validation</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="form-group" id="ccvalidationMessages">
                    <p>Please check that all required fields have been populated.</p>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary" data-dismiss="modal">Okay</button>
                </div>
              </div>
            </div>
          </div>

    </body>
    <!-- END Body -->
</html>
