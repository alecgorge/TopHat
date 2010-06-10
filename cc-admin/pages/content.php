<?php

Admin::registerPage('content', 'Content Management', 'ContentPage::display', -10);
AdminSidebar::registerForPage('content', 'ContentPage::createContent');
//AdminSidebar::registerForPage('content', 'EditPage::pageInfoBlock', -1);

class ContentPage {
	public static $navArray;

	public static function display () {
		load_library(array('jquery', 'jstree'));

		$r .= "<h2>".__("admin", "content-management")."</h2>";
		$r .= "<div id='tree'></div>";
		$json_nav = html_entity_decode(str_replace('},]', '}]', Content::generateNavHTML(array(
			'root' =>					'{"data":[%s],state: "open"}',
			'child' =>					'[%s]',
			'item' =>					'{"data":{"title":"%1$s","attr":{"href":"%2$s"},icon:"file"},state: "open",icon:"file"},',
			'itemSelected' =>			'{"data":{"title":"%1$s","attr":{"href":"%2$s"}},state: "open"},',
			'itemHasChild' =>			'{"data":{"title":"%1$s","attr":{"href":"%2$s"}},state: "open","children":%3$s},',
			'itemHasChildSelected' =>	'{"data":{"title":"%1$s","attr":{"href":"%2$s"}},state: "open","children":%3$s},',
		))), ENT_QUOTES, "utf-8" );


		queue_js_string(<<<EOT
	$(function () {
		$('#tree').jstree({"json_data":$json_nav, "plugins" : [ "themes", "json_data", "dnd" ]});
	});
EOT
);
       	//print_r(json_decode($json_nav));
		//echo("<pre>".."</pre>");

		return $r;
   	}

	public static function createContent () {
		return sprintf("<a href='%s' class='action'>%s</a>", Admin::link('content/add-page'), __('admin', 'add-page'));
	}
}

?>
