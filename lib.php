<?php

class Cleaner {

    // Days since last user activity
    public static $DAYS = 0;
    public static $DELETION_LOG_FILE = "local_cleanupusers_deletedUsers.js";
    public static $LOG_FILE = "local_cleanupusers_log.txt";
    public static $DATE_FORMAT = "d.m.Y H:i:s";
    private $inactiveUsers = null;
    public $usersToBeDeleted = array();

    function __construct() {
        $this->getInactiveUsers();
        $this->getUsersToDelete();
        //$this->printArray($this->usersToDelete);
    }

    public function printDeletedUsersJSON() {
      global $CFG;
      $fullpath = $CFG->dataroot . '/' . Cleaner::$DELETION_LOG_FILE;
      try {
        $filecontent = file_get_contents($fullpath);
      } catch (Exception $e) {
        // Datei nicht vorhanden
        echo json_encode([]);
        return;
      }

      echo $filecontent;
      return;

    }

    private function addUserToLogFile($user) {
        global $CFG;
        $fullpath = $CFG->dataroot . '/' . Cleaner::$DELETION_LOG_FILE;

        try {
          $filecontent = file_get_contents($fullpath);
        } catch (Exception $e) {

        }
        // Datei nicht vorhanden => anlegen
        $json = null;
        if (!$filecontent) {
            echo "Lege Datei neu an...\n";
            $json = json_encode(array($user->id => $user));
        } else {
            $obj = json_decode($filecontent);
            $key = $user->id;
            $obj->$key = $user;
            $json = json_encode($obj);
        }
        file_put_contents($fullpath, $json);
    }

    public static function log($msg) {
        $msg = date(Cleaner::$DATE_FORMAT) . ": " . $msg;
        // Ausgabe
        echo $msg;
        global $CFG;
        $fullpath = $CFG->dataroot . '/' . Cleaner::$LOG_FILE;
        file_put_contents($fullpath, $msg, FILE_APPEND);
    }

    public static function userToString($user) {
      return $user->firstname . " " .
      $user->lastname . " (" .
      $user->username . ")";
    }

    public function deleteUsers() {
        global $CFG;
        require_once $CFG->libdir . '/moodlelib.php';

        $noUsers = count($this->usersToBeDeleted);
        Cleaner::log($noUsers . " Nutzer werden gelöscht:\n");
        $count = 1;

        $undeleteableUsers = [];
        foreach ($this->usersToBeDeleted as $userid => $user) {
            try {
              delete_user($user);
              $user->deletedOn = time();
              $this->addUserToLogFile($user);
              $log = "[" . $count . "/" . $noUsers . "] Lösche User:\t" .
                Cleaner::userToString($user) . "\n";
              Cleaner::log($log);

              $count++;
            } catch (Exception $e) {
              $undeleteableUsers[$userid] = $user;

              $log = "[!] User " . Cleaner::userToString($user) . " konnte NICHT geloescht werden!\n";
              Cleaner::log($log);
            }
        }

        if(count($undeleteableUsers) > 0) {
          $log = "[!] " . count($undeleteableUsers) . " konnten NICHT geloescht werden!";
        }
    }

    private function checkUsersTUIDs($users) {
        $ldap = ldap_connect("ldaps://ldap.hrz.tu-darmstadt.de:636") or die("Error connecting LDAP Server: " . ldap_error($ldap));
        $bind = ldap_bind($ldap);
        $basedn = array('OU=USER,O=TU', 'OU=stud,O=tu', 'OU=GUP,O=TU');
        $attributes = array('cn', 'userId');
        $filter = '';

        if ($ldap) {
            foreach ($users as $userid => $user) {
                $filter = '(cn=' . $user->username . ')';
                $search = ldap_search($ldap, $basedn, $filter, $attributes) or die("Error in search Query: " . ldap_error($ldap));
                $result = ldap_get_entries($ldap, $search);
                if (!isset($result) OR $result['count'] < 1) {
                    $users[$userid]->valid = 0;
                } else {
                    $users[$userid]->valid = 1;
                }
            }
            ldap_close($ldap);
        } else {
            $this->error("Can't connect with LDAP-Server");
        }
        return $users;
    }

    private function getInactiveUsers() {
        $seconds = Cleaner::$DAYS * 24 * 60 * 60;
        $time = time() - $seconds;

        global $CFG, $DB;
        $sql = "SELECT "
                . "id,username,firstname,lastname,lastaccess "
                . "FROM {user} "
                . "WHERE auth = 'cas' AND deleted=0 AND lastaccess > 0 AND lastaccess < " . $time;
        $this->inactiveUsers = $this->checkUsersTUIDs($DB->get_records_sql($sql));
    }

    private function getUsersToDelete() {
        foreach ($this->inactiveUsers as $userid => $user) {
            if ($user->valid === 0) {
                $this->usersToBeDeleted[$user->id] = $user;
            }
        }
    }

    public function printArray($array) {
        echo print_r($array, true);
    }

    public function error($msg) {
        echo "[!!] " . $msg . "\n";
    }

    public function info($msg) {
        echo "[*] " . $msg . "\n";
    }

}
