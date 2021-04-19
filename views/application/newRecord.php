<ol class="breadcrumb page-breadcrumb">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/search">Application</a></li>
    <li class="breadcrumb-item"><a href="javascript:void(0);">New</a></li>
    <li class="position-absolute pos-top pos-right d-none d-sm-block"><span class="js-get-date"></span></li>
</ol>
<div class="subheader">
    <h1 class="subheader-title">
        <i class='subheader-icon fal fa-search'></i> New <span class='fw-300'>Application</span>
        <small>
          Applications
        </small>
    </h1>
</div>
<?php
echo renderValidationMessage();
?>
<script>

jQuery(document).ready(function()
{
  <?php
  //DISABLE ALL FIELDS IF STATUS != 1 and USER = 1
  $dstat = getDocumentStatus($view[2],$view[1][0]['id'], true)[0]['step'];

  if ($dstat == "" or $dstat == 1)
  {
  }
  else
  {
    if ($_SESSION['user_level'] == 1)
    {
      ?> jQuery(".form-control").attr("disabled","disabled"); <?php
    }
  }
  ?>
}

function checkField(vl,id)
{
	//nothing to do after a foreign key is selected
}
</script>
<?php
genericFormStart($view, $_SESSION['user_level'], "required", true, true, true, array());
?>
<script>
                                                (function()
                                                {
                                                    'use strict';
                                                    window.addEventListener('load', function()
                                                    {
                                                        // Fetch all the forms we want to apply custom Bootstrap validation styles to
                                                        var forms = document.getElementsByClassName('needs-validation');
                                                        // Loop over them and prevent submission
                                                        var validation = Array.prototype.filter.call(forms, function(form)
                                                        {
                                                            form.addEventListener('submit', function(event)
                                                            {
                                                                if (form.checkValidity() === false)
                                                                {
                                                                  if (jQuery('#svl').val() != 1)
                                                                  {
                                                                    showValidationWindow();
                                                                    event.preventDefault();
                                                                    event.stopPropagation();
                                                                  }
                                                                }
                                                                form.classList.add('was-validated');
                                                            }, false);
                                                        });
                                                    }, false);
                                                })();
</script>
