<?php
/**
 * @package    mod_osm
 *
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

require_once dirname(__FILE__) . '/Helper/OSMHelper.php';

// Variables defined in the configuration of the module
$clientId = $params->get('clientId');
$clientSecret = $params->get('clientSecret');
$section = $params->get('sectionName');

// Prep some variables
$error = "";
$sectionId = NULL;
$termId = NULL;
$sectionIndex = NULL;
$termIndex = NULL;
$nextMeeting = NULL;
$resource = NULL;

// Default styling
$defaultStyle = '.mod_osm_header {
	font-weight: 500;
    font-size: 1.50rem;
}
.mod_osm_datetime {
    margin-bottom: .5rem;
    font-style: italic;
}
.mod_osm_notes pre {
    margin: 0;
    white-space: pre-wrap;
}';

if (empty($clientId) || empty($clientSecret) || empty($section)) {
    $error .= "<p><b>Error: please ensure all the required information is configured.</b></p><p>You must specify the client ID, client secret and section name/ID in the module configuration." ;
}

// add CSS to the page
$css = $params->get('css');
if (empty($css)) {
    $css = $defaultStyle;
}
$document = JFactory::getDocument();
$document->addStyleDeclaration($css);

// Get an instance of the OSM Helper
$OSMHelper = new OSMHelper($clientId, $clientSecret);

if (empty($error) && $OSMHelper->getToken() === FALSE) {
    $error .= "<p><b>Error: unable to get token.</b></p><p>There was a problem authenticating with OSM.</p>";
}

if (empty($error)) {
    // Get a list of all the sections and their terms
    $resource = $OSMHelper->getResource();
}

if (empty($resource) || !empty($resource->error->message)) {
    $error .= "<p><b>Error: unable to find the group resources.</b></p><p>Please report this error to the module author.</p>";
    if (!empty($resource->error->message)) {
        $error .= "<pre>'" . $resource->error->message . "'</pre>";
    }
} else {
    // Search through the sections looking for the one we want (either defined as a section name or section ID)
    foreach ($resource->data->sections as $key => $sectionObj) {
        if ((strcasecmp($sectionObj->section_name, $section) === 0) || ($sectionObj->section_id == $section)) {
            $sectionId = $sectionObj->section_id;
            $sectionIndex = $key;
            break;
        }
    }
}

if (empty($sectionId)) {
    // If we didn't find the section then the user probably mis-typed the configuration
    $error .= "<p><b>Error: unable to find the section: " . $section . ".</b></p><p>Please ensure you have correctly configured the section name or ID.</p>";
}

// Search through the list of Terms, looking for the current term
foreach ($resource->data->sections[$sectionIndex]->terms as $key => $termObj) {
    if (Utility::isNowBetweenDates($termObj->startdate, $termObj->enddate)) {
        $termId = $termObj->term_id;
        $termIndex = $key;
        break;
    }
}

// store some bits that are useful for debugging
$debug = array(
    "section" => $section,
    "clientId" => $clientId,
    "clientSecret" => $clientSecret,
    "token" => $OSMHelper->getToken(),
    "sectionId" => $sectionId,
    "sectionIndex" => $sectionIndex
);

if (empty($termId)) {
    $error .= "<p><b>Error: unable to find the current term.</b></p><p>Please report this error to the module author.</p>";
} else {
    $debug["termId"] = $termId;
}

if (empty($error)) {
    // call the OSM API to get the programme for the given section and current term
    $programme = $OSMHelper->getProgramme($sectionId, $termId);
    $meetings = $programme->items; // the programme returns a list of items (meetings)
}

// search through the meetings attempting to find the next one
foreach ($meetings as $key => $meetingObj) {
    if (Utility::isDateInTheFuture($meetingObj->meetingdate)) {
        $debug["nextMeeting date"] = $meetingObj->meetingdate;
        $nextMeeting = $meetingObj;
        break;
    }
}

// if we haven't found a meeting in the current term, we're probably "between terms" so try looking at the next term
if (empty($nextMeeting) && !empty($termIndex)) {
    $debug["Search next term"] = TRUE;
    $termIndex++;
    // check if the next term is available to be searched
    if (array_key_exists($termIndex, $resource->data->sections[$sectionIndex]->terms)) {
        $termId = $resource->data->sections[$sectionIndex]->terms[$termIndex]->term_id;
        $programme = $OSMHelper->getProgramme($sectionId, $termId);
        $meetings = $programme->items;
        foreach ($meetings as $key => $meetingObj) {
            if (Utility::isDateInTheFuture($meetingObj->meetingdate)) {
                $debug["nextMeeting date"] = $meetingObj->meetingdate;
                $nextMeeting = $meetingObj;
                break;
            }
        }
    }
}

// prepare for output
$output = $nextMeeting;

require ModuleHelper::getLayoutPath('mod_osm', $params->get('layout', 'default'));

