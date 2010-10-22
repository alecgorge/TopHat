<?php

Admin::registerPage('plugins', __('admin', 'plugin-management'), 'PluginAdminPage::display', 1);
AdminSidebar::registerForPage('plugins', 'PluginAdminPage::getMore');

class PluginAdminPage {
	public static function pluginSort ($a, $b) {
		$raw[] = $a[0][0];
		$raw[] = $b[0][0];
		$raw2 = $raw;
		natcasesort($raw);
		if($raw2 === $raw) {
			return -1;
		}
		else {
			return 1;
		}
	}

	public static function display () {
		if(array_key_exists('action', $_GET) && !empty($_GET['name'])) {
			if($_GET['action'] == 'disable') {
				DB::update('plugins', array('active' => '0'), null, array('name = ?', $_GET['name']));
			}
			if($_GET['action'] == 'enable') {
				$smt = DB::select('plugins', '*', array('name = ?', $_GET['name']));
				$row = $smt->fetch(PDO::FETCH_ASSOC);

				if($row === false) {
					DB::insert('plugins', array('name' => $_GET['name'], 'info' => serialize(array()), 'active' => '1'));
				}
				else {
					DB::update('plugins', array('active' => '1'), null, array('name = ?', $_GET['name']));
				}
			}
			cc_redirect(Admin::link('plugins'));
		}

		$arr = array();

		$r .= sprintf("<h2>%s</h2>
			<table id='plugins-table' cellspacing='0' cellpadding='0'>
				<thead>
					<th>%s</th><th>%s</th><th class='en-di-col'> </th>
				</thead>
				<tbody>", __('admin', 'plugins'), __('admin', 'plugin-name'), __('admin', 'plugin-desc'));

		foreach(Plugins::getBootedInfo() as $val) {
			$arr[] = array(array($val['name'], $val['dir'], $val['name'], $val['desc'], $val['author'], $val['version']), true);
			$arr2[] = $val['dir'];
		}
		foreach(Plugins::getActiveInfo() as $val) {
			if(array_search($val['dir'], $arr2) === false) {
				$arr[] = array(array($val['name'], $val['dir'], $val['name'], $val['desc'], $val['author'], $val['version']), false);
			}
		}
		usort($arr, 'PluginAdminPage::pluginSort');

		foreach($arr as $val) {
			if($val[1] == true) {
				$r .= sprintf("<tr class='enabled'><td>%s %s<span class='byline'>by %s</span></td><td>%s</td><td>%s (<a href='%s'>%s</a>)</td></tr>",
						$val[0][2], $val[0][5], $val[0][4], $val[0][3],
						__('admin', 'currently-enabled'),
						Admin::link('plugins', array('action' => 'disable', 'name' => $val[0][1])),
						__('admin', 'disable')
						);
			}
			else {
				$r .= sprintf("<tr class='disabled'><td>%s %s<span class='byline'>by %s</span></td><td>%s</td><td>%s (<a href='%s'>%s</a>)</td></tr>",
						$val[0][2], $val[0][5], $val[0][4], $val[0][3],
						__('admin', 'currently-disabled'),
						Admin::link('plugins', array('action' => 'enable', 'name' => $val[0][1])),
						__('admin', 'enable')
						);
			}
		}
		$r .= "</tbody></table>";

		return $r;
	}
	public static function getMore () {
		return sprintf("<a href='%s' class='action'>%s</a>", 'http://canyoncms.com/plugins/', __('admin', 'get-more-plugins'));
	}
}

