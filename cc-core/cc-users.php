<?php

class Users {
	/**
	 * @var boolean If the user is logged in then true.
	 */
	public static $isValid = false;

	/**
	 * @var string The username from the session.
	 */
	public static $uname;

	/**
	 * @var string The password hash from the session.
	 */
	public static $phash;

	private static $currUser;

	public static function isValid () {
		return self::$isValid;
	}

	public static function loginHandle () {
		if(check_post('cc_login_uname', 'cc_login_passwd', 'cc_login_login')) {
			// for security, we don't want session fixation :(
			session_regenerate_id();

			$_SESSION['uname'] = $_POST['cc_login_uname'];
			$_SESSION['pword'] = hash('whirlpool', $_POST['cc_login_passwd']);
			$_SESSION['last_ip'] = $_SERVER['REMOTE_ADDR'];
			$_SESSION['last_user_agent'] = $_SERVER['HTTP_USER_AGENT'];

			if(self::checkSession()) {
				//var_dump(TH_PUB_ADMIN);exit();
				if($_POST['cc_login_remember'] == "yes") {
					$host = $_SERVER['HTTP_HOST'];
					if(substr($host,0,4) == "www.") {
						$host = substr($host,3);
					}
					setcookie('ln', self::packCookie(), time()+60*60*24*30*12);
				}
				cc_redirect(TH_PUB_ADMIN, true);
           	}
			else {
				Filters::bind('post_output_login', 'Users::outputError');
			}
       	}
	}

	public static function outputError () {
		return Message::error(__('admin', 'bad-uname-pass'));
	}

	/**
	 * Takes the cookie string and turns it into a array.
	 *
	 * @return array The assoc array of uname and pword from the cookie.
	 */
	public static function unpackCookie() {
		// cookie is in format (# of = at end)(base64 of json uname and password assoc)
		if(!array_key_exists('ln', $_COOKIE)) return false;
		
		$str = gzinflate(base64_decode($_COOKIE['ln']));

		// no sense going further
		if(empty($str)) {
			return false;
		}

		// secert ninja stuff :)
		return (array)json_decode(base64_decode(substr($str, 1).str_repeat('=', substr($str, 0, 1))));
   	}

	public static function packCookie() {
		$base = base64_encode(json_encode(array('uname'=>$_SESSION['uname'], 'pword' => $_SESSION['pword'])));
		$t = trim($base, '=');
		return base64_encode(gzdeflate(strlen($base)-strlen($t).$t,9));
	}

	public static function checkCookie () {
		$res = self::unpackCookie();

		if(!$res) {
			return false;
		}

		$_SESSION['uname'] = $res['uname'];
		$_SESSION['pword'] = $res['pword'];
		$_SESSION['last_ip'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['last_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
	}

	public static function logout() {
		unset($_SESSION['uname']);
		unset($_SESSION['pword']);
		unset($_SESSION['last_ip']);
		session_regenerate_id();
		session_destroy();
		setcookie("ln", "", time()-60*60*24*12);
   	}

	/**
	 * Checks the validity of the session.
	 *
	 * @return boolean True if session is valid user. False otherwise. Also false if ips are different between requests.
	 */
	public static function checkSession () {
		// session is in format (# of = at end)|(base64 of json uname and password assoc)
		if(!array_key_exists('uname', $_SESSION) || !array_key_exists('pword', $_SESSION) || !array_key_exists('last_ip', $_SESSION)) return false;

		$uname = $_SESSION['uname'];
		$pword = $_SESSION['pword'];
		$last_ip = $_SESSION['last_ip'];
		$current_ip = $_SERVER['REMOTE_ADDR'];

		// session not set.
		if(empty($uname) || empty($pword)) {
			return false;
		}

		// session spoofing!!
		if($last_ip !== $current_ip) {
			return false;
		}
		if($_SESSION['last_user_agent'] != $_SERVER['HTTP_USER_AGENT']) {
			return false;
		}
		$_SESSION['last_ip'] = $current_ip;

		$v = self::validate($uname, $pword, true);
		if($v) {
			self::$currUser = $v;
			return true;
		}
		return false;
   	}

	/**
	 * Test if a login is correct.
	 *
	 * @static
	 * @param  $username The username to test.
	 * @param  $password The password to test.
	 * @return bool|User If the login is successful, the new user instance is returned; otherwise false is returned.
	 */
	public static function validate ($username, $password, $alreadyHashed = false) {
		$pword = $alreadyHashed ? $password : hash('whirlpool', $password);

		$smt = Database::select('users', '*', array('name = ? AND type = ?', $username, 'user'));
		$row = $smt->fetch(PDO::FETCH_ASSOC);
		if($pword === $row['value']) {
			return new User($row);
		}
		return false;
	}

	public static function refChecks () {
		// we do not want people have links sent to them that delete pages/users/groups
		if(array_key_exists('HTTP_REFERER', $_SERVER)) {
			$ref = parse_url($_SERVER['HTTP_REFERER']);
			if($ref['host'] !== NULL && $ref['host'] !== $_SERVER['HTTP_HOST']) {
				cc_logout();
			}
		}
	}

	public static function bootstrap () {
		session_start();
		self::refChecks();

		Users::loginHandle();
		self::$isValid = Users::checkSession();
		if(!self::$isValid) {
			Users::checkCookie();
			self::$isValid = Users::checkSession();
		}
	}

	public static function allGroups () {
		$rows = Database::select('users', '*', array('`type` = ?', 'group'), array('name', 'ASC'))->fetchAll(PDO::FETCH_ASSOC);

		$r = array();
		foreach($rows as $k => $v) {
			$r[$v['users_id']] = new Group($v);
		}

		return $r;
	}

	public static function allUsers () {
		$rows = Database::select('users', '*', array('`type` = ?', 'user'), array('name', 'ASC'))->fetchAll(PDO::FETCH_ASSOC);

		$r = array();
		foreach($rows as $k => $v) {
			$r[$v['users_id']] = new User($v);
		}

		return $r;
	}

	/**
	 * @static
	 * @return User The currently logged in user
	 */
	public static function currentUser () {
		return self::$currUser;
	}
}
Hooks::bind('system_after_content_load', 'Users::bootstrap');

/**
 * Logs the user out and redirects them to the home page.
 */
function cc_logout () {
	Users::logout();

	cc_redirect("", true);
	exit();
}

class User {
	/**
	 * @var array An associative array that is the columns and values of the DB.
	 */
	private $data;
	public function  __construct($name) {
		if(is_string($name)) {
			$this->data = DB::select('users', '*', array('type = ? AND name = ?', 'user', $name))->fetchAll(PDO::FETCH_ASSOC);
			$this->data = $this->data[0];
		}
		else if (is_array ($name)) {
			$this->data = $name;
		}
		else {
			$this->data = DB::select('users', '*', array('users_id = ?', $name))->fetchAll(PDO::FETCH_ASSOC);
			$this->data = $this->data[0];
		}
	}

	public function getId () {
		return $this->data['users_id'];
	}

	public function getName () {
		return $this->data['name'];
	}

	/**
	 *
	 * @return Group The group corresponding to the user.
	 */
	public function getGroup () {
		return new Group($this->data['group']);
	}

	public function passwordHash () {
		return $this->data['value'];
	}
}

class Group {
	private $data;
	public function  __construct($name) {
		if(is_string($name)) {
			$data = DB::select('users', '*', array('type = ? AND name = ?', 'group', $name))->fetchAll(PDO::FETCH_ASSOC);
			$data = $data[0];
		}
		else if (is_array ($name)) {
			$data = $name;
		}
		else {
			$data = DB::select('users', '*', array('users_id = ?', $name))->fetchAll(PDO::FETCH_ASSOC);
			$data = $data[0];
		}
		$data['data'] = unserialize($data['data']);
		$this->data = $data;
	}

	public function getName () {
		return $this->data['name'];
	}

	public function getId () {
		return $this->data['users_id'];
	}

	public function getPermissions () {
		return $this->data['data'];
	}

	public function isAllowed ($data) {
		return ($this->data['data']['permissions'][$data] == true);
	}
}

