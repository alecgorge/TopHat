<?php

Admin::registerSubpage('content', 'create-page', __('admin', 'add-page'), 'CreatePage::display', -10);

class CreatePage {
	public static function display () {
		return "epic lulz";
   	}
}

?>
