<?php

Admin::registerPage('content', 'Content Management', 'ContentPage::display', -10);

class ContentPage {
	public static function display () {
		echo "epic lulz";
   	}
}

?>
