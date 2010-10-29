<?php

function icon ($slug, $link = false, $showText = false, $attr = array()) {
	$icon_dir = CC_PUB_ADMIN.'design/icons/';
	$icon = $icon_dir.$slug.'.png';
	$icon_file = CC_ADMIN.'design/icons/'.$slug.'.png';

	if(is_array($attr) && !empty($attr)) {
		$x = '';
		foreach($attr as $key => $value) {
			$x .= " $key='$value'";
		}
		$attr = $x;
	}

	if(file_exists($icon_file) && ($showText === false || i18n::translationExists('icons', $slug))) {
		$trans = __('icons', $slug);
		return sprintf('<span class="cc-icon cc-icon-%s">%s<img src="%s" title="%s" />%s%s</span>',
						$slug,
						($link ? '<a href="'.$link.'"'.(is_string($attr) ? $attr : '') .' title="'.$trans.'">' : ''),
						$icon,
						($showText ? $trans : ""),
						($showText ? "<em>".$trans."</em>" : ''),
						($link ? '</a>' : ''));
	}

}

function icon_url ($slug) {
	$icon_dir = CC_PUB_ADMIN.'design/icons/';
	$icon = $icon_dir.$slug.'.png';

	$icon_file = CC_ADMIN.'design/icons/'.$slug.'.png';

	if(file_exists($icon_file)) {
		return $icon;
	}
	return false;
}
