<?php

Admin::registerPage('content', 'Content Management', 'ContentPage::display', -10);
AdminSidebar::registerForPage('content', 'ContentPage::createContent');
AdminSidebar::registerForPage('content/edit-page', 'ContentPage::createContent',-9);
AdminSidebar::registerForPage('content/create-page', 'ContentPage::createContent',-9);
//AdminSidebar::registerForPage('content', 'EditPage::pageInfoBlock', -1);

class ContentPage {
	public static $navArray;
	public static $reorderingTemp;

	public static function updateFromPOST() {
			$conn = Database::getHandle();

			$statement = $conn->prepare("UPDATE `".CC_DB_PREFIX."content` SET weight = ? AND parent_id = ? WHERE id = ?");
			$conn->beginTransaction();

			$order = explode('|', trim($_POST['order'], '|'));
			foreach($order as $part) {
			    $parts = explode(',', $part);
			    $newOrder[$parts[0]] = array($parts[1], $parts[2]);
			}
			$order = $newOrder;
			$weight = 0;
			foreach($order as $id => $arr) {
				$parent_id = $arr[1];
				$weight = $arr[0];
			    Database::update('content', array('weight', 'parent_id'), array($weight, $parent_id), array('`id` = ?', $id));
			    if(!$statement->execute(array($weight, $parent_id, $id))) {
					print_r(self::getHandle()->errorInfo());
			    }
			    //$sql = "UPDATE `".CC_DB_PREFIX."content` SET weight = $weight, parent_id = $parent_id WHERE id = $id";
			    //$conn->exec($sql);
			    $weight++;
			}

			$conn->commit();
	    echo "ok";
	    exit();
	}

	public static function display () {
		if($_GET['do'] == 'reorder') {
		    self::updateFromPOST();
		}
		load_library(array('jquery', 'jstree'));

		$edit_link = '"<a href=\"'.Admin::link('content/edit-page').'&id=%3$s\" class=\"edit-page-link\">'.__('admin', 'edit-page').'</a>"';
		$edit_link2 = '"<a href=\"'.Admin::link('content/edit-page').'&id=%4$s\" class=\"edit-page-link\">'.__('admin', 'edit-page').'</a>"';

		$delete_link = '"<a href=\"'.Admin::link('content/delete-page').'&id=%3$s\" class=\"delete-page-link\">'.__('admin', 'delete-page').'</a>"';
		$delete_link2 = '"<a href=\"'.Admin::link('content/delete-page').'&id=%4$s\" class=\"delete-page-link\">'.__('admin', 'delete-page').'</a>"';

		$r .= "<h2>".__("admin", "content-management")."</h2>";
		$r .= "<p class='page-intro'>".__('admin', 'content-intro')."</p>\n<div id='outbox'></div><div id='tree'></div>";

		$count = Content::countNavItems();

		$json_nav = html_entity_decode(str_replace('},]', '}]', Content::generateNavHTML(array(
			'root' =>			'{"requestFirstIndex": 0,"firstIndex": 0,"count": '.$count.',"totalCount": '.$count.',
			    "columns":[	"'.__("admin", 'page-name').'",
					"'.__('admin', 'edit-page').'",
					"'.__('admin', 'delete-page').'",
					],"items":[%s]}',
			'child' =>			'[%s]',
			'item' =>			'{"id":%3$s,"info":["%1$s",'.$edit_link.', '.$delete_link.']},',
			'itemSelected' =>		'{"id":%3$s,"info":["%1$s",'.$edit_link.', '.$delete_link.']},',
			'itemHasChild' =>		'{"id":%4$s,"info":["%1$s",'.$edit_link2.', '.$delete_link2.'],"children":%3$s},',
			'itemHasChildSelected' =>	'{"id":%4$s,"info":["%1$s",'.$edit_link2.', '.$delete_link2.'],"children":%3$s},',
		))), ENT_QUOTES, "utf-8" );

		$save_url = Admin::link('content', array('do' => 'reorder'));

		$local_success = Message::success(__('admin', 'page-reorder-success'));
		$local_error = Message::error(__('admin', 'page-reorder-failure'));

		queue_js_string(<<<EOT
	$(function () {
		$('#tree').NestedSortableWidget({"jsonData":$json_nav,doSave: function (dom) {
			var jdom = $(dom);
			var string = "";
			jdom.find('.nsw-item').each(function (i) {
			    var grandparent = $(this).parent().parent();
			    var isChild = (grandparent[0].tagName == 'LI' ? grandparent.attr('id').match(/nsw\-item\-([0-9]+)/)[1] : false);

			    if(isChild !== false){
				    string += "|" + $(this).attr('id').match(/nsw\-item\-([0-9]+)/)[1] + "," + $(this).index() + "," + isChild;
			    }
			    else {
				    string += "|" +  $(this).attr('id').match(/nsw\-item\-([0-9]+)/)[1] + "," + $(this).index() + ",0" ;
			    }
			});
			$.ajax({
				url : "$save_url",
				type : "post",
				data : "order="+string,
				success: function(e, returnText) {
					var out = $('#outbox');
					out.html('');
					if(e == 'ok')
						$("$local_success").hide().appendTo(out).slideDown();
					else {
						$("$local_error").hide().appendTo(out).slideDown();
						if(typeof(console) == 'object') {
							console.log(e);
						}
						else {
							alert(e);
						}
					}
				}
			});
		}});
	});
EOT
);
	   	//print_r(json_decode($json_nav));
		//echo("<pre>".."</pre>");

		return $r;
   	}

	public static function createContent () {
		return sprintf("<a href='%s' class='action'>%s</a>", Admin::link('content/create-page'), __('admin', 'add-page'));
	}
}

?>
