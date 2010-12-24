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

			if(self::checkSession()) {
				//var_dump(CC_PUB_ADMIN);exit();
				cc_redirect(CC_PUB_ADMIN.'index.php?page=content&first=true', true);
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
		// cookie is in format (# of = at end)|(base64 of json uname and password assoc)
		$str = $_COOKIE['ln'];

		// no sense going further
		if(empty($str)) {
			return false;
		}

		$str = (array)explode('|', $str);

		// secert ninja stuff :)
		return json_decode(base64_decode($str[1].str_repeat('=', $str[0])));
   	}

	public static function checkCookie () {
		$res = self::unpackCookie();

		if(!$res) {
			return false;
		}

		$_SESSION['uname'] = $uname;
		$_SESSION['pword'] = $pword;
		$_SESSION['last_ip'] = $_SERVER['REMOTE_ADDR'];
	}

	public static function logout() {
		unset($_SESSION['uname']);
		unset($_SESSION['pword']);
		unset($_SESSION['last_ip']);
		session_destroy();
   	}

	/**
	 * Checks the validity of the session.
	 *
	 * @return boolean True if session is valid user. False otherwise. Also false if ips are different between requests.
	 */
	public static function checkSession () {
		// session is in format (# of = at end)|(base64 of json uname and password assoc)
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
		$_SESSION['last_ip'] = $current_ip;

		$smt = Database::select('users', array('value'), array('name = ? AND type = ?', $uname, 'user'));
		$row = $smt->fetch(PDO::FETCH_ASSOC);
		self::$currUser = new User($row);

		// correct password
		if($pword === $row['value']) {
			return true;
		}

		// wrong password
		return false;
   	}

	public static function refChecks () {
		// we do not want people have links sent to them that delete pages/users/groups
		$ref = parse_url($_SERVER['HTTP_REFERER']);
		if($ref['host'] !== NULL && $ref['host'] !== $_SERVER['HTTP_HOST']) {
			cc_logout();
		}
	}

	public static function bootstrap () {
		session_start();
		self::refChecks();

		Hooks::bind('post_login', 'Users::loginHandle');
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

	public static function currentUser () {

	}
}
Hooks::bind('system_before_admin_loaded', 'Users::bootstrap');

/**
 * Logs the user out and redirects them to the home page.
 */
function cc_logout () {
	Users::logout();

	cc_redirect("", true);
	exit();
}

class User {
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
		return (bool)$this->data['data'][$data];
	}
}

