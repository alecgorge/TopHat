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

error_reporting(E_ALL - E_NOTICE);
setlocale(LC_ALL,'en_US.UTF8');
header('Content-Type: text/html; charset=utf-8');

define('CC_START', microtime(true));

/**
 * This is the location of the CanyonCMS configuration file. It can be relative or absolute. It can be above the word viewable directory. Default: './config.php'
 */
define('CC_CONFIG', 'cc-config.php');

/* The definitions for directories. All paths are relative to this (index.php) file and CONTAIN THE TRAILING FORWARD SLASH */

/**
 * This is the root directory of the CanyonCMS installation. Contains trailing slash.
 */
define('CC_ROOT', dirname(__FILE__).'/');

/**
 * This is the root PUBLIC directory of the CanyonCMS installation. Contains trailing slash.
 */
define('CC_PUB_ROOT', dirname($_SERVER['PHP_SELF']));

/**
 * The location of required CanyonCMS files like the bootstrapper. Should contain trailing slash. Default: CC_ROOT.'core/'
 */
define('CC_CORE', CC_ROOT.'cc-core/'); 

/**
 * The location of the admin panel. If this is changed then the url needed to access the admin panel changes. Should contain trailing slash. Default: CC_ROOT.'admin/'
 */
define('CC_ADMIN', CC_ROOT.'cc-admin/');

/**
 * The location of the folder that contains the uploads and themes directory. Should contain trailing slash. Default: CC_ROOT.'content/'
 */
define('CC_CONTENT', 'content/');

/**
 * The location of the folder that contains the site's themes. Needs to be a subfolder of CC_CONTENT. Should contain trailing slash. <code>Default: CC_CONTENT.'themes/'</code>
 */
define('CC_THEMES', CC_CONTENT.'themes/');

/**
 * The location of the folder that contains the site's uploads. Needs to be a subfolder of CC_CONTENT. Should contain trailing slash. Default: <code>CC_CONTENT.'uploads/'</code>
 */
define('CC_UPLOADS', CC_CONTENT.'uploads/');

/**
 * The location of the folder that contains the site's plugins. Should contain trailing slash. Default: <code>CC_ROOT.'plugins/'</code>
 */
define('CC_PLUGINS', CC_ROOT.'plugins/');

/* load config */
require CC_CONFIG;

/* Off we go! */
require CC_CORE.'cc-bootstrap.php';
