<?php

Admin::registerSubpage('content', 'edit-page', 'Edit Page', 'EditPage::display');

class EditPage {
	public static function display () {
		i18n::set('admin');

       	echo sprintf("<h2>%s</h2>", __('edit-page'));

		$form = new Form('self', 'post', 'edit_page');

		$form->startFieldset('Page Information');
			$form->addInput('Page Title', 'text', 'page_title', '', array('class' => 'large'));
			$form->addSelectList('Content Type', 'content_type', array('asdf' => 'Page', 'asdf2' => 'Blog Post'), NULL, 'asdf2');
			$form->addSelectList('Theme Override', 'theme', array('-1' => 'Default Theme'));
       	$form->endFieldset();

		$form->startFieldset('Menu Information');
			$form->addInput('Menu Title', 'text', 'menutitle');
			$form->addInput('Slug (used for URL)', 'text', 'slug');
		$form->endFieldset();

		plugin('admin_editpage_custom_fields', array(&$form));

		$form->startFieldset('Content');
			$form->addTextarea('', 'content');
		$form->endFieldset();

		plugin('admin_editpage_custom_fields2', array(&$form));

		$form->startFieldset('Save');
			$form->addSubmit('Save Changes', 'save');
		$form->endFieldset();

		echo $form->endAndGetHTML();

		i18n::restore();
   	}
}

?>
