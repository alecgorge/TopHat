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
			$_SESSION['uname'] = $_POST['cc_login_uname'];
			$_SESSION['pword'] = hash('whirlpool', $_POST['cc_login_passwd']);
			$_SESSION['last_ip'] = $_SERVER['REMOTE_ADDR'];

			if(self::checkSession()) {
				//var_dump(CC_PUB_ADMIN);exit();
				cc_redirect(CC_PUB_ADMIN.'index.php?page=dashboard&first=true', true);
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

		// correct password
		if($pword === $row['value']) {
			return true;
		}

		// wrong password
		return false;
   	}

	public static function bootstrap () {
		session_start();

		Hooks::bind('post_login', 'Users::loginHandle');
		self::$isValid = Users::checkSession();
		if(!self::$isValid) {
			Users::checkCookie();
			self::$isValid = Users::checkSession();
		}
	}
}
Hooks::bind('system_ready', 'Users::bootstrap');

class User {

}
?>
