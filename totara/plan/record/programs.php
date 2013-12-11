<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010-2013 Totara Learning Solutions LTD
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
 * @author     Aaron Wells <aaronw@catalyst.net.nz>
 * @package totara
 * @subpackage totara_plan
 *
 */

/**
 * Displays collaborative features for the current user
 *
 */

    require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
    require_once($CFG->dirroot.'/totara/reportbuilder/lib.php');
    require_once($CFG->dirroot.'/totara/plan/lib.php');

    require_login();

    global $SESSION,$USER;

    $programid = optional_param('programid', null, PARAM_INT);
    $history = optional_param('history', false, PARAM_BOOL);
    $userid     = optional_param('userid', $USER->id, PARAM_INT);                       // which user to show
    $sid = optional_param('sid', '0', PARAM_INT);
    $format = optional_param('format','', PARAM_TEXT); // export format
    $rolstatus = optional_param('status', 'all', PARAM_ALPHANUM);
    if (!in_array($rolstatus, array('active','completed','all'))) {
        $rolstatus = 'all';
    }

    $pageparams = array(
        'programid' => $programid,
        'history' => $history,
        'userid' => $userid,
        'format' => $format,
        'status' => $rolstatus
    );

    if (!$user = $DB->get_record('user', array('id' => $userid))) {
        print_error('error:usernotfound', 'totara_plan');
    }

    if (!empty($programid) && (!$program = $DB->get_record('prog', array('id' => $programid), 'fullname'))) {
        print_error(get_string('programnotfound', 'totara_plan'));
    }

    $context = context_system::instance();
    // users can only view their own and their staff's pages
    // or if they are an admin
    if ($USER->id != $userid && !totara_is_manager($userid) && !has_capability('totara/plan:accessanyplan',$context)) {
        print_error('error:cannotviewpage', 'totara_plan');
    }

    $PAGE->set_context($context);
    $PAGE->set_url(new moodle_url('/totara/plan/record/programs.php', $pageparams));
    $PAGE->set_pagelayout('noblocks');

    $renderer = $PAGE->get_renderer('totara_reportbuilder');

    if ($USER->id != $userid) {
        $strheading = get_string('recordoflearningfor', 'totara_core').fullname($user, true);
    } else {
        $strheading = get_string('recordoflearning', 'totara_core');
    }
    // get subheading name for display
    $strsubheading = get_string($rolstatus.'programssubhead', 'totara_plan');

    $shortname = 'plan_programs';
    $data = array(
        'userid' => $userid,
        'exceptionstatus' => 0,
    );
    if ($rolstatus !== 'all') {
        $data['rolstatus'] = $rolstatus;
    }

    if ($history) {
        $shortname = 'plan_programs_completion_history';
        $data['programid'] = $programid;
        if (!empty($programid)) {
            $strsubheading = get_string('programscompletionhistoryforsubhead', 'totara_plan', $program->fullname);
        } else {
            $strsubheading = get_string('programscompletionhistorysubhead', 'totara_plan');
        }
    }
    $report = reportbuilder_get_embedded_report($shortname, $data, false, $sid);

    $logurl = $PAGE->url->out_as_local_url();
    if ($format != '') {
        add_to_log(SITEID, 'rbembedded', 'record export', $logurl, $report->fullname);
        $report->export_data($format);
        die;
    }

    add_to_log(SITEID, 'rbembedded', 'record view', $logurl, $report->fullname);

    $report->include_js();

    ///
    /// Display the page
    ///
    $PAGE->navbar->add(get_string('mylearning', 'totara_core'), new moodle_url('/my/'));
    $PAGE->navbar->add($strheading, new moodle_url('/totara/plan/record/index.php'));
    $PAGE->navbar->add($strsubheading);

    $PAGE->set_title($strheading);
    $PAGE->set_button($report->edit_button());
    $PAGE->set_heading($strheading);

    $ownplan = $USER->id == $userid;

    $usertype = ($ownplan) ? 'learner' : 'manager';
    $menuitem = ($ownplan) ? 'recordoflearning' : 'myteam';
    $PAGE->set_totara_menu_selected($menuitem);

    echo $OUTPUT->header();

    echo dp_display_plans_menu($userid, 0, $usertype, 'courses', $rolstatus);

    echo $OUTPUT->container_start('', 'dp-plan-content');

    echo $OUTPUT->heading($strheading.' : '.$strsubheading, 1);

    $currenttab = 'programs';
    dp_print_rol_tabs($rolstatus, $currenttab, $userid);

    // display table here
    $fullname = $report->fullname;
    $countfiltered = $report->get_filtered_count();
    $countall = $report->get_full_count();

    $heading = $renderer->print_result_count_string($countfiltered, $countall);
    echo $OUTPUT->heading($heading);

    echo $renderer->print_description($report->description, $report->_id);

    $report->display_search();

    // Print saved search buttons if appropriate.
    echo $report->display_saved_search_options();

    if ($countfiltered > 0) {
        echo $renderer->showhide_button($report->_id, $report->shortname);
        $report->display_table();
        // export button
        $renderer->export_select($report->_id, $sid);
    }

    echo $OUTPUT->container_end();
    echo $OUTPUT->footer();
