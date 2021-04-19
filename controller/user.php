<?php
include "../helper/recaptchalib.php";

class userClass
{
	public function __construct()
	{
	}

	public function keycloak()
	{
		require '/var/www/registrar/public/vendor/autoload.php';

		$provider = new Stevenmaguire\OAuth2\Client\Provider\Keycloak([
		    'authServerUrl'         => 'https://registrar.msea.go.ke:8443/auth',
		    'realm'                 => 'MSEARealm',
		    'clientId'              => 'registrar',
		    'clientSecret'          => '75579bec-2fc9-4b8d-90a7-49b50de9b404',
		    'redirectUri'           => 'https://registrar.msea.go.ke/user/keycloak'
		  //  'encryptionAlgorithm'   => 'RS256',                             // optional
		  //  'encryptionKeyPath'     => '../key.pem'                         // optional
		   // 'encryptionKey'         => 'contents_of_key_or_certificate'     // optional
		]);

		if (!isset($_GET['code'])) {

		    // If we don't have an authorization code then get one
		    $authUrl = $provider->getAuthorizationUrl();
		    $_SESSION['oauth2state'] = $provider->getState();
		    header('Location: '.$authUrl);
		    exit;

		// Check given state against previously stored one to mitigate CSRF attack
		} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

		    unset($_SESSION['oauth2state']);
		    exit('Invalid state, make sure HTTP sessions are enabled.');

		} else {

		    // Try to get an access token (using the authorization coe grant)
		    try {
		        $token = $provider->getAccessToken('authorization_code', [
		            'code' => $_GET['code']
		        ]);
		    } catch (Exception $e) {
		        exit('Failed to get access token: '.$e->getMessage());
		    }

		    // Optional: Now you have a token you can look up a users profile data
		    try {

		        // We got an access token, let's now get the user's details
		        $user = $provider->getResourceOwner($token);

		        // Use these details to create a new profile
		        //$resp = 'Hello '.json_encode($user->toArray());

						$email = $user->getEmail();

						//check if user exists...
						$res = db_fetch("","","","","emailAddress = '".$email."'","","user", false, false, null);
						if (sizeof($res) > 0)
						{
							//log in the dude
							session_regenerate_id();

							$_SESSION['authenticated'] = true;
							$_SESSION['user_logged'] = $res[0]['id'];
							$_SESSION['user_level'] = $res[0]['level'];
							$_SESSION['user_fname'] = $res[0]['first_name']. " ". $res[0]['last_name'];
							$_SESSION['user_name'] = $res[0]['username'];
							$_SESSION['user_email'] = $res[0]['emailAddress'];
							$_SESSION['user_county'] = $res[0]['ADED_county_ID'];
							$_SESSION['fingerprint'] = MD5($_SERVER['HTTP_USER_AGENT']."ejbatsair");
							$_SESSION['zetoken'] = md5($_SERVER['HTTP_USER_AGENT'].uniqid(rand(), true));
							$_SESSION['keycloaktoken'] = $token->getToken();
							$_SESSION['keycloaktoken_r'] = $token->getRefreshToken();

							db_update($_SESSION['user_logged'],'user',array('loggedin'),array($_SESSION['zetoken']));
							return renderView('index','index',$view);

						}
						else
						{
							//create the user, then log in the dude
							$fn = $user->getGivenName();
							$ln = $user->getLastName();
							$username = $user->getUsername();

							db_insert("user",array('first_name','last_name','username','password','emailAddress','level','activated'),array($fn,$ln,$email,'KEYCLOAK',$email,1,1));

							$res = db_fetch("","","","","emailAddress = '".$email."'","","user", false, false, null);

							session_regenerate_id();

							$_SESSION['authenticated'] = true;
							$_SESSION['user_logged'] = $res[0]['id'];
							$_SESSION['user_level'] = $res[0]['level'];
							$_SESSION['user_fname'] = $res[0]['first_name']. " ". $res[0]['last_name'];
							$_SESSION['user_name'] = $res[0]['username'];
							$_SESSION['user_email'] = $res[0]['emailAddress'];
							$_SESSION['user_county'] = $res[0]['ADED_county_ID'];
							$_SESSION['fingerprint'] = MD5($_SERVER['HTTP_USER_AGENT']."ejbatsair");
							$_SESSION['zetoken'] = md5($_SERVER['HTTP_USER_AGENT'].uniqid(rand(), true));
							$_SESSION['keycloaktoken'] = $token->getToken();
							$_SESSION['keycloaktoken_r'] = $token->getRefreshToken();

							db_update($_SESSION['user_logged'],'user',array('loggedin'),array($_SESSION['zetoken']));
							return renderView('index','index',$view);
						}




		    } catch (Exception $e) {
		        exit('Failed to get resource owner: '.$e->getMessage());
		    }

	}
}

	public function getOrg($org)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://crm.msea.go.ke/api/v1/organizations/search?query='.$org.'&limit=1');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

		$headers = array();
		$headers[] = 'Authorization: Token token=yhvwazsiv7_9LPy11R0kdQDGUN5GJ9qoXsCGN4ujn7XmskBL5VK5GGgKyg453-52';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);

		if (curl_errno($ch))
		{
				return false;
		}
		curl_close($ch);

		if ($result != "[]") return json_decode($result,true)[0]['id'];

		return "";
	}

	public function createUser($fn, $ln, $em, $org, $pn)
	{
		logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: Create details: ".$fn."-".$ln."-".$em."-".$org."-".$pn);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://crm.msea.go.ke/api/v1/users');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

		$zOrg = $this->getOrg($org);

		if ($org != "NA" and $org != "" and $zOrg == "")
		{
				$orgId = $this->createOrg($org);
		}

		if ($org == "NA" or $org == "")
		{
			$postparam = 'firstname='.$fn.'&lastname='.$ln.'&email='.$em.'&phone='.$pn;
			logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: Without Org: ".$postparam);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postparam);
		}
		else
		{
			logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: With Org");
			curl_setopt($ch, CURLOPT_POSTFIELDS, 'firstname='.$fn.'&lastname='.$ln.'&email='.$em.'&organization='.$org.'&phone='.$pn);
		}

		$headers = array();
		$headers[] = 'Authorization: Token token=yhvwazsiv7_9LPy11R0kdQDGUN5GJ9qoXsCGN4ujn7XmskBL5VK5GGgKyg453-52';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);

		logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: Create user result: ".$result);

		curl_close($ch);
	}

	public function createOrg($name)
	{
		logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: Create Org details: ".$name);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://crm.msea.go.ke/api/v1/organizations');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

		curl_setopt($ch, CURLOPT_POSTFIELDS, 'name='.$name);

		$headers = array();
		$headers[] = 'Authorization: Token token=yhvwazsiv7_9LPy11R0kdQDGUN5GJ9qoXsCGN4ujn7XmskBL5VK5GGgKyg453-52';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);

		logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: Create org result: ".$result);

		curl_close($ch);

		if ($result != "[]") return json_decode($result,true)[0]['id'];

		return "";
	}

	public function getUser($pn)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://crm.msea.go.ke/api/v1/users/search?query='.$pn.'&limit=1');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

		$headers = array();
		$headers[] = 'Authorization: Token token=yhvwazsiv7_9LPy11R0kdQDGUN5GJ9qoXsCGN4ujn7XmskBL5VK5GGgKyg453-52';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);

		if (curl_errno($ch))
		{
			return false;
		}
		curl_close($ch);

		if ($result != "[]") return true;

		return false;
	}

  public function contact_lookup()
	{
		logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: Starting");
		try
		{
			$resp = "";
			if (!isset($_GET['id']))
			{
				logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: No id. Ending");
				return " response:No number provided. Not making a call to CRM. Ending.";
			}

			logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: Cleaning: ".$_GET['id']);
			$number = cleanupPhone($_GET['id']);
			logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: ID present. Cleaned: ".$number);

			//if ID provided ... look it up in Registrar system
			$registrarUser = $this->getDetailsbyNumber($number);
			logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: Details found: ".$registrarUser);

			if ($registrarUser != "{}")
			{
				logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: Checking if CRM has record.");
				//check if CRM has contact; if not, create...
				if ($this->getUser($number))
				{
					//Exists
					logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: CRM has the record.");
				}
				else
				{
					logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: CRM does not have the record. Creating...");
					//Create user
					$r = json_decode($registrarUser);
					$this->createUser($r->contact->firstname, $r->contact->lastname, $r->contact->email, $r->contact->company, $r->contact->mobilephone);
				}
			}
			else
			{
				logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: No details in system. New caller.");
			}

			//make a call to crm
			logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: New call request to CRM");
			$this->crmCall($number);

			//return 3CX with contact details - if any.
			logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: Returning to 3CX. Ended.");
			return " response:".$registrarUser;

		}
		catch (exception $e)
		{
			logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "CRM_3CX: Exception .".$e);
		}
	}

	public function crmCall($number)
	{
		$ch = curl_init();

		$callid = strtoupper(mb_substr(bin2hex(random_bytes(4)), 0, 7));

		curl_setopt($ch, CURLOPT_URL, 'https://crm.msea.go.ke/api/v1/cti/G6JieVu6SrLy5oJGJeks4mdFpQ8');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'event=newCall&from='.$number.'&direction=in&to=0111028000&callId='.$callid);

		$headers = array();
		$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		curl_close($ch);
	}

	public function getDetailsbyNumber($pn)
	{
		//Look it up on the user db...
		$user = db_fetch("","","","","right(replace(replace(replace(u.phone,'+',''),' ',''),'-',''),9) = '".$pn."'", "u.*, r.name_ID", "user u left join registration r ON r.user_HD = u.id", false, false, null);

		//TODO: Look it up in the members
		//TODO: Look it up in the search (>registration) db

		if (sizeof($user) > 0)
		{
			$company = "NA";

			if (isset($user[0]['name_ID']) and $user[0]['name_ID'] != "")
			{
				$company = $user[0]['name_ID'];
			}

			$resp = json_encode(array("contact" =>
													array(
														"id"=> $user[0]['id'],
														"firstname" => $user[0]['first_name'],
														"lastname" => $user[0]['last_name'],
														"company" => $company,
														"email" => $user[0]['emailAddress'],
														"businessphone" => "",
														"businessphone2" => "",
														"mobilephone" => cleanupPhone($user[0]['phone']),
														"mobilephone2" => cleanupPhone($user[0]['phone']),
														"url" => ""
													)));

		 }
		 else
		 {
		 	$resp = "{}";
		 }

		 return $resp;
	}


	public function login()
	{
		if (isset($_POST['db_user_username']))
		{
			$username = strip_tags($_POST['db_user_username']);
			$password = strip_tags($_POST['db_user_password']);

			if ($username == "")
			{
				$view[0]->error = "Username is required.";
				$_SESSION['zetoken'] = md5($_SERVER['HTTP_USER_AGENT'].uniqid(rand(), true));
				$_POST['layout'] = "login";
				return renderView('user','loginPage',$view);
			}

			if ($password == "")
			{
				$view[0]->error = "Password is required.";
				$_SESSION['zetoken'] = md5($_SERVER['HTTP_USER_AGENT'].uniqid(rand(), true));
				$_POST['layout'] = "login";

				return renderView('user','loginPage',$view);
			}

			//validate username
			if(preg_match('/^[a-zA-Z0-9\.@]*$/', $username))
			{
				logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "Username Validation ok");

				//ok
				$table = "user";
				$where = " (username = '".$username."' or national_identity = '".$username."' or REPLACE(phone,'-','') = '0".substr(str_replace('-','',str_replace(' ','',$username)),-9)."')";
				$res = db_fetch("", "", "", "", $where, "", $table, false, false, null);

				$okay = "No";

				if (isset($res) && isset($res[0]))
				{
					if (isset($res[0]['loggedin']) && $res[0]['loggedin'] == "3")
					{
						$view[0]->error = "Account is locked. Please reset your password to unlock.";
						$_SESSION['zetoken'] = md5($_SERVER['HTTP_USER_AGENT'].uniqid(rand(), true));
						$_POST['layout'] = "single";
						return renderView('user','forgotPassPage',$view); //no logoutView, we're going to the login page
					}

					if ($res[0]['activated'] == 1)
					{
						logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "UNSA CHECK:".$_SESSION['unsa'] );
						$res2 = db_fetch("", "", "", "", "id=".$res[0]['id']." and password = '".str_replace($_SESSION['unsa'],"",$_POST['db_user_password'])."'" , "", "user", false, false, null);
						if (sizeOf($res2) >0)
						{
							$okay = "Yes";
						}

						if ($okay == "Yes")
						{
							session_regenerate_id();

							$_SESSION['authenticated'] = true;
							$_SESSION['user_logged'] = $res[0]['id'];
							$_SESSION['user_level'] = $res[0]['level'];
							$_SESSION['user_fname'] = $res[0]['first_name']. " ". $res[0]['last_name'];
							$_SESSION['user_name'] = $res[0]['username'];
							$_SESSION['user_email'] = $res[0]['emailAddress'];
							$_SESSION['user_county'] = $res[0]['ADED_county_ID'];
							$_SESSION['fingerprint'] = MD5($_SERVER['HTTP_USER_AGENT']."ejbatsair");
							$_SESSION['zetoken'] = md5($_SERVER['HTTP_USER_AGENT'].uniqid(rand(), true));

							if ($res[0]['loggedin'] != "")
							{
								$view[0]->message = "You were logged in at another location. You have been logged out from there.";
							}

							db_update($_SESSION['user_logged'],'user',array('loggedin'),array($_SESSION['zetoken']));

						}
					}
					else
					{
						logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "Not activated");

					}
				}
				else
				{
					logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "Username not found");

				}
			}
			else
			{
				logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "Username Validation bad");

			}
		}



		if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] == true)
		{
			logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "Authenticated");
			$view = array();
			if (isset($_POST['path']))
			{
				return renderView('index','default', " redirect:".$_SESSION['boot']->getAppPublic().$_POST['path']);
			}
			return renderView('index','index',$view);
		}
		else
		{
			if (isset($res[0]['activated']) and $res[0]['activated'] != 1)
			{
				$view[0]->message = "Your profile is not activated. If you have not received an activation email use the form below to resent the email. Ensure that you have checked the Spam/Junk folder.";
				$_POST['layout'] = "login";
				return renderView('user','notActivatedPage',$view); //no logoutView, we're going to the login page
			}
			else
			{
				$view[0]->error = "Username/Password incorrect.";
				$_POST['layout'] = "login";

				if (isset($res) && isset($res[0]))
				{
					if (isset($res[0]['loggedin']))
					{
						$att = intval($res[0]['loggedin']);
					}
					else
					{
						$att = 0;
					}

					if (isset($res[0]['id']))
					{
						$att++;
						$rs = db_update($res[0]['id'],'user',array("loggedin"),array($att));
						$view[0]->error = "Username/Password incorrect.";
					}
				}

				if (isset($_POST['path']))
				{
					$view['path'] = $_POST['path'];
				}
				$_SESSION['zetoken'] = md5($_SERVER['HTTP_USER_AGENT'].uniqid(rand(), true));

				return renderView('user','loginPage',$view); //no logoutView, we're going to the login page
			}
		}



	}

	public function checkUser()
	{
		$username = $_POST['user'];

		if(preg_match('/^[a-z\d_\.\@]{2,20}$/i', $username))
		{

		}
		else
		{
			if ($username != "")
			{
				return " response:99";
			}
		}

		$res = db_fetch("","","","","username = '".$username."'","","user",false,false,null);
		if (sizeOf($res) > 0)
		{
			return " response:0";
		}
		else
		{
			return " response:1";
		}
	}

	public function checkPhone()
	{
		$phone = cleanupPhone($_POST['phone']);

		$res = db_fetch("","","","","RIGHT(REPLACE(REPLACE(phone, '-',''), ' ',''),9) = '".$phone."'","","user",false,false,null);

		if (sizeOf($res) > 0)
		{
			return " response:1";
		}
		else
		{
			return " response:0";
		}
	}


		public function checkID()
		{
			$nationalid = $_POST['nationalid'];

			$res = db_fetch("","","","","national_identity = '".$nationalid."'","","user",false,false,null);
			if (sizeOf($res) > 0)
			{
				return " response:1";
			}
			else
			{
				return " response:0";
			}
		}

	public function checkEmail()
	{
		$email = $_POST['email'];

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		  return " response:99";
		}

		$res = db_fetch("","","","","emailAddress= '".str_replace("'","\'",$email)."'","","user",false,false,null);
		if (sizeOf($res) > 0)
		{
			return " response:".sizeOf($res);
		}
		else
		{
			return " response:".sizeOf($res);;
		}
	}

	public function resendActivation()
	{
		$email = strip_tags($_POST['emailAddress']);

		if ($email == "")
		{
			$view[0]->error = "Email Address is required.";
			return renderView('user','notActivatedPage',$view);
		}

		$res = db_fetch("","","","","emailAddress = '".str_replace("'","\'",$email)."'","","user",false,false,null);
		if ($res)
		{

			$content = sendActivationEmail($res[0]['id'],$res[0]['first_name'],$res[0]['emailAddress'], 15, null, "Use previously provided", "Use previously provided");

			//prepare response
			if ($content == "OK")
			{
				$view[0]->message = "Activation email resent. Please check ".$view[1]->emailAddress.".";
			}
			else
			{
				$view[0]->error = $content;
			}
		}
		else
		{
			$view[0]->error = "No account with specified email address found. Please check email address provided.";
		}

		return renderView('user','notActivatedPage',$view);
	}


	public function register()
	{
		$table = "user";

 		$_POST['email'] = $_POST['db_user_emailAddress'];
		$_POST['nationalid'] = $_POST['db_user_national_identity'];
		$_POST['phone'] = $_POST['db_user_phone'];

		$view[0]->error;
		if ($this->checkID() == 1) $view[0]->error .= "National ID already registered.<BR>";
		if ($this->checkPhone() == 1) $view[0]->error .= "Phone already registered.<BR>";
		if ($this->checkEmail() > 0) $view[0]->error .= "Email already registered.<BR>";

		if ($this->checkID() == 1 or $this->checkPhone() == 1 or $this->checkEmail() > 0)
		{
			$view[1]->firstname =  strip_tags($_POST['db_user_first_name']);
			$view[1]->middlename =  strip_tags($_POST['db_user_middle_name']);
			$view[1]->phone = strip_tags($_POST['db_user_phone']);
			$view[1]->national_identity = strip_tags($_POST['db_user_national_identity']);
			$view[1]->lastname =  strip_tags($_POST['db_user_last_name']);
			$view[1]->username = strip_tags($_POST['db_user_emailAddress']);
			$view[1]->password = strip_tags($_POST['db_user_password']);
			$view[1]->emailAddress = strip_tags($_POST['db_user_emailAddress']);

			$_POST['layout'] = "single";
			return renderView('user','registerPage',$view);
		}

		$_POST['db_user_username'] = $_POST['db_user_emailAddress'];
		if (isset($_POST['db_user_emailAddress']) && $_POST['db_user_emailAddress'] != "")
		{
			$view[1]->firstname =  strip_tags($_POST['db_user_first_name']);
			$view[1]->middlename =  strip_tags($_POST['db_user_middle_name']);
			$view[1]->lastname =  strip_tags($_POST['db_user_last_name']);
			$view[1]->username = strip_tags($_POST['db_user_emailAddress']);
			$view[1]->emailAddress = strip_tags($_POST['db_user_emailAddress']);
			$view[1]->password = strip_tags($_POST['db_user_password']);
			$view[1]->phone = strip_tags($_POST['db_user_phone']);
			$view[1]->national_identity = strip_tags($_POST['db_user_national_identity']);
			$view[1]->password = strip_tags($_POST['db_user_password']);

			//RECAPTCHA
			$failed = false;

			$privatekey = "6LdyzygaAAAAAFy20R5UIa_tIzJH5GkVby6QGksO";

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,"https://www.google.com/recaptcha/api/siteverify");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('secret' => $privatekey, 'response' => $_POST['g-recaptcha-response'])));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$response = curl_exec($ch);
			curl_close($ch);
			$arrResponse = json_decode($response, true);

			// verify the response
			if($arrResponse["success"] == true && $arrResponse["action"] == "submit" && $arrResponse["score"] >= 0.5) {
			    // valid submission
			    // go ahead and do necessary stuff
			} else {
//				$failed = true;
			}
			//END RECAPTCHA

			if ($failed)
			{
				$view[0]->message = "Captcha was wrong. Please try again.";
				if (isset($_POST['db_user_First_Name']))
				{
					$view[1]->firstname =  strip_tags($_POST['db_user_first_name']);
					$view[1]->middlename =  strip_tags($_POST['db_user_middle_name']);
					$view[1]->lastname =  strip_tags($_POST['db_user_last_name']);
					$view[1]->phone = strip_tags($_POST['db_user_phone']);
					$view[1]->national_identity = strip_tags($_POST['db_user_national_identity']);
					$view[1]->username = strip_tags($_POST['db_user_emailAddress']);
					$view[1]->password = strip_tags($_POST['db_user_password']);
					$view[1]->emailAddress = strip_tags($_POST['db_user_emailAddress']);
				}

				$_POST['layout'] = "single";
				$_SESSION['layout'] = "single";
				return renderView('user','registerPage',$view);
			}

			if (strlen($view[1]->password) < 6)
			{
				$view[0]->error = "Password length must be at least 6 characters containing both numbers and letters e.g. 1a2b3c";
				if (isset($_POST['db_user_first_name']))
				{
					$view[1]->firstname =  strip_tags($_POST['db_user_first_name']);
					$view[1]->middlename =  strip_tags($_POST['db_user_middle_name']);
					$view[1]->phone = strip_tags($_POST['db_user_phone']);
					$view[1]->national_identity = strip_tags($_POST['db_user_national_identity']);
					$view[1]->lastname =  strip_tags($_POST['db_user_last_name']);
					$view[1]->username = strip_tags($_POST['db_user_emailAddress']);
					$view[1]->password = strip_tags($_POST['db_user_password']);
					$view[1]->emailAddress = strip_tags($_POST['db_user_emailAddress']);
				}

				$_POST['layout'] = "single";
				return renderView('user','registerPage',$view);
			}

			if ($_POST['db_user_password'] != $_POST['password2'])
			{
				$view[0]->error = "Passwords must match.";
							if (isset($_POST['db_user_first_name']))
				{
					$view[1]->firstname =  strip_tags($_POST['db_user_first_name']);
					$view[1]->middlename =  strip_tags($_POST['db_user_middle_name']);
					$view[1]->phone = strip_tags($_POST['db_user_phone']);
					$view[1]->national_identity = strip_tags($_POST['db_user_national_identity']);
					$view[1]->lastname =  strip_tags($_POST['db_user_last_name']);
					$view[1]->username = strip_tags($_POST['db_user_emailAddress']);
					$view[1]->password = strip_tags($_POST['db_user_password']);
					$view[1]->emailAddress = strip_tags($_POST['db_user_emailAddress']);
				}

				$_POST['layout'] = "single";
				return renderView('user','registerPage',$view);
			}
			if (!validEmail($_POST['db_user_emailAddress']))
			{
				$view[0]->error = "Email must be valid!";
							if (isset($_POST['db_user_first_name']))
				{
					$view[1]->phone = strip_tags($_POST['db_user_phone']);
					$view[1]->national_identity = strip_tags($_POST['db_user_national_identity']);
					$view[1]->firstname =  strip_tags($_POST['db_user_first_name']);
					$view[1]->middlename =  strip_tags($_POST['db_user_middle_name']);
					$view[1]->lastname =  strip_tags($_POST['db_user_last_name']);
					$view[1]->password = strip_tags($_POST['db_user_password']);
					$view[1]->emailAddress = strip_tags($_POST['db_user_emailAddress']);
					$view[1]->username = strip_tags($_POST['db_user_emailAddress']);
				}

				$_POST['layout'] = "single";
				return renderView('user','registerPage',$view);
			}
			$insertId = db_insert_form($_POST,$table);

			$content = sendActivationEmail($insertId,$view[1]->firstname,$view[1]->emailAddress,15,1, $view[1]->username, $view[1]->password);

			//prepare response
			if ($content == "OK")
			{
				$view[0]->message = "You have been registered. Please check ".$view[1]->emailAddress." for an activation email.";
			}
			else
			{
				$view[0]->error = $content;
			}

			$_POST['layout'] = "single";
			return renderView("index","default",$view);
		}
		else
		{
				if (isset($_POST['db_user_first_name']))
				{
					$view[1]->firstname =  strip_tags($_POST['db_user_first_name']);
					$view[1]->middlename =  strip_tags($_POST['db_user_middle_name']);
					$view[1]->lastname =  strip_tags($_POST['db_user_last_name']);
					$view[1]->phone = strip_tags($_POST['db_user_phone']);
					$view[1]->national_identity = strip_tags($_POST['db_user_national_identity']);
					$view[1]->username = strip_tags($_POST['db_user_emailAddress']);
					$view[1]->password = strip_tags($_POST['db_user_password']);
					$view[1]->emailAddress = strip_tags($_POST['db_user_emailAddress']);
				}

				$view[0]->message = "Please fill in all the fields.";
				return renderView('user','registerPage',$view);
		}
	}

	public function activate()
	{
		if (isset($_GET['cd']))
		{
			$cd = strip_tags($_GET['cd']);
			$res = db_fetch("","","","","activated = '".$cd."' and activated != '1' and activated != '0'","","user",false,false,null);
			if ($res)
			{
				session_regenerate_id();

				$_SESSION['authenticated'] = true;
				$_SESSION['user_logged'] = $res[0]['id'];
				$_SESSION['user_class'] = $res[0]['proc_class'];
				$_SESSION['user_level'] = $res[0]['level'];
				$_SESSION['user_name'] = $res[0]['username'];
				$_SESSION['user_fname'] = $res[0]['first_name'];
				$_SESSION['user_email'] = $res[0]['emailAddress'];
				$_SESSION['fingerprint'] = MD5($_SERVER['HTTP_USER_AGENT']."ejbatsair");
				$_SESSION['zetoken'] = md5($_SERVER['HTTP_USER_AGENT'].uniqid(rand(), true));

				db_update($_SESSION['user_logged'],'user',array('loggedin'),array($_SESSION['zetoken']));

				$con = db_fetch("", "", "", "", "id = ".$res[0]['id'], "", "user", false, false, null);

				$id = $res[0]['id'];
				$label = array('activated');
				$data = array(1);
				$r = db_update($id,'user',$label, $data);
				$view[0]->message = "Your account has been successfully activated. Please log in to proceed.";
				$_POST['layout'] = "login";
				return renderView('user','loginPage',$view);
			}
			else
			{
				$_POST['layout'] = "login";
				$view[0]->error = "Account already activated. Please proceed with login.";
				return renderView('user','loginPage',$view);
			}
		}
		else
		{
			$view[0]->error = "Could not find token for activation.";
			return renderView('user','resendActivation',$view);
		}
	}

	public function crmlogout()
	{
			header('Location: https://crm.msea.go.ke/#logout');
	}

	public function logoutcrm()
	{
		if (isset($_SESSION['user_logged']) && $_SESSION['user_logged'] != "")
		{
			db_update($_SESSION['user_logged'],'user',array('loggedin'),array(""));
		}
		$_SESSION['authenticated'] = false;
		$_SESSION['user_logged'] = "";
		$_SESSION['user_level'] = "";
		$_SESSION['user_name'] = "";
		unset($_SESSION['db_relations']);
		$_SESSION['user_email'] = "";
		$_SESSION['fingerprint'] = "";

		session_regenerate_id();
		$view[0]->message = "You have been logged out.".$resp;

		$_SESSION['zetoken'] = md5($_SERVER['HTTP_USER_AGENT'].uniqid(rand(), true));
		$_POST['layout'] = "login";

		header('Location: https://registrar.msea.go.ke:8443/auth/realms/MSEARealm/protocol/openid-connect/logout?access_token='.$_SESSION['keycloaktoken'].'&post_logout_redirect_uri=https%3A%2F%2Fregistrar.msea.go.ke/user/crmlogout');
	}

	public function logout()
	{
		if (isset($_SESSION['user_logged']) && $_SESSION['user_logged'] != "")
		{
			db_update($_SESSION['user_logged'],'user',array('loggedin'),array(""));
		}
		$_SESSION['authenticated'] = false;
		$_SESSION['user_logged'] = "";
		$_SESSION['user_level'] = "";
		$_SESSION['user_name'] = "";
		unset($_SESSION['db_relations']);
		$_SESSION['user_email'] = "";
		$_SESSION['fingerprint'] = "";

		session_regenerate_id();
		$view[0]->message = "You have been logged out.".$resp;

		$_SESSION['zetoken'] = md5($_SERVER['HTTP_USER_AGENT'].uniqid(rand(), true));
		$_POST['layout'] = "login";

		if (isset($_SESSION['keycloaktoken']))
		{
				header('Location: https://registrar.msea.go.ke:8443/auth/realms/MSEARealm/protocol/openid-connect/logout?access_token='.$_SESSION['keycloaktoken'].'&post_logout_redirect_uri=https%3A%2F%2Fregistrar.msea.go.ke');
		}
		else
		{
			return renderView('user','loginPage',$view); //no logoutView, we're going to the login page
		}
	}

	public function loginPage()
	{
		$view = "";
		if (isset($_GET['path']))
		{
			$view['path'] = $_GET['path'];
		}
		$_SESSION['zetoken'] = md5($_SERVER['HTTP_USER_AGENT'].uniqid(rand(), true));
		$_POST['layout'] = "login";
		return renderView(__CLASS__,__METHOD__,$view);
	}

	public function registerPage()
	{
		$view[1]->fullname = "";
		$view[1]->username = "";
		$view[1]->national_identity = "";
		$view[1]->phone = "";
		$view[1]->password = "";
		$view[1]->emailAddress = "";
		$view[1]->level = "";
		$_POST['layout'] = "single";
		return renderView(__CLASS__,__METHOD__,$view);
	}


	public function index()
	{
		if (intval($_SESSION['user_level']) == 1 or intval($_SESSION['user_level'] == 2))
		{
			require_once "../controller/consultant.php";
			$clazz = new consultantClass;
			return $clazz->index();
		}
		$view = array();
		$_POST['layout'] = "login";
		return renderView(__CLASS__,__METHOD__,$view);
	}

	public function changePassPage()
	{
		$view[1]->password = "";
		$_POST['layout'] = "login";
		return renderView(__CLASS__,__METHOD__,$view);
	}

	public function resetPage()
	{
		$_SESSION['passtoken'] = $_GET['token'];
		$view[1] = "";
		$_POST['layout'] = "single";
		return renderView(__CLASS__,__METHOD__,$view);
	}

	public function forgotPassPage()
	{
		$view = "";
		$_POST['layout'] = "single";
		return renderView(__CLASS__,__METHOD__,$view);
	}

	public function reset()
	{
		if (
		isset($_POST["db_user_username"]) && $_POST["db_user_username"] != "" &&
		isset($_POST["password"]) && $_POST["password"] != "" &&
		isset($_POST["password2"]) && $_POST["password2"] != ""
				)
		{
			$newPassword = $_POST["password"];
			$newPassword2 = $_POST["password2"];

			if ($newPassword == "")
			{
				$view[0]->error = "New password is required.";
				return renderView('user','resetPage',$view);
			}

			$pattern = '/^\S*(?=\S{6,})(?=\S*[\W])(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/';
			if (!preg_match($pattern,$newPassword))
			{
				$_POST['layout'] = "single";
				$view[0]->error = "Password length minimum 6 characters required. Atleast 1 upper, 1 lower, 1 number and 1 special character required.";
				return renderView('user','resetPage',$view);
			}

			if ($newPassword2 == $newPassword)
			{
				$table = "user";
				$where = "username = '".strip_tags($_POST['db_user_username'])."'";
				$res = db_fetch("", "", "", "", $where, "", $table, false, false, null);
				logger(__FILE__,__LINE__,__CLASS__,__METHOD__, print_r($res,true));

				if (isset($res) && isset($res[0]))
				{
					logger(__FILE__,__LINE__,__CLASS__,__METHOD__,$res[0]['token']  ."=". strip_tags($_POST['token'])  ." && ".  time()  ."<".  strtotime($res[0]['token_expiry']));
					if ($res[0]['token'] == strip_tags($_POST['token']) && time() < strtotime($res[0]['token_expiry']))
					{
						$label = array("password","loggedin","token_expiry");
						$data = array($newPassword,"0","2012-01-01 00:00:00");

						logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "PASSWORD: " . $newPassword . "; MD5: " . MD5($newPassword) );

						db_update($res[0]['id'],$table,$label,$data);
						$view[0]->message = "password changed.";

						$_SESSION['zetoken'] = md5($_SERVER['HTTP_USER_AGENT'].uniqid(rand(), true));

						$_POST['layout'] = "login";
						return renderView('user','loginPage',$view);
					}
					else
					{
						$_POST['layout'] = "single";
						$view[0]->error = "Token is invalid or has expired. Please request for an account reset again to proceed.";
						return renderView('index','index',$view);
					}
				}
				else
				{
					$_POST['layout'] = "single";
					$view[0]->error = "Username not found. Please try again.";
					return renderView('index','index',$view);
				}
			}
			else
			{
				$_POST['layout'] = "single";
				$view[0]->error = "new passwords do not match.";
				return renderView('user','resetPage',$view);
			}

		}
		else
		{
			$view[0]->error = "Username and passwords are required for reset.";
			return renderView('index','index',$view);
		}
	}

	public function forgotPass()
	{
		if (isset($_POST["db_user_emailAddress"]) && $_POST["db_user_emailAddress"] != "")
		{
			$table = "user";
			$where = "emailAddress = '".strip_tags($_POST["db_user_emailAddress"])."'";
			$res = db_fetch("", "", "", "", $where, "", $table, false, false, null);

			if (isset($res) && isset($res[0]))
			{
				$pass = generateRandom(7);
				$to = $res[0]['emailAddress'];
				$subject = "Change Password";
				$body = "You requested for a password reset. Please use the link below to reset your password.<BR><BR><b>Your username is:</b> ".$res[0]['username']."<BR><BR> This token is valid for only 15 minutes.:<BR><BR>".$_SESSION['boot']->siteAddress . "user/resetPage?token=" . $pass;
				$clazz = new mailClass();
				$content = $clazz->send($to,$body,$subject, null);

				$label = array("token_expiry","token");
				$data = array($sDate = date("Y-m-d H:i:s", strtotime("+15 minutes")),$pass);
				db_update($res[0]['id'], "user", $label, $data);

				$_POST['layout'] = "login";
				$view[0]->message = "Account reset instructions have been sent to email address <b>".$to."</b>.";
				return renderView("index","default",$view);
			}
			else
			{
				$_POST['layout'] = "login";
				$view[0]->error = "The email address you provided is not associated with any account";
				return renderView('user','forgotPassPage',$view);
			}
		}
		else
		{
			$_POST['layout'] = "login";
			$view[0]->error = "Email address is requried.";
			return renderView('user','forgotPassPage',$view);
		}
	}

	public function changePass()
	{
		$table = "user";
		$where = "id = ".$_SESSION['user_logged'];
		$res = db_fetch_pass("", "", "", "", $where, "", $table, false, false, null);

		if (isset($res) && isset($res[0]) && isset($_POST['db_user_password']))
		{
			//change the password
			$oldPassword = strip_tags($_POST['db_user_password']);
			$newPassword = strip_tags($_POST['password']);
			$newPassword2 = strip_tags($_POST['password2']);


			if ($oldPassword == "")
			{
				$view[0]->error = "Old password is required.";
				return renderView('user','changePassPage',$view);
			}

			if ($newPassword == "")
			{
				$view[0]->error = "New password is required.";
				return renderView('user','changePassPage',$view);
			}

			if (!preg_match($pattern,$newPassword))
			{
				$view[0]->error = "Passwords must be a combination of upper case letters, lower case letters and numbers if at least 7 characters.";
				return renderView('user','changePassPage',$view);
			}

			if ($newPassword == $oldPassword)
			{
				$view[0]->error = "Old and new passwords must be different.";
				return renderView('user','changePassPage',$view);
			}

			if ($newPassword2 == $newPassword)
			{
				if (MD5($oldPassword) == $res[0]['password'])
				{
					$label = array("password");
					$data = array($newPassword);
					db_update($_SESSION['user_logged'],$table,$label,$data);
					$view[0]->message = "password changed.";
					return renderView(__CLASS__,__METHOD__,$view);
				}
				else
				{
					$view[0]->error = "old password is incorrect.";
					return renderView('user','changePassPage',$view);
				}
			}
			else
			{
				$view[0]->error = "new passwords do not match.";
				return renderView('user','changePassPage',$view);
			}
		}
		else
		{
			$view[0]->error = "User not found or session timed out";
			return renderView('index','index',$view);
		}
	}
}
