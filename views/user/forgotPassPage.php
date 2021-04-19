<h1 class="text-white fw-300 mb-3 d-sm-block d-md-none">
		Secure login
</h1>
<div class="card p-4 rounded-plus bg-faded">
	  <form id="js-login" onSubmit="return ver();" autocomplete="off" action='<?php echo $_SESSION['boot']->getAppPublic(); ?>user/forgotPass' method="post" id="login-form" class="smart-form client-form">
				<input type="hidden" name="zetoken" value="<?php echo $_SESSION['zetoken']; ?>">

				<div class="form-group">
						<label class="form-label" for="email">Email Address</label>
						<input type="email" id="email" name="db_user_emailAddress"class="form-control form-control-lg" placeholder="Email Address" value="" required>
						<div class="invalid-feedback">No, you missed this one.</div>
						<div class="help-block">Your email address</div>
				</div>
				<div class="row no-gutters">
						<div class="col-lg-12 pl-lg-1 my-2">
								<button id="js-login-btn" type="submit" class="btn btn-danger btn-block btn-lg">Reset</button>
						</div>
				</div>
		</form>
</div>
