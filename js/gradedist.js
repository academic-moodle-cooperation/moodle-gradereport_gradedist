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
 * Javascript for the grade distribution chart
 *
 * @package       gradereport_gradedist
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Günther Bernsteiner
 * @author        Andreas Krieger
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.gradereport_gradedist = {
    /**
     * init function
     * gets all variables from dom
     * installs handlers
     */
    init: function (Y, data) {

        var mode = 0;
        var letters = [];

        var absolut = [];
        var percent = [];
        var absolutnew = [];
        var percentnew = [];

        var submit = Y.one('#id_submitbutton');
        if (submit) {
            submit.set('disabled', true);
        }

        $.map(data.actdist, function(grade, index) {
            letters.push(index)
            absolut.push(grade.count);
            percent.push(grade.percentage);
        });

        $.map(data.newdist, function(grade, index) {
            absolutnew.push(grade.count);
            percentnew.push(grade.percentage);
        });

		if(data.highcharts) {
			
			var chart = new Highcharts.Chart({
				chart: {
			renderTo: 'chart_container',
					type: 'column'
				},
				title: {
					text: data.title
				},
				xAxis: {
					title: {
						text: M.str.gradereport_gradedist.gradeletter
					},
					categories: letters
				},
				yAxis: {
					title: {
						text: M.str.gradereport_gradedist.absolut
					}
				},
				legend: {
					enabled: false
				},
				tooltip: {
					enabled: false
				},
				lang: {
					printChart: M.str.gradereport_gradedist.printchart,
					downloadPNG: M.str.gradereport_gradedist.downloadpng,
					downloadJPEG: M.str.gradereport_gradedist.downloadjpeg,
					downloadPDF: M.str.gradereport_gradedist.downloadpdf,
					downloadSVG: M.str.gradereport_gradedist.downloadsvg,
					contextButtonTitle: M.str.gradereport_gradedist.contextbuttontitle
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
					}, {
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
			})
		} else {
			Y.one('#chart_container').setHTML('<br><p><i><strong>[ !!! '+ M.str.gradereport_gradedist.highchartsmissing + ' !!! ]</strong></i></p><br>');	
		};

        var update = function(id, o, args) {

            data = Y.JSON.parse(o.responseText);

            if(data.updateall == 1) {
                absolut = [];
                percent = [];

                $.map(data.actdist, function(grade, index) {
                    absolut.push(grade.count);
                    percent.push(grade.percentage);
                });

                var values = (mode == 1) ? percent : absolut;
                chart.series[0].setData(values);

                chart.setTitle({ text: data.title });
            }
            absolutnew = [];
            percentnew = [];

            $.map(data.newdist, function(grade, index) {
                absolutnew.push(grade.count);
                percentnew.push(grade.percentage);
            });

            coverage(data);
            var newvalues = (mode == 1) ? percentnew : absolutnew;
            chart.series[1].setData(newvalues);
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
            },
            on: {
                complete: update
            }
        };

        var gradeitems = Y.one('#id_gradeitem');
        gradeitems.on('change', function (e) {
            var success = Y.all('.alert-success');
            if (success) {
                success.remove();
            }
            cfg.data = 'updateall=1';
            Y.io(uri, cfg);
        });

        var coursegroups = Y.one('#id_coursegroup');
        if (coursegroups) {
            coursegroups.on('change', function (e) {
                var success = Y.all('.alert-success');
                if (success) {
                    success.remove();
                }
                cfg.data = 'updateall=1';
                Y.one('#id_coursegrouping').set('value', '0');
                Y.io(uri, cfg);
            });
        }

        var coursegroupings = Y.one('#id_coursegrouping');
        if (coursegroupings) {
            coursegroupings.on('change', function (e) {
                var success = Y.all('.alert-success');
                if (success) {
                    success.remove();
                }
                cfg.data = 'updateall=1';
                Y.one('#id_coursegroup').set('value', '0');
                Y.io(uri, cfg);
            });
        }

        var boundaries = Y.all('.gradeboundaries_new input[type=text], #fgroup_id_grp_gradeboundaries_new input[type=text]');
        boundaries.on('change', function (e) {
            var notifications = Y.all('#page-grade-report-gradedist-index .notifyproblem, #page-grade-report-gradedist-index .notifysuccess');
            if (notifications) {
                notifications.remove();
            }
            var success = Y.all('.alert-success');
            if (success) {
                success.remove();
            }
            if (validate()) {
                Y.io(uri, cfg);
            }
        });

        var validate = function() {

            var error = false;

            var errdec = false;
            var errint = false;
            var errpre = false;
            var erremp = false;

            var errdecdiv = Y.one('#b_decimals');
            var errintdiv = Y.one('#b_interval');
            var errprediv = Y.one('#b_predecessor');

            var decimals = /^\d+([.]\d{1,2})?$/;
            var pre = 100.01;

            boundaries.each(function(boundary) {
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
                    Y.one('#boundary_error_container').append('<div class="b_error" id="b_decimals"><span>' + M.str.gradereport_gradedist.decimals + '</span></div>');
                }
                error = true;
            } else if (errdecdiv) {
                errdecdiv.remove();
            }
            if (errint) {
                if (!errintdiv) {
                    Y.one('#boundary_error_container').append('<div class="b_error" id="b_interval"><span>' + M.str.gradereport_gradedist.interval + '</span></div>');
                }
                error = true;
            } else if (errintdiv) {
                errintdiv.remove();
            }
            if (errpre) {
                if (!errprediv) {
                    Y.one('#boundary_error_container').append('<div class="b_error" id="b_predecessor"><span>' + M.str.gradereport_gradedist.predecessor + '</span></div>');
                }
                error = true;
            } else if (errprediv) {
                errprediv.remove();
            }

            if (submit !== null) {
                submit.set('disabled', error || erremp);
            }
            return !error;
        };

        var coverage = function(data) {

            var erremp = false;
            var errcov = Number(data.newcoverage[0]) != 0;
            var errcovdiv = Y.one('#b_coverage');

            boundaries.each(function(boundary) {
                if (boundary.get('value') == '') {
                    erremp = true;
                }
            });

            if (!erremp && errcov) {
                if (!errcovdiv) {
                    Y.one('#boundary_error_container').append('<div class="b_error" id="b_coverage"><span>' + M.str.gradereport_gradedist.coverage + '</span><span class="newcoverage">' + data.newcoverage[0] + '/' + data.newcoverage[1] + '</span></div>');
                }
            } else if (errcovdiv) {
                errcovdiv.remove();
            }

            Y.all('.actcoverage').setContent(data.actcoverage[0] + '/' + data.actcoverage[1] + ' (' + data.actcoverage[2] + '%)');
            Y.all('.newcoverage').setContent(data.newcoverage[0] + '/' + data.newcoverage[1] + ' (' + data.newcoverage[2] + '%)');
        }

        var desc = Y.all('input[name^="grp_description"]');
        desc.on('change', function (e) {
            mode = e.currentTarget.get('value');
            var values, values_new;

            if (mode == 1) {
                values = percent;
                values_new = percentnew;
                chart.yAxis[0].axisTitle.attr({
                    text: M.str.gradereport_gradedist.percent
                });
                chart.yAxis[0].setExtremes(0, 100);
            } else {
                values = absolut;
                values_new = absolutnew;
                chart.yAxis[0].axisTitle.attr({
                    text: M.str.gradereport_gradedist.absolut
                });
                chart.yAxis[0].setExtremes(0, null);
            }
            chart.series[0].setData(values);
            chart.series[1].setData(values_new);
        });

        var cols = Y.all('#id_grp_columns_actualcolumns, #id_grp_columns_newcolumns');
        cols.on('click', function (e) {
            var column = (e.currentTarget.get('id') == 'id_grp_columns_actualcolumns') ? 0 : 1;
            if(e.currentTarget.get('checked')) {
                chart.series[column].show();
            } else {
                chart.series[column].hide();
            }
        });

        validate();
    }
}