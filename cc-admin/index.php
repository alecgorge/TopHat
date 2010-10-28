<?php
/*
Copyright 2010 Ramblingwood, LLC. All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are
permitted provided that the following conditions are met:

   1. Redistributions of source code must retain the above copyright notice, this list of
      conditions and the following disclaimer.

   2. Redistributions in binary form must reproduce the above copyright notice, this list
      of conditions and the following disclaimer in the documentation and/or other materials
      provided with the distribution.

THIS SOFTWARE IS PROVIDED BY Ramblingwood, LLC ``AS IS'' AND ANY EXPRESS OR IMPLIED
WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL Ramblingwood, LLC OR
CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

The views and conclusions contained in the software and documentation are those of the
authors and should not be interpreted as representing official policies, either expressed
or implied, of Ramblingwood, LLC.

    +-----------------------------------------+
	|  CanyonCMS v0.1.0a - 5/2/2010           |
	|  http://canyoncms.com                   |
	+-----------------------------------------+


*/
define('CC_IS_ADMIN', true);

require_once '../cc-config.php';

require_once 'cc-admin-include.php';

// include hooking capabilites
require_once CC_CORE.'cc-hooks.php';

// include include manager
require_once CC_CORE.'cc-includes.php';

// fix the $_GET variable
cc_core_include('cc-functions.php');

cc_core_include('cc-message.php');

cc_core_include('cc-timers.php');

// forms
cc_core_include('cc-forms.php');

cc_core_include('cc-table.php');

cc_core_include('cc-icons.php');

// utf-8 utils
cc_core_include('cc-utf8.php');

// loggin utils
cc_core_include('cc-log.php');

// editors
cc_core_include('cc-editors.php');

// some libraries
cc_core_include('cc-library.php');

// include redirection utils
cc_core_include('cc-redirect.php');

// have we installed yet? this checks if $database and $timezone are set in the CC_CONFIG file.
define('INSTALLED', (isset($database) && isset($timezone)));
if(!INSTALLED) {
	cc_redirect('cc-admin/install/', true);
}

// the all important db abstraction layer
cc_core_include('cc-database.php');

// get the validation methods
cc_core_include('cc-validate.php');

// setup settings manager
cc_core_include('cc-settings.php');

// themes
cc_core_include('cc-themes.php');

// i18n is important!
cc_core_include('cc-i18n.php');

// setup plugin architecture
cc_core_include('cc-plugins.php');

plugin('system_ready');

cc_core_include('cc-content.php');

plugin('system_after_content_load');

cc_include_admin('cc-users.php');

cc_include_admin('cc-admin-sidebar.php');

plugin('system_before_admin_loaded');

// this is where the awesome is
cc_include_admin('cc-admin.php');

// let some things run (pulling settings, etc) before we go on to pull the page info
plugin('system_complete');

?>
