<?php

function icon ($slug, $link = false, $showText = false) {
	$icon_dir = CC_PUB_ADMIN.'design/icons/';
	$icon = $icon_dir.$slug.'.png';
	$icon_file = CC_ADMIN.'design/icons/'.$slug.'.png';

	if(file_exists($icon_file) && ($showText === false || i18n::translationExists('icons', $slug))) {
		$trans = __('icons', $slug);
		return sprintf('<span class="cc-icon cc-icon-%s">%s<img src="%s" title="%s" />%s%s</span>',
						$slug,
						($link ? '<a href="'.$link.'">' : ''),
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
