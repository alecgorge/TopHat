<?php
function array_remove_empty($arr) {
	foreach($arr as $k => $v) {
		if(!empty($v)) {
			$r[] = $v;
		}
	}
	return $r;
}