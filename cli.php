<?php
define('CLI_SCRIPT', 1);
require_once '../../config.php';
require_once $CFG->dirroot . '/local/cleanupusers/lib.php';

// Auswerten der CMD-Argumente

Cleaner::log("Cleaner CLI gestartet\n");

if(paramsContain("--pretend", $argv) || paramsContain("-p", $argv)) {
    $c = new Cleaner();
    Cleaner::log("Mode: Pretend\n");
    echo "ID\tUsername\tVorname\tNachname\tZuletzt aktiv\n";
    foreach ($c->usersToBeDeleted as $user) {
        echo $user->id; t();
        echo $user->username; t();
        echo $user->firstname; t();
        echo $user->lastname; t();
        echo date(Cleaner::$DATE_FORMAT, $user->lastaccess); 
        echo "\n";
    }
    echo count($c->usersToBeDeleted) . " Benutzer würden gelöscht\n";
} else if (paramsContain("--delete", $argv) || paramsContain("-d", $argv)) {
    Cleaner::log("Mode: Delete\n");
    $c = new Cleaner();
    $c->deleteUsers();
} else {
    Cleaner::log("Ungültige Parameter!\n");
    echo "Gültige Parameter:\n";
    echo " --pretend bzw. -p:\t Zeigt die Liste der zu löschenden User an\n";
    echo " --delete bzw. -d:\t Löscht abgelaufene User\n";
}
Cleaner::log("ENDE\n");
//$cleaner = new Cleaner();

//$cleaner->deleteUsers();

function paramsContain($p, $argv) {
    for($i = 1; $i < count($argv); $i++) {
        if(strcmp($argv[$i], $p) === 0) {
            return true;
        }
    }
    return false;
}                

function t() {
    echo "\t";
}
                