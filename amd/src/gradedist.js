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
 * @package
 * @author    Andreas Krieger
 * @copyright 2014-2018 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * @module gradereport_gradedist/gradedist
  */

define(['jquery', 'core/log', 'core/str', 'gradereport_gradedist/config'],
function($, log, str) {
    /**
     * @constructor
     * @alias module:gradereport_gradedist/gradedist
     */
    var Gradedist = function() {
        // Constructor intentionally left empty as it is initialized without properties.
    };

    /*
     * Function update() updates the chart according to the values currently set in the form
     *
     * @return true if everything's allright (no error handling by now)
     */
    Gradedist.prototype.update = function(o) {

        var data = JSON.parse(o.responseText);

        if (data.updateall == 1) {
            window.absolut = [];
            window.percent = [];

            $.map(data.letters, function(letter) {
                window.absolut.push(data.actdist[letter].count);
                window.percent.push(data.actdist[letter].percentage);
            });

            var values = (window.mode == 1) ? window.percent : window.absolut;

            window.chart.data.datasets[0].data = values;
            window.chart.options.title.text = [data.title, ""];
        }

        window.absolutnew = [];
        window.percentnew = [];

        $.map(data.letters, function(letter) {
            window.absolutnew.push(data.newdist[letter].count);
            window.percentnew.push(data.newdist[letter].percentage);
        });

        instance.coverage(data);
        var newvalues = (window.mode == 1) ? window.percentnew : window.absolutnew;
        window.chart.data.datasets[1].data = newvalues;
        window.chart.update();

        return true;
    };

    /*
     * Function validate() checks if the current boundaries given in the form data are
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
     * Function coverage() determines how many grades are covered/not covered by
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

    Gradedist.prototype.initChart = function(initdata, letters) {

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
            window.chart = new window.Chart($("#chart_container"), {
                type: 'bar',
                data: {
                    labels: letters,
                    datasets: [{
                        data: window.absolut,
                        backgroundColor: '#990000',
                        borderWidth: 1,
                    },
                    {
                        data: window.absolutnew,
                        backgroundColor: '#33cc33',
                        borderWidth: 1
                    }]
                },
                options: {
                    title: {
                        display: true,
                        text: [initdata.title, ""],
                        fontSize: 18,
                        fontStyle: 'normal'
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                padding: 10
                            },
                            scaleLabel: {
                                display: true,
                                labelString: s[1],
                                fontColor: "#4d759e",
                                fontSize: 15
                            },
                            gridLines: {
                                drawBorder: false,
                                lineWidth: 0.3,
                                color: '#000000',
                                zeroLineColor: '#c0e0d0'
                            },
                        }],
                        xAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: s[0],
                                fontColor: "#4d759e",
                                fontSize: 15
                            },
                            gridLines: {
                                drawOnChartArea: false,

                            },
                            barPercentage: 0.8
                        }]
                    },
                    legend: {
                        display: false
                    },
                    plugins: {
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            color: '#000000'
                        }
                    }
                }
            });
        });
    };

    var instance = new Gradedist();

    /*
     * Function initializer(config) prepares settings form for JS-functionality
     */
    instance.initializer = function(config) {

        log.info('Initialize settings JS', 'gradedist');

        require(['canvastoBlob']); // Load canvastoBlob
        var initdata = config.data;

        window.mode = 0;
        var letters = initdata.letters;

        window.absolut = [];
        window.percent = [];
        window.absolutnew = [];
        window.percentnew = [];

        var submitSelector = "#id_submitbutton";
        window.submit = $(submitSelector);
        if (window.submit.length) {
            window.submit.prop('disabled', true);
        }

        $.map(letters, function(letter) {
            window.absolut.push(initdata.actdist[letter].count);
            window.percent.push(initdata.actdist[letter].percentage);
            window.absolutnew.push(initdata.newdist[letter].count);
            window.percentnew.push(initdata.newdist[letter].percentage);
        });

        window.chart = [];

        require(['ChartDataLabels'], function() {
            window.Chart.plugins.register({
                beforeDraw: function(chartInstance) {
                    var ctx = chartInstance.chart.ctx;
                    ctx.fillStyle = "white";
                    ctx.fillRect(0, 0, chartInstance.chart.width, chartInstance.chart.height);
                }
            });
            instance.initChart(initdata, letters);
        });

        var uri = M.cfg.wwwroot + '/grade/report/gradedist/ajax_handler.php?id=' + initdata.courseid;

        var cfg = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            complete: function(arg1) {
                instance.update(arg1);
            },
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
            var values, valuesNew;

            var yAxisLabel;
            var yAxisMax;
            if (window.mode == 1) {
                values = window.percent;
                valuesNew = window.percentnew;
                yAxisLabel = 'percent';
                yAxisMax = 100;
            } else {
                values = window.absolut;
                valuesNew = window.absolutnew;
                yAxisLabel = 'absolut';
                yAxisMax = Math.max(Math.max.apply(this, window.absolut), Math.max.apply(this, window.absolutnew));
            }

            str.get_string(yAxisLabel, 'gradereport_gradedist').done(function(s) {
                window.chart.options.scales.yAxes[0].scaleLabel.labelString = s;
                window.chart.options.scales.yAxes[0].ticks.suggestedMax = yAxisMax;

                window.chart.data.datasets[0].data = values;
                window.chart.data.datasets[1].data = valuesNew;
                window.chart.update();
            });
        });

        var cols = $('#id_grp_columns_actualcolumns, #id_grp_columns_newcolumns');
        cols.click(instance, function() {
            var column = (this.id === 'id_grp_columns_actualcolumns') ? 0 : 1;
            if (this.checked) {
                window.chart.data.datasets[column].hidden = false;
                window.chart.update();
            } else {
                window.chart.data.datasets[column].hidden = true;
                window.chart.update();
            }
        });

        var toprint = $('.grgd_print');
        var $printframe = $('#printframe');
        toprint.click(instance, function() {
            var imagePath = document.getElementById('chart_container').toDataURL("image/png");
            var content = '<!DOCTYPE html>' +
                  '<html>' +
                  '<head><title></title><script></script></head>' +
                  '<body onload="">' +
                  '<img src="' + imagePath + '" style="width: 100%;" />' +
                  '</body>' +
                  '</html>';
            $printframe[0].contentWindow.document.write(content);
            setTimeout(function() {
                $printframe[0].contentWindow.print();
            }, 200);
        });


        var topdf = $('.grgd_pdf');
        topdf.click(instance, function() {
            require(['html2pdf'], function(html2pdf) {
                var container = document.getElementById('chart_container');
                var positionInfo = container.getBoundingClientRect();
                var contheight = positionInfo.height;
                var contwidth = positionInfo.width;
                var opt = {
                  margin:       1,
                  filename:     'chart.pdf',
                  image:        {type: 'png'},
                  pagebreak:    {mode: 'avoid-all'},
                  html2canvas:  {backgroundColor: '#ffffff'},
                  jsPDF:        {unit: 'px', format: [contwidth, contheight * 1.1], orientation: 'landscape'}
                };
                html2pdf(container, opt);
            });
        });

        var topng = $('.grgd_png');
        topng.click(instance, function() {
            // Cross-browser compatibility (with IE 11) required.
            require(['filesaver'],
                function(saveAs) {
                // Cross-browser compatibility (with IE 11) required.
                $("#chart_container").get(0).toBlob(function(blob) {
                    saveAs(blob, "chart.png");
                });
            });
        });

        var tojpg = $('.grgd_jpg');
        tojpg.click(instance, function() {
            require(['filesaver'],
                function(saveAs) {
                // Cross-browser compatibility (with IE 11) required.
                $("#chart_container").get(0).toBlob(function(blob) {
                    saveAs(blob, "chart.jpg");
                }, "image/jpeg");
            });
        });

        var toxlsx = $('.grgd_xlsx');
        toxlsx.click(instance, function() {
            $("input[name='grp_export']").val('xlsx');
            $('#letterform').submit();
        });

        var toods = $('.grgd_ods');
        toods.click(instance, function() {
            $("input[name='grp_export']").val('ods');
            $('#letterform').submit();
        });

        var tocsv = $('.grgd_csv');
        tocsv.click(instance, function() {
            $("input[name='grp_export']").val('csv');
            $('#letterform').submit();
        });

        $('#letterform input[type="submit"]').click(function() {
            setTimeout(function() {
                instance.validate();
            }, 1000);
        });

        instance.validate();
    };

    return instance;
});
