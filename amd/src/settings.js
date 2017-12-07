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
    Settings.prototype.update = function(id, o) {

        alert(JSON.stringify(id, null, 4));
        alert(JSON.stringify(o, null, 4));
        
        this.data = o;

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

        alert(JSON.stringify("in validate", null, 4));
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

        $.each($.boundaries, function(id, boundary) {
        //$.boundaries.each(function(boundary) {
            var value = boundary.get('value').replace(/,/g,'.');
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

        if (errdec) {
            if (!errdecdiv) {
                $('#boundary_error_container').first().append('<div class="b_error" id="b_decimals"><span>'
                        + str.get_string('decimals', 'gradereport_gradedist') + '</span></div>');
            }
            error = true;
        } else if (errdecdiv) {
            errdecdiv.remove();
        }
        if (errint) {
            if (!errintdiv) {
                $('#boundary_error_container').first().append('<div class="b_error" id="b_interval"><span>'
                        + str.get_string('interval', 'gradereport_gradedist') + '</span></div>');
            }
            error = true;
        } else if (errintdiv) {
            errintdiv.remove();
        }
        if (errpre) {
            if (!errprediv) {
                $('#boundary_error_container').first().append('<div class="b_error" id="b_predecessor"><span>'
                        + str.get_string('predecessor', 'gradereport_gradedist') + '</span></div>');
            }
            error = true;
        } else if (errprediv) {
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

        $.each($.boundaries, function(id, boundary) {
            if (boundary.get('value') == '') {
                alert(JSON.stringify(boundary, null, 4));
                erremp = true;
            }
        });

        if (!erremp && errcov) {
            if (!errcovdiv) {
                $('#boundary_error_container').first().append('<div class="b_error" id="b_coverage"><span>'
                        + str.get_string('coverage', 'gradereport_gradedist') + '</span><span class="newcoverage">'
                        + data.newcoverage[0] + '/' + data.newcoverage[1] + '</span></div>');
            }
        } else if (errcovdiv) {
            errcovdiv.remove();
        }

        $('.actcoverage').html(data.actcoverage[0] + '/' + data.actcoverage[1] + ' (' + data.actcoverage[2] + '%)');
        $('.newcoverage').html(data.newcoverage[0] + '/' + data.newcoverage[1] + ' (' + data.newcoverage[2] + '%)');
    };


    var instance = new Settings();

    /*
     * initializer(config) prepares settings form for JS-functionality
     */
    instance.initializer = function(config) {
        
        log.info('Initialize settings JS', 'gradedist');

        data = config.data;

        mode = 0;
        var letters = [];

        var absolut = [];
        var percent = [];
        var absolutnew = [];
        var percentnew = [];

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
             //var chart = new Highcharts.Chart({
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
                        text: str.get_string('gradeletter', 'gradereport_gradedist')
                    },
                    categories: letters
                },
                yAxis: {
                    title: {
                        text: str.get_string('absolut', 'gradereport_gradedist')
                    }
                },
                legend: {
                    enabled: false
                },
                tooltip: {
                    enabled: false
                },
                lang: {
                    printChart: str.get_string('printchart', 'gradereport_gradedist'),
                    downloadPNG: str.get_string('downloadpng', 'gradereport_gradedist'),
                    downloadJPEG: str.get_string('downloadjpeg', 'gradereport_gradedist'),
                    downloadPDF: str.get_string('downloadpdf', 'gradereport_gradedist'),
                    downloadSVG: str.get_string('downloadsvg', 'gradereport_gradedist'),
                    contextButtonTitle: str.get_string('contextbuttontitle', 'gradereport_gradedist')
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
        } else {
            var chartContainerSelector = "#chart_container";
            $(chartContainerSelector).first().html(
                    '<br><p><i><strong>[ !!! '
                    + str.get_string('highchartsmissing', 'gradereport_gradedist') + ' !!! ]</strong></i></p><br>');
        }

     
        var uri = M.cfg.wwwroot + '/grade/report/gradedist/ajax_handler.php?id=' + data.courseid;

        var cfg = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            form: {
                id: 'letterform',
                useDisabled: true,
                upload: false
            }/*,
            on: {
                complete: instance.validate()
            }*/
        };


        var mycfg = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            //success: instance.validate(),
            data: 'updateall=1',
            url: uri
        };


        var gradeitemsSelector = "#id_gradeitem";
        var gradeitems = $(gradeitemsSelector).first();
        gradeitems.change(instance, function () {
            var success = $('.alert-success');
            if (success) {
                success.remove();
            }
            cfg.data = 'updateall=1';
            //$.ajax(mycfg);
        });

        var coursegroupsSelector = "#id_coursegroup";
        var coursegroups = $(coursegroupsSelector).first();
        if (coursegroups) {
            coursegroups.change(instance, function () {
                var success = $('.alert-success');
                if (success) {
                    success.remove();
                }
                cfg.data = 'updateall=1';
                $('#id_coursegrouping').first().prop('value', '0');
               // $.ajax({data: cfg.data, url: uri});
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
                cfg.data = 'updateall=1';
                $('#id_coursegroup').first().prop('value', '0');
                // $.ajax({data: cfg.data, url: uri});
            });
        }

        var boundaries = $('.gradeboundaries_new input[type=text], #fgroup_id_grp_gradeboundaries_new input[type=text]');
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
            //if (instance.validate()) {
               // $.ajax({data: cfg.data, url: uri});
            //}
        });


        var desc = $('input[name^="grp_description"]');
        desc.change(instance, function (e) {
            mode = e.currentTarget.valueOf();
            var values, values_new;

            if (mode == 1) {
                values = percent;
                values_new = percentnew;
                chart.yAxis[0].axisTitle.attr({
                    text: str.get_string('percent', 'gradereport_gradedist')
                });
                chart.yAxis[0].setExtremes(0, 100);
            } else {
                values = absolut;
                values_new = absolutnew;
                chart.yAxis[0].axisTitle.attr({
                    text: str.get_string('absolut', 'gradereport_gradedist')
                });
                chart.yAxis[0].setExtremes(0, null);
            }
            chart.series[0].setData(values);
            chart.series[1].setData(values_new);
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

        //instance.validate();
    };

    return instance;
});