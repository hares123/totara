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
 * @author Ciaran Irvine <ciaran.irvine@totaralms.com>
 * @package totara
 * @subpackage core/assign
 */

/**
 * This file defines the Totara dialog for adding groups of users
 */

M.totara_assigngroupdialog = M.totara_assigngroupdialog || {

    Y: null,

    // optional php params and defaults defined here, args passed to init method
    // below will override these values
    config: {},

    /**
     * module initialisation method called by php js_init_call()
     *
     * @param object    YUI instance
     * @param string    args supplied in JSON format
     */
    init: function(Y, args) {
        // save a reference to the Y instance (all of its dependencies included)
        this.Y = Y;

        // if defined, parse args into this module's config object
        if (args) {
            var jargs = Y.JSON.parse(args);
            for (var a in jargs) {
                if (Y.Object.owns(jargs, a)) {
                    this.config[a] = jargs[a];
                }
            }
        }

        // check jQuery dependency is available
        if (typeof $ === 'undefined') {
            throw new Error('M.totara_assigngroupdialog.init()-> jQuery dependency required for this module.');
        }

        this.totara_assigngroupdialog_init_dialogs();
    },

    totara_assigngroupdialog_init_dialogs: function() {
        var url = M.cfg.wwwroot + '/totara/' + M.totara_assigngroupdialog.config.module + '/lib/assign/assigngroup.php';
        var saveurl = url;
        // Dialog & handler for hierarchy picker

        var thandler = new totaraDialog_handler_assigngrouptreeview();
        var tbuttons = {};
        tbuttons[M.util.get_string('cancel','moodle')] = function() { thandler._cancel(); }
        tbuttons[M.util.get_string('save','totara_core')] = function() { thandler._save(); }
        var tdialog = new totaraDialog(
            'assigngrouptreeviewdialog',
            'nobutton',
            {
                buttons: tbuttons,
                title: '<h2>' + M.util.get_string('assigngroup', 'totara_' + M.totara_assigngroupdialog.config.module) + '</h2>'
            },
            url,
            thandler
        );
        tdialog.assigngroup_base_url = url;
        totaraDialogs['assigngrouptreeview'] = tdialog;

        // Bind open event to group_selector menu(s)
        // Also set their default value
        $(document).on('change', 'select.group_selector', function(event) {

            // Stop any default event occuring
            event.preventDefault();

            // Open default url
            var select = $(this);
            var grouptype = select.val();

            // Prevent selecting the instruction item when page loading was slow.
            if (grouptype == '') {
                return;
            }

            var id = select.attr('itemid');
            var module = M.totara_assigngroupdialog.config.module;
            var sesskey = M.totara_assigngroupdialog.config.sesskey;

            var dialog = totaraDialogs['assigngrouptreeview'];
            var url = dialog.assigngroup_base_url;
            var handler = dialog.handler;

            handler.responsetype = 'newgroup';
            handler.responsegoeshere = $('#assignedgroups');

            dialog.default_url = url + '?module=' + module + '&grouptype=' + grouptype + '&itemid=' + id + '&sesskey=' + sesskey;
            dialog.saveurl = dialog.default_url + '&add=1';
            dialog.open();

            // Set the value of the menu back if they cancel
            select.val('');
        });

    }
}


// A function to handle the responses generated by handlers
var assigngroup_handler_responsefunc = function(response) {

    if (response.substr(0,4) == 'DONE') {
        // Get all root elements in response
        var els = $(response.substr(4));

        // If we're adding a new group, insert it
        if (this.responsetype == 'newgroup') {
            this.responsegoeshere.replaceWith(els);
            els.effect('pulsate', { times: 3 }, 2000);
            // Trigger update of the user paginator
            var oTable = $('#datatable').dataTable();
            if (oTable) {
                oTable.fnClearTable();
            }
        }

        // Close dialog
        this._dialog.hide();
    } else {
        this._dialog.render(response);
    }
}

totaraDialog_handler_assigngrouptreeview = function() {};
totaraDialog_handler_assigngrouptreeview.prototype = new totaraDialog_handler_treeview_multiselect();

/**
 * Serialize dropped items and send to url,
 * update table with result
 *
 * @param string URL to send dropped items to
 * @return void
 */
totaraDialog_handler_assigngrouptreeview.prototype._save = function() {
    // Serialize data
    var elements = $('.selected > div > span', this._container);
    var selected = this._get_ids(elements);
    var extrafields = $('.assigngrouptreeviewsubmitfield');

    // If they're trying to create a new rule but haven't selected anything, just exit.
    // (If they are updating an existing rule, we'll want to delete the selected ones.)
    if (!selected.length) {
        if (this.responsetype == 'new') {
            this._cancel();
            return;
        } else if (this.responsetype == 'update') {
            // Trigger the "delete" link, closing this dialog if it's successful
            $('a.group-delete', this.responsegoeshere).trigger('click', {object: this, method: '_cancel'});
            return;
        }
    }

    // Check for any validation functions
    var success = true;
    extrafields.each(
        function(intIndex) {
            if (typeof(this.assigngroup_validation_func) == 'function') {
                success = success && this.assigngroup_validation_func(this);
            }
        }
    );
    if (!success) {
        return;
    }
    $('#assigngroup_action_box').show();

    var selected_str = selected.join(',');

    // Add to url
    var url = this._dialog.saveurl + '&selected=' + selected_str;

    extrafields.each(
        function(intIndex) {
            if ($(this).val() != null) {
                url = url + '&' + $(this).attr('name') + '=' + $(this).val();
            }
        }
    );

    // Send to server
    this._dialog._request(url, {object: this, method: '_update'});
}

// TODO: T-11233 need to figure out a better way to share this common code between this and the formpicker.
totaraDialog_handler_assigngrouptreeview.prototype._update = assigngroup_handler_responsefunc;
