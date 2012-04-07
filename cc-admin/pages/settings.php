<?php

Admin::registerPage('settings', __('admin', 'settings'), 'SettingsPage::display', 2);
// AdminSidebar::registerForPage('plugins', 'PluginAdminPage::getMore');

class SettingsPage {
	public static function display () {
        $r = "";
		if($_POST['cc_form'] == 'settings') {
			$name_lookup = array();

			Database::beginTransaction();
			foreach($_POST as $key => $value) {
				if($key == 'cc_form') continue;

				if(substr($key, 0, 12) == 'cc_settings_') {
					$name_lookup[substr($key, 12)] = explode('|', $value);
					continue;
				}
				$setting_name = $key;
				//var_dump(array_key_exists($key, $name_lookup),$name_lookup);

				if(!array_key_exists($setting_name, $name_lookup)) continue;

				if($key == 'clean-urls') $value = (bool) $value;

				Database::update('settings', array('data'), array(serialize($value)), array('package = ? AND name = ?', $name_lookup[$setting_name][1], $name_lookup[$setting_name][0]));
			}
			$r .= Message::success(__('admin', 'settings-saved'));

			Database::endTransaction();
		}

		$settings = Database::select('settings', '*', array('package = ? OR package = ? OR package = ? OR package = ?', 'core','admin','site', 'gui'), array('package', 'ASC', 'name', 'ASC'));
		$settings = $settings->fetchAll(PDO::FETCH_ASSOC);

		$rows = array();
		foreach($settings as $row) {
			if(!array_key_exists($row['package'], $rows)) {
				$rows[$row['package']] = array();
			}
			$rows[$row['package']][] = $row;
		}
		ksort($rows);

		$form = new Form('self', 'POST', 'settings');
		foreach($rows as $cat => $catRows) {
			$form->startFieldset(__('settings', $cat));

			foreach($catRows as $row) {
				$data = unserialize($row['data']);

				$form->addHidden('cc_settings_'.UTF8::slugify($row['name']), $row['name'].'|'.$row['package']);
				if($row['name'] == 'clean urls') {
					$form->addSelectList(__('settings',$row['name']), UTF8::slugify($row['name']), array(
						1 => __('admin','yes'),
						0 => __('admin','no')
					), true, $data);
				}
				else if($row['name'] == 'theme') {
					$themes = Themes::getThemeList();

					$options = array();
					foreach($themes as $slug => $ini) {
						$options[$slug] = $ini['name'];
					}

					$form->addSelectList(__('settings',$row['name']), UTF8::slugify($row['name']), $options, true, $data);
				}
				else if($row['name'] == 'locale') {
					$locales = i18n::getLocales();

					$form->addSelectList(__('settings',$row['name']), UTF8::slugify($row['name']), $locales, false, $data);
				}
				else if($row['name'] == 'homepage id') {
					$form->addSelectList(__('settings',$row['name']), UTF8::slugify($row['name']), Content::optionListArrayFromArray(Content::parseNavigation()), true, $data);
				}
				else if($row['name'] == 'site name') {
					$form->addInput(__('settings', $row['name']), 'text', UTF8::slugify($row['name']), $data);
				}
				else if($row['name'] == 'editor') {
					$editors = Editors::getNamesOfRegistered();

					$form->addSelectList(__('settings',$row['name']), UTF8::slugify($row['name']), $editors, false, $data);
				}
				else if($row['name'] == 'homepage') {
					$form->addSelectList(__('settings',$row['name']), UTF8::slugify($row['name']), Admin::getAdminPageOptions(), true, $data);
				}
			}

			$form->endFieldset();
		}

		$form->startFieldset('');
			$form->addSubmit('', 'save-settings', __('admin', 'save'));
		$form->endFieldset();

		return array(__('admin', 'settings'), $r.$form->endAndGetHTML());
	}
}