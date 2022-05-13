<?php

// try to set a time-limit to infinite
@\set_time_limit(0);

// define current unix timestamp
\define('TIME_NOW', \time());

// fix timezone warning issue
if (!@\ini_get('date.timezone')) {
	@\date_default_timezone_set('Europe/London');
}

require_once(WCF_DIR . 'lib/system/api/autoload.php');
require_once(WCF_DIR . 'lib/core.functions.php');


