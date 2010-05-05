<?php

function cc_include_admin ($file) {
	if(file_exists(CC_ADMIN.$file)) {
		require_once CC_ADMIN.$file;
	}
}


?>
