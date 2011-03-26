<?php

require 'header.php';

function render_file_structure ($arr, $is_node, $style_node, $style_leaf, $parentKey = null) {
	$html = "";
	foreach($arr as $k => $v) {
		$this_is_node = call_user_func($is_node, $k, $v, $arr);
		if($this_is_node == 1) {
			if(is_callable($style_node)) {
				$html .= call_user_func($style_node, $k, $v, $arr);
			}
			else {
				$html .= sprintf($style_node, $k, $v);
			}
			$html .= render_file_structure($v, $is_node, $style_node, $style_leaf, $k);
		}
		elseif ($this_is_node == 0) {
			if(is_callable($style_node)) {
				$html .= call_user_func($style_leaf, $k, $v, is_null($parentKey) ? $arr : array($parentKey => $arr), $arr);
			}
			else {
				$html .= sprintf($style_leaf, $k, $v);
			}
		}
	}
	return $html;
}

function is_node ($k, $v, $tree) {
	return is_string($k);
}

function style_node ($k, $v, $children, $parent, $depth) {
	$indent = str_repeat("\t", $depth+1);
	return $indent.sprintf("<li class='folder'><span>%s</span><ul>\n%s$indent</ul></li>\n", $k, $children);
}

function style_leaf ($k, $v, $parent, $depth) {
	return str_repeat("\t", $depth+1).sprintf("<li class='file'>%s</li>\n", is_dir(realpath($v)) ? $v : basename($v));
}

function render_tree ($tree, $has_child, $draw_hasChild, $draw_noChild, $depth = 0, $parent = false) {
	$html_stack = "";

	if($depth === 0) {
		$parent = $tree;
	}

	foreach($tree as $key => $value) {
		$res = call_user_func_array($has_child, array(
			$key, $value,
			$parent, $depth, $tree
		));

		$html = "";

		// has children
		if($res === true) {
			$children_arr = render_tree($value, $has_child, $draw_hasChild, $draw_noChild, $depth+1, $tree);
			if(is_callable($draw_hasChild)) {
				$html  = call_user_func_array($draw_hasChild, array(
					$key, $value,
					$children_arr, $parent, $depth, $tree
				));
			}
			else {
				$html = sprintf($draw_hasChild, $key, $value, implode($children_arr));
			}
		}
		// doesn't have children
		elseif($res === false) {
			if(is_callable($draw_hasChild)) {
				$html  = call_user_func_array($draw_noChild, array(
					$key, $value,
					$parent, $depth, $tree
				));
			}
			else {
				$html = sprintf($draw_hasChild, $key, $value);
			}
		}
		$html_stack .= $html;
	}

	return $html_stack;
}

//foreach( as $folder => $file) {
echo "<ul>\n".render_tree(Uploads::getFilesArray(), 'is_node', 'style_node', 'style_leaf')."</ul>";
foreach(Uploads::getFilesArray() as $folder => $file) {
//	var_dump($file);
}
//}