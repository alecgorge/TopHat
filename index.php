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
	|  CanyonCMS v0.1.0a - 5/24/2010          |
	|  http://canyoncms.com                   |
	+-----------------------------------------+


*/
define('CC_START', microtime(true));

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
//error_reporting(E_ALL + E_STRICT & ~E_NOTICE);
header('Content-Type: text/html; charset=utf-8');

if(!defined('CC_IS_ADMIN')) {
	define('CC_IS_ADMIN', false);
}

/**
 * This is the location of the CanyonCMS configuration file. It can be relative or absolute. It can be above the word viewable directory. Default: './config.php'
 */
define('CC_CONFIG', 'cc-config.php');

/* load config */
require CC_CONFIG;

/* Off we go! */
require CC_CORE.'cc-bootstrap.php';
