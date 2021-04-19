
<h1 class="text-white fw-300 mb-3 d-sm-block d-md-none">
		Reset password
</h1>
<div class="card p-4 rounded-plus bg-faded">

	  <form id="js-login" autocomplete="off" id="registration_form" action='<?php echo $_SESSION['boot']->getAppPublic(); ?>user/reset' method="post">
				<input type="hidden" name="zetoken" value="<?php echo $_SESSION['zetoken']; ?>">
				<input type="hidden" name="token" value="<?php echo $_SESSION['passtoken']; ?>">

				<div class="form-group">
						<label class="form-label" for="username">Email Address</label>
						<input type="text" id="username" name="db_user_username" class="form-control form-control-lg" placeholder="Email Address" value="" required>
						<div class="invalid-feedback">No, you missed this one.</div>
						<div class="help-block">Your email address.</div>
				</div>
				<div class="form-group">
						<label class="form-label" for="password">Password</label>
						<input type="password" id="password" name="password" class="form-control form-control-lg" placeholder="Password" value="" required>
						<div class="invalid-feedback">Sorry, you missed this one.</div>
						<div class="help-block">Your password</div>
				</div>
				<div class="form-group">
						<label class="form-label" for="password2">Re-enter Password</label>
						<input type="password" id="password2"  name="password2"  class="form-control form-control-lg" placeholder="Password Verification" value="" required>
						<div class="invalid-feedback">Sorry, you missed this one.</div>
						<div class="help-block">Your password, again</div>
				</div>
				<!--div class="form-group text-left">
						<div class="custom-control custom-checkbox">
								<input type="checkbox" class="custom-control-input" id="rememberme">
								<label class="custom-control-label" for="rememberme"> Remember me for the next 30 days</label>
						</div>
				</div-->
				<div class="row no-gutters">
						<!--div class="col-lg-6 pr-lg-1 my-2">
								<button type="submit" class="btn btn-info btn-block btn-lg">Sign in with <i class="fab fa-google"></i></button>
						</div-->
						<div class="col-lg-12 pl-lg-1 my-2">
								<button id="js-login-btn" type="submit" class="btn btn-danger btn-block btn-lg g-recaptcha">Reset</button>
						</div>
				</div>
		</form>
</div>
