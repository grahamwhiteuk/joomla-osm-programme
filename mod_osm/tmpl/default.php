<?php
/**
 * @package    mod_osm
 *
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * $nextMeeting keys
 *   num eveningid
 *   num sectionid
 *   str title
 *   str notesforparents
 *   str notesforhelpingparents
 *   num parentsrequired
 *   str games
 *   str prenotes
 *   str postnotes
 *   str leaders
 *   date meetingdate (YYYY-MM-DD)
 *   time starttime (HH:MM:SS)
 *   time endtime (HH:MM:SS)
 *   str config
 *   str googlecalendar
 *   num soft_deleted
 *   num parentsattendingcount
 */

// output either the correct module output or an error message
if (empty($error)) {
    if (empty($nextMeeting)) {
        if ($params->get('showNotFound', '1') === '1') {
            // container div
            echo "<div class='mod_osm'>";

            // section name
            echo "<div class='mod_osm_header'>";
            echo "No Meeting Found";
            echo "</div>";

            // message about unknown meeting
            echo "<div class='mod_osm_notes'><p>";
            echo "Unable to find details for the next meeting.";
            echo "</p></div>";

            // end container div
            echo "</div>";
        }

    } else {
        $showtime = TRUE;
        if (empty($nextMeeting->starttime) || ($nextMeeting->starttime == '00:00:00')) {
            $showtime = FALSE;
        } else {
            $starttime = new DateTime($nextMeeting->starttime);
            $endtime = new DateTime($nextMeeting->endtime);
        }
        $date = new DateTime($nextMeeting->meetingdate);

        // container div
        echo "<div class='mod_osm'>";

        // meeting title
        echo "<div class='mod_osm_header'>";
        echo $nextMeeting->title;
        echo "</div>";

        // meeting date and time
        echo "<div class='mod_osm_datetime'>";
        echo "<span class='mod_osm_date'>";
        echo $date->format('l jS F');
        echo "</span>";
        if ($showtime) {
            echo "<span class='mod_osm_time'>";
            echo $starttime->format(', G:i-');
            echo $endtime->format('G:i');
            echo "</span>";
        }
        echo "</div>";

        // meeting notes
        if (!empty($nextMeeting->notesforparents) && $params->get('showNotes', '1') === '1') {
            echo "<div class='mod_osm_notes'><pre>";
            echo $nextMeeting->notesforparents;
            echo "</pre></div>";
        }

        // end container div
        echo "</div>";
    }
} else {
    echo $error;
}
