<html>
    <head>
<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('../../config.php');
require_once('lib.php');
global $CFG;


require_login();
require_capability('local/cleanupusers:view', context_system::instance());


$cleanupusersDir = $CFG->wwwroot . "/local/cleanupusers";

$html_includes = '
<script src="' . $cleanupusersDir . '/js/jquery-2.2.0.min.js"></script>
<link rel="stylesheet" href="' . $cleanupusersDir . '/css/bootstrap.min.css">
<link rel="stylesheet" href="' . $cleanupusersDir . '/css/bootstrap-theme.min.css">
      <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">

<script src="' . $cleanupusersDir . '/js/bootstrap.min.js"></script>
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <script>
  $(function() {
    $( "#tabs" ).tabs();
  });
  </script>
</head>';

$body = "<body>"
        . "<div id='tabs' class='container'>"
        . "<ul>"
        . "<li><a href='#tab-deletedUsers'>Gelöschte User</a></li>"
        . "<li><a href='#tab-inactiveUsers'>Nutzer die gelöscht werden</a></li>"
        . "</ul>";

$body .= tableOfDeletedUsers($cleanupusersDir);
$body .= tableOfUsersToBeDeleted()
        . "</div>";

$body .= "</body>";

echo $html_includes . $body;

function tableOfUsersToBeDeleted() {

    $c = new Cleaner();
    $count = count($c->usersToBeDeleted);
    $output = "<div class='container' id='tab-inactiveUsers'>"
        . "<h3>Zu löschende Nutzer: ".$count."</h3>"
        . "<p>Es gibt " . $count . " Nutzer, die "
            . "<ul>"
                . "<li>seit " . Cleaner::$DAYS . " Tagen nicht mehr aktiv waren</li>"
                . "<li>deren TU-ID nicht mehr gültig ist</li>"
            . "</ul>"
            . "und deshalb beim nächsten Durchlauf gelöscht werden</p>"
        . "<table class='table table-bordered table-condensed' id='usersToBeDeleted'>"
        . "<thead><th>ID</th><th>Username</th><th>Vorname</th><th>Nachname</th><th>Zuletzt aktiv</th></thead>"
        . "<tbody>";

    foreach ($c->usersToBeDeleted as $userid => $user) {
        $output .= "<tr>"
                . "<td>" . $user->id . "</td>"
                . "<td>" . $user->username . "</td>"
                . "<td>" . $user->firstname . "</td>"
                . "<td>" . $user->lastname . "</td>"
                . "<td>" . date(Cleaner::$DATE_FORMAT, $user->lastaccess) . "</td>"
                .  "</tr>";
    }

    $output .= "</tbody>"
        . "</table>";

    $output .= "</div>";

    return $output;
}

function tableOfDeletedUsers($cleanupusersDir) {
    $output =
          "<div class='container' id='tab-deletedUsers'>"
        . "<h3>Gelöschte Nutzer: <span id='numberOfDeletedUsers'></span></h3>"
        . "<table class='table table-bordered table-condensed' id='deletedUsers'>"
        . "<thead><th>ID</th><th>Username</th><th>Vorname</th><th>Nachname</th><th>Zuletzt aktiv</th><th>Gelöscht am</th></thead>"
        . "<tbody></tbody>"
        . "</table>"
        . "</div>"
        . '<script>$(document).ready(function() {
                var timestampToLocalTime = function (timestamp) {
                    var date = new Date(timestamp * 1000);
                    return date.toLocaleString();
                };

                $.getJSON( "' . $cleanupusersDir . '/deletedUsers.php", function( users ) {
                    $.each(users, function(userid, user) {
                        console.log(userid);
                        $("#deletedUsers > tbody:last-child").append("<tr><td>" + userid + "</td><td>" + user.username + "</td><td>" + user.firstname + "</td><td>" + user.lastname + "</td><td>" + timestampToLocalTime(user.lastaccess) + "</td><td>" + timestampToLocalTime(user.deletedOn) + "</td></tr>");
                      });
                    $("#numberOfDeletedUsers").text(Object.keys(users).length);
                });
              });
            </script>';

    return $output;

}


?>


</html>
