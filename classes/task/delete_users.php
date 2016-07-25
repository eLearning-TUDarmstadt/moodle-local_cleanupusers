<?php

namespace local_cleanupusers\task;

class delete_users extends \core\task\scheduled_task {

    public function get_name() {
        // Shown in admin screens
        return "Ungültige Nutzer löschen";
    }

    public function execute() {
        //require_once '../../../../config.php';
        global $CFG;
        require_once $CFG->dirroot . '/local/cleanupusers/lib.php';
        \Cleaner::log("Cron started at " . date(\Cleaner::$DATE_FORMAT) . "\n");
        $c = new \Cleaner();
        $c->deleteUsers();
    }

}
