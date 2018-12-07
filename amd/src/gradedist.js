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
 * gradedist.js
 *
 * @package   gradereport_gradedist
 * @author    Andreas Krieger
 * @copyright 2014-2018 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * @module gradereport_gradedist/gradedist
  */

define(['jquery', 'core/log', 'core/str'],
function($, log, str) {
    /**
     * @constructor
     * @alias module:gradereport_gradedist/gradedist
     */
    var Gradedist = function() {
    };

    /*
     * update() updates the chart according to the values currently set in the form
     *
     * @return true if everything's allright (no error handling by now)
     */
    Gradedist.prototype.update = function(o) {

        var data = JSON.parse(o.responseText);

        if (data.updateall == 1) {
            window.absolut = [];
            window.percent = [];

            $.map(data.actdist, function(grade) {
                window.absolut.push(grade.count);
                window.percent.push(grade.percentage);
            });

            var values = (window.mode == 1) ? window.percent : window.absolut;

            window.chart.series[0].setData(values);

            window.chart.setTitle({text: data.title});
        }

        window.absolutnew = [];
        window.percentnew = [];

        $.map(data.newdist, function(grade) {
            window.absolutnew.push(grade.count);
            window.percentnew.push(grade.percentage);
        });

        instance.coverage(data);
        var newvalues = (window.mode == 1) ? window.percentnew : window.absolutnew;
        window.chart.series[1].setData(newvalues);

        return true;
    };

    /*
     * validate() checks if the current boundaries given in the form data are
     * syntactically and semantically correct and displays warnings based on this
     *
     * @return true if everything's allright, false on detected error
     */
    Gradedist.prototype.validate = function() {

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

        $.each(window.boundaries, function(id, boundary) {
            var value = boundary.value.replace(/,/g, '.');
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
            if (!errdecdiv.length) {
                str.get_string('decimals', 'gradereport_gradedist').done(function(s) {
                    $('#boundary_error_container').first().append('<div class="b_error" id="b_decimals"><span>'
                        + s + '</span></div>');
                });
            }
            error = true;
        } else if (errdecdiv.length) {
            errdecdiv.remove();
        }
        if (errint) {
            if (!errintdiv.length) {
                str.get_string('interval', 'gradereport_gradedist').done(function(s) {
                    $('#boundary_error_container').first().append('<div class="b_error" id="b_interval"><span>'
                        + s + '</span></div>');
                });
            }
            error = true;
        } else if (errintdiv.length) {
            errintdiv.remove();
        }
        if (errpre) {
            if (!errprediv.length) {
                str.get_string('predecessor', 'gradereport_gradedist').done(function(s) {
                    $('#boundary_error_container').first().append('<div class="b_error" id="b_predecessor"><span>'
                        + s + '</span></div>');
                });
            }
            error = true;
        } else if (errprediv.length) {
            errprediv.remove();
        }

        if (window.submit.length) {
            window.submit.prop('disabled', error || erremp);
        }

        return !error;
    };

    /*
     * coverage() determines how many grades are covered/not covered by
     * the current forms grade distribution and displays this information
     *
     * @return true if everything's allright, false on detected error
     */
    Gradedist.prototype.coverage = function(data) {

        var error = false;
        var erremp = false;
        var errcov = Number(data.newcoverage[0]) != 0;
        var errcovdiv = $('#b_coverage').first();

        $.each(window.boundaries, function(id, boundary) {
            if (boundary.value == '') {
                erremp = true;
            }
        });

        if (!erremp && errcov) {
            if (!errcovdiv.length) {
                str.get_string('coverage', 'gradereport_gradedist').done(function(s) {
                    $('#boundary_error_container').first().append('<div class="b_error" id="b_coverage"><span>'
                        + s + '</span><span class="newcoverage">'
                        + data.newcoverage[0] + '/' + data.newcoverage[1] + '</span></div>');
                });
            }
            error = true;
        } else if (errcovdiv.length) {
            errcovdiv.remove();
        }

        $('.actcoverage').html(data.actcoverage[0] + '/' + data.actcoverage[1] + ' (' + data.actcoverage[2] + '%)');
        $('.newcoverage').html(data.newcoverage[0] + '/' + data.newcoverage[1] + ' (' + data.newcoverage[2] + '%)');

        return !error;
    };

    Gradedist.prototype.initChart = function(initdata, letters, HC) {

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
            window.chart = new HC.Chart({
                chart: {
                    renderTo: 'chart_container',
                    type: 'column'
                },
                title: {
                    text: initdata.title
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
                    data: window.absolut,
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
                    data: window.absolutnew,
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

    var instance = new Gradedist();

    /*
     * initializer(config) prepares settings form for JS-functionality
     */
    instance.initializer = function(config) {

        log.info('Initialize settings JS', 'gradedist');

        var initdata = config.data;

        window.mode = 0;
        var letters = [];

        window.absolut = [];
        window.percent = [];
        window.absolutnew = [];
        window.percentnew = [];

        var submitSelector = "#id_submitbutton";
        window.submit = $(submitSelector);
        if (window.submit.length) {
            window.submit.prop('disabled', true);
        }

        $.map(initdata.actdist, function(grade, index) {
            letters.push(index);
            window.absolut.push(grade.count);
            window.percent.push(grade.percentage);
        });

        $.map(initdata.newdist, function(grade) {
            window.absolutnew.push(grade.count);
            window.percentnew.push(grade.percentage);
        });

        window.chart = [];
        if (initdata.highcharts_src) {
            require(['gradereport_gradedist/highcharts_src'], function(highcharts_src) {
                instance.initChart(initdata, letters, highcharts_src);
            });
        } else if (initdata.highcharts_min) {
            require(['gradereport_gradedist/highcharts_min'], function(highcharts_min) {
                instance.initChart(initdata, letters, highcharts_min);
            });
        } else {
            var chartContainerSelector = "#chart_container";
            str.get_string('highchartsmissing', 'gradereport_gradedist').done(function(s) {
                $(chartContainerSelector).first().html(
                    '<br><p><i><strong>[ !!! '
                    + s + ' !!! ]</strong></i></p><br>');
            });
        }

        var uri = M.cfg.wwwroot + '/grade/report/gradedist/ajax_handler.php?id=' + initdata.courseid;

        var cfg = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            complete: function(arg1) { instance.update(arg1); },
            url: uri
        };

        var gradeitemsSelector = "#id_gradeitem";
        var gradeitems = $(gradeitemsSelector).first();
        gradeitems.change(instance, function() {
            var success = $('.alert-success');
            if (success) {
                success.remove();
            }
            cfg.data = $('#letterform').serialize() + "&updateall=1";
            $.ajax(cfg);
        });

        var coursegroupsSelector = "#id_coursegroup";
        var coursegroups = $(coursegroupsSelector).first();
        if (coursegroups) {
            coursegroups.change(instance, function() {
                var success = $('.alert-success');
                if (success) {
                    success.remove();
                }
                $('#id_coursegrouping').first().prop('value', '0');
                cfg.data = $('#letterform').serialize() + "&updateall=1";
                $.ajax(cfg);
            });
        }

        var coursegroupingsSelector = "#id_coursegrouping";
        var coursegroupings = $(coursegroupingsSelector).first();
        if (coursegroupings) {
            coursegroupings.change(instance, function() {
                var success = $('.alert-success');
                if (success) {
                    success.remove();
                }
                $('#id_coursegroup').first().prop('value', '0');
                cfg.data = $('#letterform').serialize() + "&updateall=1";
                $.ajax(cfg);
            });
        }

        window.boundaries = $('.gradeboundaries_new input[type=text], #fgroup_id_grp_gradeboundaries_new input[type=text]');
        window.boundaries.change(instance, function() {
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
                cfg.data = $('#letterform').serialize() + "&updateall=1";
                $.ajax(cfg);
            }
        });

        var desc = $('input[name^="grp_description"]');
        desc.change(instance, function() {
            window.mode = this.value;
            var values, values_new;

            var s_y;
            var ext_y;
            if (window.mode == 1) {
                values = window.percent;
                values_new = window.percentnew;
                s_y = 'percent';
                ext_y = 100;
            } else {
                values = window.absolut;
                values_new = window.absolutnew;
                s_y = 'absolut';
                ext_y = null;
            }

            str.get_string(s_y, 'gradereport_gradedist').done(function(s) {
                window.chart.yAxis[0].axisTitle.attr({
                    text: s
                });
                window.chart.yAxis[0].setExtremes(0, ext_y);
                window.chart.series[0].setData(values);
                window.chart.series[1].setData(values_new);
            });
        });

        var cols = $('#id_grp_columns_actualcolumns, #id_grp_columns_newcolumns');
        cols.click(instance, function() {
            var column = (this.id === 'id_grp_columns_actualcolumns') ? 0 : 1;
            if (this.checked) {
                window.chart.series[column].show();
            } else {
                window.chart.series[column].hide();
            }
        });

        instance.validate();
    };

    return instance;
});