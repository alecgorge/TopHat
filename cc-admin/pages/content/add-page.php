<?php

Admin::registerSubpage('content', 'create-page', 'Create Page', 'ContentPage::display', -10);

class CreatePage {
	public static function display () {
		echo "epic lulz";
   	}
}

?>
