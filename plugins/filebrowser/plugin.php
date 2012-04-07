<?php

class FileBrowser {
	public static $plugin;
	public static function bootstrap () {
		URLMap::from("/filebrowser/view/:type/", 'FileBrowser::display');
	}

	public static function display ($args) {
		cc_admin_include('cc-users.php');
		Users::bootstrap();
		
		if(Users::isValid()) {
			//var_dump(self::$plugin->pluginPublicDir());
			cc_redirect(self::$plugin->pluginPublicDir()."browser/");
		}
		else {
			cc_redirect(TH_PUB_ROOT);
		}
	}
}

FileBrowser::$plugin = new Plugin('File Browser', 'TopHat Team', 'Allows you to manage the files on your server.', '1.0');
FileBrowser::$plugin->bootstrap('FileBrowser::bootstrap');