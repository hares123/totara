<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings.
 *
 * @package tool_generator
 * @copyright 2013 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['bigfile'] = 'Big file {$a}';
$string['coursesize_0'] = 'XS (~10KB; create in ~1 second)';
$string['coursesize_1'] = 'S (~10MB; create in ~30 seconds)';
$string['coursesize_2'] = 'M (~100MB; create in ~5 minutes)';
$string['coursesize_3'] = 'L (~1GB; create in ~1 hour)';
$string['coursesize_4'] = 'XL (~10GB; create in ~4 hours)';
$string['coursesize_5'] = 'XXL (~20GB; create in ~8 hours)';
$string['createcourse'] = 'Create course';
$string['creating'] = 'Creating course';
$string['done'] = 'done ({$a}s)';
$string['explanation'] = 'This tool creates standard test courses that include many
sections, activities, and files.

This is intended to provide a standardised measure for checking the reliability
and performance of various system components (such as backup and restore).

This test is important because there have been many cases previously where,
faced with real-life use cases (e.g. a course with 1,000 activities), the system
does not work.

Courses created using this feature can occupy a large amount of database and
filesystem space (tens of gigabytes). You will need to delete the courses
(and wait for various cleanup runs) to release this space again.

**Do not use this feature on a live system**. Use only on a developer server.
(To avoid accidental use, this feature is disabled unless you have also selected
DEVELOPER debugging level.)';

$string['error_notdebugging'] = 'Not available on this server because debugging is not set to DEVELOPER';
$string['firstname'] = 'Test course user';
$string['fullname'] = 'Test course: {$a->size}';
$string['maketestcourse'] = 'Make test course';
$string['pluginname'] = 'Development data generator';
$string['progress_createcourse'] = 'Creating course {$a}';
$string['progress_checkaccounts'] = 'Checking user accounts ({$a})';
$string['progress_coursecompleted'] = 'Course completed ({$a}s)';
$string['progress_createaccounts'] = 'Creating user accounts ({$a->from} - {$a->to})';
$string['progress_createbigfiles'] = 'Creating big files ({$a})';
$string['progress_createforum'] = 'Creating forum ({$a} posts)';
$string['progress_createpages'] = 'Creating pages ({$a})';
$string['progress_createsmallfiles'] = 'Creating small files ({$a})';
$string['progress_enrol'] = 'Enrolling users into course ({$a})';
$string['progress_sitecompleted'] = 'Site completed ({$a}s)';
$string['shortsize_0'] = 'XS';
$string['shortsize_1'] = 'S';
$string['shortsize_2'] = 'M';
$string['shortsize_3'] = 'L';
$string['shortsize_4'] = 'XL';
$string['shortsize_5'] = 'XXL';
$string['sitesize_0'] = 'XS (~10MB; 3 courses, created in ~30 seconds)';
$string['sitesize_1'] = 'S (~50MB; 8 courses, created in ~2 minutes)';
$string['sitesize_2'] = 'M (~200MB; 73 courses, created in ~10 minutes)';
$string['sitesize_3'] = 'L (~1\'5GB; 277 courses, created in ~1\'5 hours)';
$string['sitesize_4'] = 'XL (~10GB; 1065 courses, created in ~5 hours)';
$string['sitesize_5'] = 'XXL (~20GB; 4177 courses, created in ~10 hours)';
$string['size'] = 'Size of course';
$string['smallfiles'] = 'Small files';
