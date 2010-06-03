<?php

Admin::registerSubpage('content', 'create-page', __('admin', 'add-page'), 'ContentPage::display', -10);

class CreatePage {
	public static function display () {
		echo "epic lulz";
   	}
}

?>
