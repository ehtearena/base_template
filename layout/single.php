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

           <style>

           .was-validated .form-control:invalid + .select2-container > span > .select2-selection{
           	border-color:red !important;
           }

           </style>

    </head>

    <script>

    function showValidationWindow()
    {
      //#validationMessages
      jQuery("#ValidationModal").modal("show");
    }


    function customSelectItem(obj, term)
    {
      console.log ("["+term+"]");
      if (term.replaceAll(" ","") != "")
      {
        var option1 = new Option(term, term, true, true);
        jQuery(obj).append(option1);
        jQuery(obj).trigger("change");
        jQuery(".select2-search__field").val("");

      }
    }
    </script>

    <style>
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
    <body>
        <!-- DOC: script to save and load page settings -->


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
        <div class="page-wrapper auth">
            <div class="page-inner bg-brand-gradient">
                <div class="page-content-wrapper bg-transparent m-0">
                    <div class="height-10 w-100 shadow-lg px-4 bg-brand-gradient">
                        <div class="d-flex align-items-center container p-0">
                          <div class="page-logo width-mobile-auto m-0 align-items-center justify-content-center p-0 bg-transparent bg-img-none shadow-0 height-9 border-0">
                              <a href="/" class="page-logo-link press-scale-down d-flex align-items-center">
                                  <img src="/img/logo.png" alt="SmartAdmin WebApp" aria-roledescription="logo">
                                  <span class="page-logo-text mr-1"><?php echo $_SESSION['boot']->appName; ?></span>
                              </a>
                          </div>
                          <?php
                          if ($_SESSION['boot']->controller != "member")
                          {
                            echo '<a href="/user/loginPage" class="btn-link text-white ml-auto">Login</a>';
                          }
                           ?>

                        </div>
                    </div>
                    <div class="flex-1" style="background: url(/img/svg/pattern-1.svg) no-repeat center bottom fixed; background-size: cover;">
                        <div style="margin-top: 10px !important; padding-top: 0px !important;" class="container py-4 py-lg-5 my-lg-5 px-4 px-sm-0">
                            <div class="row">
                                <div class="col-xl-12">
                                    <h2 class="fs-xxl fw-500 mt-4 text-white text-center">
<?php
if ($_SESSION['boot']->action == "registerPage") echo "Sign-up";
if ($_SESSION['boot']->controller == "member" or $_SESSION['boot']->controller == "registration")
{
  echo "Member Details";
}
 ?>
                                        <small class="h3 fw-300 mt-3 mb-5 text-white opacity-60 hidden-sm-down">
                                          <?php $view['update_details']; ?>
                                        </small>
                                    </h2>
                                </div>
                                <div class="col-xl-12 ml-auto mr-auto">
                                    <div class="card p-4 rounded-plus bg-faded">
                                        @CONTENT@
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="position-absolute pos-bottom pos-left pos-right p-3 text-center text-white">
                            <?php echo date('Y'); ?> © MSEA
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                <div class="form-group" id="validationMessages">
                  <p>Please check that all required fields have been populated.</p>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Okay</button>
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
    </body>
    <!-- END Body -->
</html>
