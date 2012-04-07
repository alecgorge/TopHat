<?php

Admin::registerPage('content', __('admin', 'content-management'), 'ContentPage::display', -10);
AdminSidebar::registerForPage('content', 'ContentPage::createContent');
AdminSidebar::registerForPage('content/edit-page', 'ContentPage::createContent',-9);
AdminSidebar::registerForPage('content/create-page', 'ContentPage::createContent',-9);
//AdminSidebar::registerForPage('content', 'EditPage::pageInfoBlock', -1);

class ContentPage {
	public static $navArray;
	public static $reorderingTemp;

	public static function updateFromPOST() {
			$conn = Database::getHandle();

			$statement = $conn->prepare("UPDATE `".TH_DB_PREFIX."content` SET weight = ?, parent_id = ? WHERE content_id = ?");
			$conn->beginTransaction();

			$order = json_decode($_POST['order'], true);
			foreach($order as $id => $arr) {
				$parent_id = $arr['parent_id'];
				$weight = $arr['weight'];

			    if(!$statement->execute(array($weight, $parent_id, $id))) {
					print_r(Database::getHandle()->errorInfo());
			    }
			}

			$conn->commit();
	    echo "ok";
	    exit();
	}

	public static function display () {
		if($_GET['do'] == 'reorder') {
		    self::updateFromPOST();
		}
		load_library(array('nestedSortable', 'json'));

		$r = "<button id='save-btn' class='btn btn-primary'>" . __('admin', 'save-changes') . "</button>"
		. "<p class='page-intro'>".__('admin', 'content-intro')."</p>
		<div id='outbox' style='clear:both;'></div>";

		$editUrl = Admin::link('content/edit-page');
		$edit = __('admin', 'edit-page');

		$view = __('admin', 'view-page');

		$deleteUrl = Admin::link('content/delete-page');
		$delete = __('admin', 'delete-page');

		$r .= Content::generateNavHTML(array(
			'root' => "\n<ol class='sortable' id='sortable-nav'>\n%s\n</ol>\n",
			'child' => "\n<ol>\n%s\n</ol>\n",
			'item' => "\n\t<li data-node-id='%3\$s'>
	<div class='clearfix'>
		<span class='name'>%1\$s</span>
		<span class='delete-link link'><a href='$deleteUrl'>$delete</a></span>
		<span class='edit-link link'><a href='$editUrl&id=%3\$s'>$edit</a></span>
		<span class='pub-link link'><a href='%2\$s' target='_blank'>$view</a></span>
	</div>
</li>",
			'itemHasChild' => "\n\t<li data-node-id='%4\$s'>
	<div class='clearfix'>
		<span class='name'>%1\$s</span>
		<span class='delete-link link'><a href='$deleteUrl'>$delete</a></span>
		<span class='edit-link link'><a href='$editUrl&id=%4\$s'>$edit</a></span>
		<span class='pub-link link'><a href='%2\$s' target='_blank'>$view</a></span>
	</div>
	%3\$s
</li>"
		));

		$save_url = Admin::link('content', array('do' => 'reorder'));

		$local_success = Message::success(__('admin', 'page-reorder-success'), true);
		$local_error = Message::error(__('admin', 'page-reorder-failure'), true);

		$conf_text = __("admin", 'delete-confirm');

		queue_js_string(<<<EOT
	$(function () {
		var restripe = function () {
			var stripe = false;
			$("#sortable-nav li").each(function () {
				if(stripe = !stripe) $(this).addClass('stripe');
			});
		};

		$("#sortable-nav").nestedSortable({
			disableNesting: 'no-nest',
			forcePlaceholderSize: true,
			handle: 'div',
			helper:	'clone',
			items: 'li',
			maxLevels: 0,
			opacity: .6,
			placeholder: 'placeholder',
			revert: 250,
			tabSize: 25,
			tolerance: 'pointer',
			toleranceElement: '> div'
		}).bind('change', restripe);
		restripe();

		$('.delete-link a').click(function (e) {
			e.preventDefault();

			if(!confirm("$conf_text")) return;

			var g = $(this).parent().parent().parent();

			$.ajax({
				url : $(this).attr('href'),
				type : "post",
				data : {
					'id': g.attr('data-node-id')
				},
				success: function(e, returnText) {
					var out = $('#outbox');
					out.html(e);
				}
			});

			g.remove();

			return false;
		});

		$('#save-btn').click(function () {
			dis = $(this).addClass('disabled')
			, json = {};

			$("#sortable-nav li span.name").each(function() {
				var li = $(this).parent().parent()
				, grandparent = li.parent().parent();

				json[li.attr('data-node-id')] = {
					'parent_id': grandparent[0].tagName == 'LI' ? grandparent.attr('data-node-id') : '0',
					'weight': li.index()
				};
			});

			$.ajax({
				url : "$save_url",
				type : "post",
				data : {
					order: JSON.stringify(json)
				},
				success: function(e, returnText) {
					var out = $('#outbox');
					out.html('');
					if(e == 'ok') {
						$("$local_success").hide().appendTo(out).slideDown();
					}
					else {
						$("$local_error").hide().appendTo(out).slideDown();
						if(typeof console != 'undefined' && console.log) {
							console.log(e);
						}
						else {
							alert(e);
						}
					}
				}
			});
		});
		/*
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
		*/
	});
EOT
);
	   	//print_r(json_decode($json_nav));
		//echo("<pre>".."</pre>");

		return array(__("admin", "content-management"), $r);
   	}

	public static function createContent () {
		return sprintf("<a href='%s' class='action'>%s%s</a>", Admin::link('content/create-page'), icon('page_add'), __('admin', 'add-page'));
	}
}
