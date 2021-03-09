<?php
/**
 *    Verify Users
 *
 *    Verified users automatically in interval
 *
 * @author  itsmeJAY
 * @version 1.4.1
 */
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}
function task_verifyusers($task)
{
    global $db, $mybb, $lang;

    $query = $db->query("SELECT * FROM mybb_users");

    $usersQ = [];
    while ($row = $db->fetch_array($query)) {
        $usersQ[] = $row;
    }

    foreach ($usersQ AS $userQ) {
        global $mybb, $db;

        // Wenn Benutzer in einer Benutzergruppe ist, die zum verifizieren ausgewählt wurde.
        if (in_array($userQ['usergroup'], explode(',', $mybb->settings['vf_group']))) {
            // Wenn beim Benutzer groupverified auf 0 steht und Zeit der Verifizierung auch auf 0 steht
            // Dann wird der Benutzer Gruppenverifiziert und die Zeit der Verifizierung in die Datenbank geschrieben.
            if ($userQ['groupverified'] == 0 && $userQ['verificationdate'] == 0) {
                $uid = (int)$userQ['uid'];
                $update_array = array(
                    "groupverified" => 1,
                    "verificationdate" => time(),
                );

                $db->update_query("users", $update_array, "uid='{$uid}'");

                // Wenn beim Benutzer groupverified auf 0 steht und Zeit der Verifizierung > 0 ist
                // weil der Benutzer z. B. bereits vorher manuell verifiziert wurde, dann behalten wir die alte Zeit (der manuellen Verifizierung)
                // und ändern lediglich, dass der Benutzer nicht mehr Gruppenverifiziert ist.
            } else if ($userQ['groupverified'] == 0 && $userQ['verificationdate'] > 0) {
                $uid = (int)$userQ['uid'];
                $update_array = array(
                    "groupverified" => 1,
                );

                $db->update_query("users", $update_array, "uid='{$uid}'");

                // Wenn der Benutzer Gruppenverifiziert wurde + manuell verifiziert und ein Admin löscht die manuelle Verifizierung.
                // Dann muss die Zeit neu gesetzt werden.
            } else if ($userQ['groupverified'] == 1 && $userQ['verificationdate'] == 0) {
                $uid = (int)$userQ['uid'];
                $update_array = array(
                    "groupverified" => 1,
                    "verificationdate" => time(),
                );

                $db->update_query("users", $update_array, "uid='{$uid}'");
            }

            // Wenn Benutzer NICHT MEHR in einer Benutzergruppe ist, die zum verifizieren ausgewählt wurde.
        } else if (!in_array($userQ['usergroup'], explode(',', $mybb->settings['vf_group']))) {
            // Falls Benutzer Gruppenverifiziert war und ebenfalls manuell verifiziert ist, entfernen wir die Gruppenverifizierung - löschen aber nicht die Zeit.
            if ($userQ['groupverified'] == 1 && $userQ['verified'] == 1) {
                $uid = (int)$userQ['uid'];
                $update_array = array(
                    "groupverified" => 0,
                );

                $db->update_query("users", $update_array, "uid='{$uid}'");

                // Falls Benutzer Gruppenverifiziert war und nicht manuell verifiziert ist, entfernen wir die Gruppenverifizierung inkl. der Zeit.
            } else if ($userQ['groupverified'] == 1 && $userQ['verified'] == 0) {
                $uid = (int)$userQ['uid'];
                $update_array = array(
                    "groupverified" => 0,
                    "verificationdate" => 0,
                );

                $db->update_query("users", $update_array, "uid='{$uid}'");

            }
        }
    }
    add_task_log($task, "Users in the selected groups verified / unverified");
}