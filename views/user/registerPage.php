<script>


jQuery(document).ready(function()
{
	checkID();
	checkPhone();
	checkEmail();
})

var errorEmail = 1;
var errorID = 1;
var errorPhone = 1;

function checkUser()
{
	jQuery('#userNotification').html("Checking availability...");
	var user = jQuery("#user").val();
	jQuery.ajax(
	{
	    data: ({user:user}),
	    global: false,
		type:'POST',
		url: '<?php echo $_SESSION['boot']->getAppPublic(); ?>user/checkUser',
		success: function(msg)
		{
			if (parseInt(msg) == 0)
			{
				jQuery('#userNotification').html("Not Available");
				errorUser = 1;
			}
			else if (parseInt(msg) == 99)
			{
				jQuery('#userNotification').html("Allowed characters: letters, numbers, underscore(_), at(@), period(.)");
				errorUser = 1;
			}
			else
			{
				jQuery('#userNotification').html("Available");
				errorUser = 0;
			}
			if (errorUser == 0 && errorEmail == 0) { jQuery('#submitbutton').show(); } else {jQuery('#submitbutton').hide();}
		},
		error: function(ErrXMLHttpRequest, textStatus, errorThrown)
		{
			//TODO: something
		}
	});
}


function checkID()
{
	var nationalid = jQuery("#national_identity").val();
	if (nationalid != "")
	{
		jQuery('#idNotification').html("Checking availability...");
		jQuery.ajax(
		{
		    data: ({nationalid:nationalid}),
		    global: false,
			type:'POST',
			url: '<?php echo $_SESSION['boot']->getAppPublic(); ?>user/checkID',
			success: function(msg)
			{
				if (parseInt(msg) > 0)
				{
						jQuery('#idNotification').html("Not Available");
						errorID = 1;
						jQuery('#submitbutton').removeClass("btn-danger")
						jQuery('#submitbutton').addClass("btn-default")
				}
				else
				{
					jQuery('#idNotification').html("Available");
					errorID = 0;
					jQuery('#submitbutton').removeClass("btn-default")
					jQuery('#submitbutton').addClass("btn-danger")
				}
				if (errorID == 0 && errorEmail == 0  && errorPhone == 0) { jQuery('#submitbutton').show(); } else { jQuery('#submitbutton').hide();}
			},
			error: function(ErrXMLHttpRequest, textStatus, errorThrown)
			{
				//TODO: something
			}
		});

	}
}

function checkPhone()
{
	var phone = jQuery("#phone").val();
	if (phone != "")
	{
		jQuery('#phoneNotification').html("Checking availability...");
		jQuery.ajax(
		{
		    data: ({phone:phone}),
		    global: false,
			type:'POST',
			url: '<?php echo $_SESSION['boot']->getAppPublic(); ?>user/checkPhone',
			success: function(msg)
			{
				if (parseInt(msg) > 0)
				{
						jQuery('#phoneNotification').html("Not Available");
						errorPhone = 1;
						jQuery('#submitbutton').removeClass("btn-danger")
						jQuery('#submitbutton').addClass("btn-default")
				}
				else
				{
					jQuery('#phoneNotification').html("Available");
					errorPhone = 0;
					jQuery('#submitbutton').removeClass("btn-default")
					jQuery('#submitbutton').addClass("btn-danger")
				}
				if (errorID == 0 && errorEmail == 0  && errorPhone == 0) { jQuery('#submitbutton').show(); } else { jQuery('#submitbutton').hide();}
			},
			error: function(ErrXMLHttpRequest, textStatus, errorThrown)
			{
				//TODO: something
			}
		});

	}
}


function checkEmail()
{
	var email = jQuery("#email").val();
	if (email != "")
	{
		jQuery('#emailNotification').html("Checking availability...");
		jQuery.ajax(
		{
		    data: ({email:email}),
		    global: false,
			type:'POST',
			url: '<?php echo $_SESSION['boot']->getAppPublic(); ?>user/checkEmail',
			success: function(msg)
			{
				if (parseInt(msg) > 0)
				{
					if (parseInt(msg) == 99)
					{
						jQuery('#emailNotification').html("Invalid Email. Please confirm.");
						errorEmail = 1;
						jQuery('#submitbutton').removeClass("btn-danger")
						jQuery('#submitbutton').addClass("btn-default")
					}
					else
					{
						jQuery('#emailNotification').html("Not Available");
						errorEmail = 1;
						jQuery('#submitbutton').removeClass("btn-danger")
						jQuery('#submitbutton').addClass("btn-default")
					}
				}
				else
				{
					jQuery('#emailNotification').html("Available");
					errorEmail = 0;
					jQuery('#submitbutton').removeClass("btn-default")
					jQuery('#submitbutton').addClass("btn-danger")
				}
				if (errorID == 0 && errorEmail == 0  && errorPhone == 0) { jQuery('#submitbutton').show(); } else { jQuery('#submitbutton').hide();}
			},
			error: function(ErrXMLHttpRequest, textStatus, errorThrown)
			{
				//TODO: something
			}
		});

	}
}

</script>

<h1 class="text-black fw-300 mb-3 d-sm-block d-md-none">
		Sign-up
</h1>
<div class="card p-4 rounded-plus bg-faded">
	  <form id="js-login" autocomplete="off" id="registration_form" action='<?php echo $_SESSION['boot']->getAppPublic(); ?>user/register' method="post">
			<input type="hidden" name="zetoken" value="<?php echo $_SESSION['zetoken']; ?>">

			<div class="row">
				<div class="col-xl-6 col-lg-6">
					<div class="form-group">
							<label class="form-label" for="fname">First Name</label>
							<input type="text" id="fname" name="db_user_first_name" class="form-control form-control-lg" placeholder="First Name" value="<?php echo $view[1]->firstname; ?>" required>
							<div class="invalid-feedback">No, you missed this one.</div>
							<div class="help-block">Your first name.</div>
					</div>
					<div class="form-group">
							<label class="form-label" for="mname">Middle Name</label>
							<input type="text" id="mname" name="db_user_middle_name" class="form-control form-control-lg" placeholder="Middle Name" value="<?php echo $view[1]->middlename; ?>">
							<div class="invalid-feedback">No, you missed this one.</div>
							<div class="help-block">Your middle name.</div>
					</div>
					<div class="form-group">
							<label class="form-label" for="flname">Last Name</label>
							<input type="text" id="lname" name="db_user_last_name" class="form-control form-control-lg" placeholder="Last Name" value="<?php echo $view[1]->lastname; ?>" required>
							<div class="invalid-feedback">No, you missed this one.</div>
							<div class="help-block">Your last name.</div>
					</div>
					<div class="form-group">
							<label class="form-label" for="national_identity">ID/Alien/Diplomatic No</label>
							<input type="text" id="national_identity" onBlur="checkID();" name="db_user_national_identity" class="form-control form-control-lg" placeholder="National Identity Number" value="<?php echo $view[1]->national_identity; ?>" required>
							<div class="invalid-feedback">No, you missed this one.</div>
							<div id="idNotification" class="help-block">Your national ID, alien or diplomatic number.</div>
					</div>
				</div>
				<div class="col-xl-6 col-lg-6">
					<div class="form-group">
							<label class="form-label" for="email">Email Address</label>
							<input type="email" id="email" name="db_user_emailAddress" onBlur="checkEmail();" class="form-control form-control-lg" placeholder="Email Address" value="<?php echo $view[1]->emailAddress; ?>" required>
							<div class="invalid-feedback">Sorry, you missed this one.</div>
							<div id="emailNotification" class="help-block">Your email address</div>
					</div>
					<div class="form-group">
							<label class="form-label" for="phone">Phone Number</label>
							<input type="text" id="phone" name="db_user_phone" onBlur="checkPhone();" class="form-control form-control-lg" placeholder="0700-000-000" value="<?php echo $view[1]->phone; ?>" required>
							<div class="invalid-feedback">Sorry, you missed this one.</div>
							<div id="phoneNotification" class="help-block">Phone number</div>
					</div>
					<div class="form-group">
							<label class="form-label" for="password">Password</label>
							<input type="password" id="password" name="db_user_password" class="form-control form-control-lg" placeholder="Password" value="" required>
							<div class="invalid-feedback">Sorry, you missed this one.</div>
							<div class="help-block">Your password</div>
					</div>
					<div class="form-group">
							<label class="form-label" for="password2">Re-enter Password</label>
							<input type="password" id="password2"  name="password2"  onBlur="checkEmail();" class="form-control form-control-lg" placeholder="Password Verification" value="" required>
							<div class="invalid-feedback">Sorry, you missed this one.</div>
							<div class="help-block">Your password, again</div>
					</div>
				</div>
				<!--div class="form-group text-left">
						<div class="custom-control custom-checkbox">
								<input type="checkbox" class="custom-control-input" id="rememberme">
								<label class="custom-control-label" for="rememberme"> Remember me for the next 30 days</label>
						</div>
				</div-->
						<!--div class="col-lg-6 pr-lg-1 my-2">
								<button type="submit" class="btn btn-info btn-block btn-lg">Sign in with <i class="fab fa-google"></i></button>
						</div-->
			</div>
			<div style="padding-top:20px" class="col-lg-12 col-xl-12 pl-lg-1 my-2">
					<button id="submitbutton" style="display:none" data-sitekey="6LdyzygaAAAAAJIt5GzzC9NFcmXVd-MaXUlg66TC" data-callback='onSubmit' type="submit" class="btn btn-default btn-block btn-lg g-recaptcha">Register</button>
			</div>

		</form>
</div>

<script src="https://www.google.com/recaptcha/api.js"></script>
<script src="/js/jquery.mask.min.js"></script>
<script>


jQuery(document).ready(function(){
	jQuery('#phone').mask('0000-000-000', { translation: {"0000-000-000": { pattern: /[0-9*]/ }} });
	jQuery('#national_identity').mask("0", { translation: {"0": { pattern: /[0-9*]/, recursive: true }} });
	jQuery('#email').mask("A", { translation: {"A": { pattern: /[\w@\-.+]/, recursive: true }} });
});

function onSubmit(token) {
	if (jQuery('#submitbutton').hasClass("btn-danger"))
	{
		jQuery('#submitbutton').fadeOut();
		document.getElementById("js-login").submit();
	}
}
</script>
