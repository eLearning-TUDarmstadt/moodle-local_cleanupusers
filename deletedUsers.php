<?php
use Cleaner;

require_once('../../config.php');
require_once('lib.php');

require_login();
require_capability('local/cleanupusers:view', context_system::instance());
$c = new Cleaner();
$c->printDeletedUsersJSON();
