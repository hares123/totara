<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 - 2013 Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Valerii Kuznetsov <valerii.kuznetsov@totaralms.com>
 * @package totara
 * @subpackage totara_appraisal
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/totara/appraisal/lib.php');
require_once($CFG->dirroot . '/totara/reportbuilder/lib.php');

$appraisalid = optional_param('appraisalid', 0, PARAM_INT);
$clearfilters = optional_param('clearfilters', null, PARAM_INT);
$sid = optional_param('sid', '0', PARAM_INT);
$format = optional_param('format', '', PARAM_TEXT);
$debug = optional_param('debug', 0, PARAM_INT);

$url = new moodle_url('/totara/appraisal/detailreport.php', array('appraisalid' => $appraisalid,
    'format' => $format, 'debug' => $debug));
admin_externalpage_setup('reportappraisals', '', null, $url);

$systemcontext = context_system::instance();
require_capability('totara/appraisal:manageappraisals', $systemcontext);

if (!$appraisalid) {
    echo $OUTPUT->header();
    $overviewurl = new moodle_url('/totara/appraisal/reports.php');
    echo $OUTPUT->container(get_string('selectanappraisal', 'rb_source_appraisal', $overviewurl->out()));
    echo $OUTPUT->footer();
    exit;
}

$renderer = $PAGE->get_renderer('totara_reportbuilder');
$appraisal = new appraisal($appraisalid);

$data = array(
    'appraisalid' => $appraisalid,
);

if (!$report = reportbuilder_get_embedded_report('appraisal_detail', $data, false, $sid)) {
    print_error('error:couldnotgenerateembeddedreport', 'totara_reportbuilder');
}

if ($format != '') {
    $report->export_data($format);
    die;
}

if (isset($clearfilters)) {
    $SESSION->reportbuilder[$report->_id] = array();
    $_POST = array();
}

$PAGE->set_button($report->edit_button());
echo $renderer->header();

if ($debug) {
    $report->debug($debug);
}

$countfiltered = $report->get_filtered_count();
$countall = $report->get_full_count();

$heading = get_string('detailreportforx', 'totara_appraisal', $appraisal->name);
$heading .= $renderer->print_result_count_string($countfiltered, $countall);
echo $renderer->heading($heading);

echo $renderer->print_description($report->description, $report->_id);

$report->include_js();

$report->display_search();

// Print saved search buttons if appropriate.
echo $report->display_saved_search_options();

if ($countfiltered > 0) {
    echo $renderer->showhide_button($report->_id, $report->shortname);
    $report->display_table();
    // Export button.
    $renderer->export_select($report->_id, $sid);
}

echo $renderer->footer();
