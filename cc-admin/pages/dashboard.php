<?php

Admin::registerPage('dashboard', 'Dashboard', 'Dashboard::display', -10);

class Dashboard {
	public static function display () {
		var_dump('i am teh bashbord');
	}
}

?>
