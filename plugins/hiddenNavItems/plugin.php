<?php

class HideNavPlugin {
	public static $plugin;

	public static function doHide ($ref, $data, $options, &$continue) {
		if((!defined('CC_IS_ADMIN') || CC_IS_ADMIN == false) && $options['hide-from-nav'] === true)
			$continue = false; // skips it from being added to the menus
		return false;
	}

	public static function bootstrap () {
		self::$plugin->bind('admin_edit_custom_fields2', 'HideNavPlugin::showEditForm');
		self::$plugin->bind('admin_create_custom_fields2', 'HideNavPlugin::showEditForm');
		self::$plugin->filter('admin_edit_post_posted_values', 'HideNavPlugin::handleForm');
		self::$plugin->filter('admin_create_post_posted_values', 'HideNavPlugin::handleForm');
		self::$plugin->bind('content_parsenavigation_before', 'HideNavPlugin::doHide');
	}

	public static function handleForm ($values) {
		$values['settings']['hide-from-nav'] = ($_POST['hide-from-nav'] === 'hide' ? true : false);
		self::$currentState = $values['settings']['hide-from-nav'];
		return $values;
	}

	private static $currentState = null;
	public static function showEditForm (&$form) {
		$form->startFieldset('Options');

		$val = (!is_null(self::$currentState) ? self::$currentState : (bool)EditPage::$row['settings']['hide-from-nav']);
		$form->addInput(__('hide-nav-plugin', 'form-label'), 'checkbox', 'hide-from-nav', 'hide', ($val === true ? array('checked' => 'checked') : array()));
		$form->endFieldset();
	}
}
i18n::register('en_US', 'hide-nav-plugin', array(
	'title' => 'Hide navigation Items',
	'desc' => 'Allows you to hide certain items from displaying on the menu.',
	'form-label' => 'Hide page from menu'
));
HideNavPlugin::$plugin = new Plugin(__('hide-nav-plugin', 'title'), 'CanyonCMS Team' , __('hide-nav-plugin', 'desc'), '1.0');
HideNavPlugin::$plugin->bootstrap('HideNavPlugin::bootstrap');

