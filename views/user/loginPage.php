<?php
$_SESSION['unsa'] = uniqid();
logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "GENERATING SESSION UNIQUE ID: " .$_SESSION['unsa']);
?>
<script type="text/javascript">
function ver()
{
	jQuery("#password").val('<?php echo $_SESSION['unsa']; ?>'+jQuery.md5(jQuery("#password").val()));
	return true;
}
</script>

    <h1 class="text-white fw-300 mb-3 d-sm-block d-md-none">
        Login
    </h1>
    <div class="card p-4 rounded-plus bg-faded">
        <form id="js-login" onSubmit="return ver();" autocomplete="off" action='<?php echo $_SESSION['boot']->getAppPublic(); ?>user/login' method="post" id="login-form">
            <input type="hidden" name="zetoken" value="<?php echo $_SESSION['zetoken']; ?>">

            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="db_user_username" class="form-control form-control-lg" placeholder="Username" value="" required>
                <div class="invalid-feedback">No, you missed this one.</div>
                <div class="help-block">Your unique username to app</div>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="db_user_password" class="form-control form-control-lg" placeholder="Password" value="" required>
                <div class="invalid-feedback">Sorry, you missed this one.</div>
                <div class="help-block">Your password</div>
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
                    <button id="js-login-btn" type="submit" class="btn btn-danger btn-block btn-lg">Secure login</button>
                </div>
								<a href="/user/forgotPassPage" class="help-block">Forgot Password?</a>
								<!--a href="/user/keycloak" class="help-block">SSO</a-->
							</div>
            </div>
        </form>
    </div>
