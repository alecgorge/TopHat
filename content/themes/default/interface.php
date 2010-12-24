<?php
// the interface has arrived.

AdminSidebar::registerForPage('theme-settings', 'DefaultTheme::sidebar');

class DefaultTheme {
	public static function sidebar () {
		return "<h3>Sidebar Item</h3><p>Text</p>";
	}
}

echo "interface";