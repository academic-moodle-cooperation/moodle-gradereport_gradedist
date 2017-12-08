// This file is part of mod_checkmark for Moodle - http://moodle.org/
//
// It is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * settings.js
 *
 * @package   gradereport_gradedist
 * @author    Andreas Krieger
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * @module gradereport_gradedist/settings
  */
define(['jquery', 'core/log', 'core/str'], function($, log, str) {

    /**
     * @constructor
     * @alias module:gradereport_gradedist/settings
     */
    var Settings = function() {
        //this.chart;
        //this.submit;
    };

    /*
     * updateSettings() updates the grade-selector appropriate to the given
     * individual grades (flexiblenaming = 1) or the given amount of examples (flexiblenaming = 0)
     *
     * @return true if everything's allright (no error handling by now)
     */
    Settings.prototype.update = function(o) {

        data = JSON.parse(o.responseText);
        
        if(data.updateall == 1) {
            absolut = [];
            percent = [];

            $.map(data.actdist, function(grade) {
                absolut.push(grade.count);
                percent.push(grade.percentage);
            });

            var values = (mode == 1) ? percent : absolut;

            chart.series[0].setData(values);

            chart.setTitle({ text: data.title });
        }
        
        absolutnew = [];
        percentnew = [];

        $.map(data.newdist, function(grade) {
            absolutnew.push(grade.count);
            percentnew.push(grade.percentage);
        });

        instance.coverage(data);
        var newvalues = (mode == 1) ? percentnew : absolutnew;
        chart.series[1].setData(newvalues);
    };


    /*
     * updateSettings() updates the grade-selector appropriate to the given
     * individual grades (flexiblenaming = 1) or the given amount of examples (flexiblenaming = 0)
     *
     * @return true if everything's allright (no error handling by now)
     */
    Settings.prototype.validate = function() {

        var error = false;

        var errdec = false;
        var errint = false;
        var errpre = false;
        var erremp = false;

        var errdecdiv = $('#b_decimals').first();
        var errintdiv = $('#b_interval').first();
        var errprediv = $('#b_predecessor').first();

        var decimals = /^\d+([.]\d{1,2})?$/;
        var pre = 100.01;

        $.each(boundaries, function(id, boundary) {
            var value = boundary.value.replace(/,/g,'.');
            if (value != '') {
                if (!decimals.test(value)) {
                    errdec = true;
                }
                if (Number(value) > 100) {
                    errint = true;
                }
                if (Number(value) >= Number(pre)) {
                    errpre = true;
                }

                pre = value;
            } else {
                erremp = true;
            }
        });

        // for debugging: alert(JSON.stringify(errpre, null, 4));

        if (errdec) {
            if (!errdecdiv.length) {
                $('#boundary_error_container').first().append('<div class="b_error" id="b_decimals"><span>'
                        + 'This is decimals a bit crossy'  + '</span></div>');
            }
            error = true;
        } else if (errdecdiv.length) {
            errdecdiv.remove();
        }
        if (errint) {
            if (!errintdiv.length) {
                $('#boundary_error_container').first().append('<div class="b_error" id="b_interval"><span>'
                        + 'This is interval a bit crossy' + '</span></div>');
            }
            error = true;
        } else if (errintdiv.length) {
            errintdiv.remove();
        }
        if (errpre) {
            if (!errprediv.length) {
                $('#boundary_error_container').first().append('<div class="b_error" id="b_predecessor"><span>'
                        + 'This is pre numbers a bit crossy' + '</span></div>');
            }
            error = true;
        } else if (errprediv.length) {
            errprediv.remove();
        }

        if (submit !== null) {
            submit.prop('disabled', error || erremp);
        }
        return !error;
    };


    /*
     * updateSettings() updates the grade-selector appropriate to the given
     * individual grades (flexiblenaming = 1) or the given amount of examples (flexiblenaming = 0)
     *
     * @return true if everything's allright (no error handling by now)
     */
    Settings.prototype.coverage = function(data) {

        var erremp = false;
        var errcov = Number(data.newcoverage[0]) != 0;
        var errcovdiv = $('#b_coverage').first();

        $.each(boundaries, function(id, boundary) {
            if (boundary.value == '') {
                erremp = true;
            }
        });

        if (!erremp && errcov) {
            if (!errcovdiv.length) {
                $('#boundary_error_container').first().append('<div class="b_error" id="b_coverage"><span>'
                        + 'The coverage is a big strange' + '</span><span class="newcoverage">'
                        + data.newcoverage[0] + '/' + data.newcoverage[1] + '</span></div>');
            }
        } else if (errcovdiv.length) {
            errcovdiv.remove();
        }

        $('.actcoverage').html(data.actcoverage[0] + '/' + data.actcoverage[1] + ' (' + data.actcoverage[2] + '%)');
        $('.newcoverage').html(data.newcoverage[0] + '/' + data.newcoverage[1] + ' (' + data.newcoverage[2] + '%)');
    };


    Settings.prototype.dohigh = function() {
 
        var tofetch = [
            {key: 'gradeletter', component: 'gradereport_gradedist'},
            {key: 'absolut', component: 'gradereport_gradedist'},
            {key: 'percent', component: 'gradereport_gradedist'},
            {key: 'printchart', component: 'gradereport_gradedist'},
            {key: 'downloadpng', component: 'gradereport_gradedist'},
            {key: 'downloadjpeg', component: 'gradereport_gradedist'},
            {key: 'downloadpdf', component: 'gradereport_gradedist'},
            {key: 'downloadsvg', component: 'gradereport_gradedist'},
            {key: 'downloadjpeg', component: 'gradereport_gradedist'},
            {key: 'contextbuttontitle', component: 'gradereport_gradedist'},
                    ];
        str.get_strings(tofetch).done(function(s) {

            chart = new Highcharts.Chart({
               chart: {
                   renderTo: 'chart_container',
                   type: 'column'
               },
               title: {
                   text: data.title
               },
               xAxis: {
                   title: {
                       text: s[0]
                   },
                   categories: letters
               },
               yAxis: {
                   title: {
                       text: s[1]
                   }
               },
               legend: {
                   enabled: false
               },
               tooltip: {
                   enabled: false
               },
               lang: {
                   printChart: s[3],
                   downloadPNG: s[4],
                   downloadJPEG: s[5],
                   downloadPDF: s[6],
                   downloadSVG: s[7],
                   contextButtonTitle: s[8]
               },
               series:
               [{
                   data: absolut,
                   color: '#990000',
                   dataLabels: {
                       enabled: true,
                       color: '#000000',
                       style: {
                           fontWeight: 'normal'
                       }
                   }
               },
               {
                   data: absolutnew,
                   color: '#33cc33',
                   dataLabels: {
                       enabled: true,
                       color: '#000000',
                       style: {
                           fontWeight: 'normal'
                       }
                   }
               }]
           });
    });
    };


    var instance = new Settings();

    /*
     * initializer(config) prepares settings form for JS-functionality
     */
    instance.initializer = function(config) {
        
        log.info('Initialize settings JS', 'gradedist');

        data = config.data;

        mode = 0;
        letters = [];

        absolut = [];
        percent = [];
        absolutnew = [];
        percentnew = [];

        var submitSelector = "#id_submitbutton";
        submit = $(submitSelector).first();
        if (submit) {
            submit.prop('disabled', true);
        }

        $.map(data.actdist, function(grade, index) {
            letters.push(index);
            absolut.push(grade.count);
            percent.push(grade.percentage);
        });

        $.map(data.newdist, function(grade) {
            absolutnew.push(grade.count);
            percentnew.push(grade.percentage);
        });


        chart = [];
        if(data.highcharts) {
            instance.dohigh();
        } else {
            var chartContainerSelector = "#chart_container";
            $(chartContainerSelector).first().html(
                    '<br><p><i><strong>[ !!! '
                    + str.get_string('highchartsmissing', 'gradereport_gradedist') + ' !!! ]</strong></i></p><br>');
        }

     
        var uri = M.cfg.wwwroot + '/grade/report/gradedist/ajax_handler.php?id=' + data.courseid;

        var mycfg = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            complete: function(arg1) { instance.update(arg1);},
            url: uri,
        };


        var gradeitemsSelector = "#id_gradeitem";
        var gradeitems = $(gradeitemsSelector).first();
        gradeitems.change(instance, function () {
            var success = $('.alert-success');
            if (success) {
                success.remove();
            }
            mycfg.data = $('#letterform').serialize() + "&updateall=1";
            $.ajax(mycfg);
        });

        var coursegroupsSelector = "#id_coursegroup";
        var coursegroups = $(coursegroupsSelector).first();
        if (coursegroups) {
            coursegroups.change(instance, function () {
                var success = $('.alert-success');
                if (success) {
                    success.remove();
                }
                $('#id_coursegrouping').first().prop('value', '0');
                mycfg.data = $('#letterform').serialize() + "&updateall=1";
                $.ajax(mycfg);
            });
        }

        var coursegroupingsSelector = "#id_coursegrouping";
        var coursegroupings = $(coursegroupingsSelector).first();
        if (coursegroupings) {
            coursegroupings.change(instance, function () {
                var success = $('.alert-success');
                if (success) {
                    success.remove();
                }
                $('#id_coursegroup').first().prop('value', '0');
                mycfg.data = $('#letterform').serialize() + "&updateall=1";
                $.ajax(mycfg);
            });
        }

        boundaries = $('.gradeboundaries_new input[type=text], #fgroup_id_grp_gradeboundaries_new input[type=text]');
        boundaries.change(instance, function () {
            var notifications =
                    $('#page-grade-report-gradedist-index .notifyproblem, #page-grade-report-gradedist-index .notifysuccess');
            if (notifications) {
                notifications.remove();
            }
            var success = $('.alert-success');
            if (success) {
                success.remove();
            }
            if (instance.validate()) {
                mycfg.data = $('#letterform').serialize() + "&updateall=1";
                $.ajax(mycfg);
            }
        });


        var desc = $('input[name^="grp_description"]');
        desc.change(instance, function (e) {
            mode = this.value;
            var values, values_new;

            var s_y;
            var ext_y;
            if (mode == 1) {
                values = percent;
                values_new = percentnew;
                s_y = 'percent';
                ext_y = 100;
            } else {
                values = absolut;
                values_new = absolutnew;
                s_y = 'absolut'
                ext_y = null;
            }

            str.get_string(s_y, 'gradereport_gradedist').done(function(s) {
                chart.yAxis[0].axisTitle.attr({
                    text: s
                });
                chart.yAxis[0].setExtremes(0, ext_y);
                chart.series[0].setData(values);
                chart.series[1].setData(values_new);
            })
        });

        var cols = $('#id_grp_columns_actualcolumns, #id_grp_columns_newcolumns');
        cols.click(instance, function (e) {
            var column = (this.id === 'id_grp_columns_actualcolumns') ? 0 : 1;
            if(this.checked) {
                chart.series[column].show();
            } else {
                chart.series[column].hide();
            }
        });

        instance.validate();
    };

    return instance;
});