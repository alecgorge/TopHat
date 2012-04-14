<?php

class elRTEeditor implements NewEditor {
	public static $handles = array();
	public static $instance;
	public static $plugin;

	public static function bootstrap () {
		$editor = new Editor("elRTE", 3.2, "xya");
		$editor->bind_create("elRTEeditor::create");
		Editors::register($editor);
   	}

	public static function create ($name, $initContents) {
		$p = self::$plugin;
	
		queue_css($p->pluginPublicDir().'elrte/css/smoothness/jquery-ui-1.8.13.custom.css');
		queue_css($p->pluginPublicDir().'elrte/css/elrte.min.css');
		queue_css($p->pluginPublicDir().'elfinder/css/elfinder.min.css');
		queue_css($p->pluginPublicDir().'elfinder/css/theme.css');
		queue_js($p->pluginPublicDir().'elrte/js/jquery-ui-1.8.13.custom.min.js');
		queue_js($p->pluginPublicDir().'elrte/js/elrte.full.js');
		queue_js($p->pluginPublicDir().'elfinder/js/elfinder.full.js');
		queue_js($p->pluginPublicDir().'elrte/js/i18n/elrte.en.js');

		return sprintf(<<<EOT
<script type="text/javascript" charset="utf-8">
	$(function() {
		var opts = {
			lang         : 'en',   // set your language
			styleWithCSS : false,
			height       : 400,
			toolbar      : 'maxi',
			cssfiles     : ['%s'],
			fmOpen : function(callback) {
				$('<div />').dialogelfinder({
					url : '%selfinder/php/connector.php',
					lang : 'en',
					commandsOptions : {
						getfile : {
							onlyURL  : true, // disable to return detail info
							multiple : false, // disable to return multiple files info
							folders  : false, // disable to return folders info
							oncomplete : 'destroy' // action after callback (""/"close"/"destroy")
						}
					},
					getFileCallback : function (obj) {
						callback(obj.baseUrl + obj.path.substr(obj.path.replace('\\\', '/').indexOf('/')+1));
					}
				})
			}
		};
		// create editor
		$('#%s').elrte(opts);

		// or this way
		// var editor = new elRTE(document.getElementById('our-element'), opts);
	});
</script>
<textarea id="%s">%s</textarea>
EOT
, Themes::getCurrentTheme()->getPublicPath() . "editor.css", $p->pluginPublicDir(), $name, $name, $initContents);
   	}
}
elRTEeditor::$plugin = new Plugin('elRTE Enabler', 'author' , 'desc', '3.3');
elRTEeditor::$plugin->bootstrap('elRTEeditor::bootstrap');


