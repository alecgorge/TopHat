<?php

class ExternalLinkPlugin {
	public static $plugin;
	public static function bootstrap () {
		i18n::register('en_US', 'external-link-nodetype', array(
			'name' => 'External Link',
			'create-external-link' => 'Create External Link',
			'update-external-link' => 'Update External Link',
			'url' => 'URL',
			'parent' => 'Parent Item',
			'blank-error' => 'You can\'t leave the URL or the Text to display blank!',
			'weight' => 'Weight',
			'display-text' => 'Text to display',
			'link-creation-successful' => 'Link successfuly added!',
			'link-creation-failure' => 'There was an error please try again.',
			'link-update-successful' => 'Link successfuly updated!',
			'link-update-failed' => 'There was an error please try again.',
		));
		Node::register('external-link', 'ExternalLinkNodeType');
	}
}

/**
 * The a custom content type for external links
 */
class ExternalLinkNodeType extends NodeType implements NodeActions {
	public function __construct($row) {
		$this->checkRow($row);
    }

	public static function name () {
		return __('external-link-nodetype', 'name');
	}

  	public static function cc_setup ($row) {
		$class = new ExternalLinkNodeType($row);
		return $class;
   	}

	public static function create($args) {
		$smt = DB::insert('content', $args + array(
			'type' => 'external-link'
		));

		return $smt;
	}

	public static function edit($id, $args) {
		return DB::update('content', array('last_modified' => time()) + $args, null, array('content_id = ?', $id));
	}

	public static function delete($id) {
		return DB::delete('content', array('content_id = ?', $id));
	}

	public static $row = array();
	public static function get ($x) {
	    if(array_key_exists($x, $_POST)) return $_POST[$x];

		if($x == 'content_area') $x = 'content';
	    if(array_key_exists($x, self::$row)) return self::$row[$x];
	    return '';
	}

	public static function edit_display($row) {
		self::$row = $row;
	    i18n::set('external-link-nodetype');

	    if($_POST['cc_form'] == 'update_external_link') {
			$id				= $_GET['id'];
			$weight			= self::get('weight');
			$menutitle		= self::get('menutitle');
			$parent_id		= self::get('parent_id');
			$slug			= self::get('slug');

			if(empty($slug) || empty($menutitle)) {
				$message = Message::error(__('blank-error'));
			}
			else {
				$values = array(
					'title' => '',
					'content' => '',
					'settings' => 'a:0:{}',
					'weight' => $weight,
					'menutitle' => $menutitle,
					'parent_id' => $parent_id,
					'slug' => $slug

				);
				$res = Content::editNode($id, 'external-link', $values);

				if($res) {
					$message = Message::success(sprintf(__('link-update-successful').' (<a href="%s">%s</a>)', Admin::link('content'), __('admin','view-all-pages')));
				}
				else {
					$message = Message::error(__('link-update-failed'));
				}
			}
	    }

	    $r .= sprintf("<h2>%s</h2>%s", __('update-external-link'), $message);

		$form = new Form('self', 'post', 'update_external_link');

		$form->addInput(__('url'), 'text', 'slug', self::get('slug'));
		$form->addInput(__('display-text'), 'text', 'menutitle', self::get('menutitle'));
		$form->addSelectList(__('parent'), 'parent_id', PageNode::buildParentOptions(),true,  $_POST['parent_id'] ? $_POST['parent_id'] : '0');
		$form->addInput(__('weight'), 'text', 'weight', self::get('weight'));

		$form->addSubmit('', 'update', 'Update');

		i18n::restore();

		return $r.$form->endAndGetHTML();

	}

	public static function url ($id, $menutitle, $slug) {
		return $slug;
	}

	public static function create_display() {
	    i18n::set('external-link-nodetype');

        $message = "";
	    if($_POST['cc_form'] == 'create_external_link') {
			$id				= $_GET['id'];
			$weight			= self::get('weight');
			$menutitle		= self::get('menutitle');
			$parent_id		= self::get('parent_id');
			$slug			= self::get('slug');

			if(empty($url) || empty($text)) {
				$message = Message::error(__('blank-error'));
			}
			else {
				if(empty($weight)) {
					$weight = '0';
				}
				$values = array(
					'title' => '',
					'content' => '',
					'settings' => 'a:0:{}',
					'weight' => $weight,
					'menutitle' => $menutitle,
					'parent_id' => $parent_id,
					'slug' => $slug

				);
				$res = Content::createNode($_GET['type'], $values);

				if($res) {
					$message = Message::success(sprintf(__('link-creation-successful').' (<a href="%s">%s</a>)', Admin::link('content'), __('admin', 'view-all-pages')));
				}
				else {
					$message = Message::error(__('link-creation-failed'));
				}
			}
	    }

	    $r = $message;

		$form = new Form('self', 'post', 'create_external_link');

		$form->addInput(__('url'), 'text', 'slug', self::get('slug'));
		$form->addInput(__('display-text'), 'text', 'menutitle', self::get('menutitle'));
		$form->addSelectList(__('parent'), 'parent_id', PageNode::buildParentOptions(),true,  $_POST['parent_id'] ? $_POST['parent_id'] : '0');
		$form->addInput(__('weight'), 'text', 'weight', self::get('weight'));

		$form->addSubmit('', 'create', 'Create');

		i18n::restore();

		return array(__('external-link-nodetype', 'create-external-link'), $form->endAndGetHTML());

	}
}

i18n::register('en_US', 'external-link-plugin', array(
	'title' => 'Add External Link',
	'desc' => 'Allows you to add links to specific urls in the menu.',
	'form-label' => 'Hide page from menu'
));

ExternalLinkPlugin::$plugin = new Plugin(__('external-link-plugin', 'title'), 'TopHat Team' , __('external-link-plugin', 'desc'), '1.0');
ExternalLinkPlugin::$plugin->bootstrap('ExternalLinkPlugin::bootstrap');
