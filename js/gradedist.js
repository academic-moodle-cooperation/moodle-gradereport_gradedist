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
        var absolut_new = [];
        var percent_new = [];
        
        $.map(data.actdist, function(grade, index) {
            letters.push(index)
            absolut.push(grade.count);
            percent.push(Math.round(grade.percentage));
        });
        
        $.map(data.newdist, function(grade, index) {
            absolut_new.push(grade.count);
            percent_new.push(Math.round(grade.percentage));
        });
        
        var chart = new Highcharts.Chart({
            chart: {
                renderTo: 'chart_container',
                type: 'column'
            },
            title: {
                style: {
                    display: 'none'
                }
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
            series:
                [{
                    data: absolut,
                    color: '#993300',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        backgroundColor: '#FFFFFF',
                    }
                }, {
                    data: absolut_new,
                    color: '#006600',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        backgroundColor: '#FFFFFF',
                        align: 'center',
                    }
                }]
        });
        chart.update = function(id, o, args) {
            
            data = Y.JSON.parse(o.responseText);
            
            if(data.updateall) {
                absolut = [];
                percent = [];

                $.map(data.actdist, function(grade, index) {
                    absolut.push(grade.count);
                    percent.push(Math.round(grade.percentage));
                });
                
                var values = (mode) ? percent : absolut;
                chart.series[0].setData(values);
            }
            absolut_new = [];
            percent_new = [];
            
            $.map(data.newdist, function(grade, index) {
                absolut_new.push(grade.count);
                percent_new.push(Math.round(grade.percentage));
            });
            
            coverage(data);
            var newvalues = (mode) ? percent_new : absolut_new;
            chart.series[1].setData(newvalues);
        }
        
        var uri = M.cfg.wwwroot+'/grade/report/gradedist/ajax_handler.php?id=' + data.courseid;
        var cfg = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            },
            form: {
                id: 'letterform',
                useDisabled: true,
                upload: false
            },
            on: {
                complete: chart.update
            }
        };
        
        var gradeitems = Y.one('#id_gradeitem');
        gradeitems.on('change', function (e) {
            cfg.data = 'updateall=1';
            Y.io(uri, cfg);
        });
        
        var boundaries = Y.all('#fgroup_id_grp_gradeboundaries_new input[type=text]');
        boundaries.on('change', function (e) {
            var notifications = Y.all('#page-grade-report-gradedist-index .notifyproblem, #page-grade-report-gradedist-index .notifysuccess');
            if (notifications) {
                notifications.remove();
            }
            if(validate()) Y.io(uri, cfg);
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
            
            var decimals = /^\d+([,.]\d{1,2})?$/;
            var pre = 100.01;
            
            boundaries.each(function(boundary) {
                var value = boundary.get('value');
                if (value != '') {
                    if (!decimals.test(value))
                        errdec = true;
                    if (Number(value) > 100)
                        errint = true;
                    if (Number(value) >= Number(pre)) {
                        errpre = true;
                    }
                    
                    pre = value;
                } else {
                    erremp = true;
                }
            });
            
            if (errdec) {
                if (!errdecdiv)
                    Y.one('#fgroup_id_grp_gradeboundaries_new').append('<div class="b_error" id="b_decimals"><span>' + M.str.gradereport_gradedist.decimals + '</span></div>');
                
                error = true;
            } else if (errdecdiv) {
                errdecdiv.remove();
            }
            if (errint) {
                if (!errintdiv)
                    Y.one('#fgroup_id_grp_gradeboundaries_new').append('<div class="b_error" id="b_interval"><span>' + M.str.gradereport_gradedist.interval + '</span></div>');
                
                error = true;
            } else if (errintdiv) {
                errintdiv.remove();
            }
            if (errpre) {
                if (!errprediv)
                    Y.one('#fgroup_id_grp_gradeboundaries_new').append('<div class="b_error" id="b_predecessor"><span>' + M.str.gradereport_gradedist.predecessor + '</span></div>');
                
                error = true;
            } else if (errprediv) {
                errprediv.remove();
            }
            
            Y.one('#id_submitbutton').set('disabled', error || erremp);
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
                if (!errcovdiv)
                    Y.one('#fgroup_id_grp_gradeboundaries_new').append('<div class="b_error" id="b_coverage"><span>' + M.str.gradereport_gradedist.coverage + '</span><span class="newcoverage">' + data.newcoverage[0] + '/' + data.newcoverage[1] + '</span></div>');
            
            } else if (errcovdiv) {
                errcovdiv.remove();
            }
            
            Y.all('.actcoverage').setContent(data.actcoverage[0] + '/' + data.actcoverage[1]);
            Y.all('.newcoverage').setContent(data.newcoverage[0] + '/' + data.newcoverage[1]);
        }
        
        var desc = Y.all('#fgroup_id_grp_description input[type=radio]');
        desc.on('change', function (e) {
            mode = e.currentTarget.get('value');
            var values, values_new;
            
            if (mode) {
                values = percent;
                values_new = percent_new;
                chart.yAxis[0].axisTitle.attr({
                    text: M.str.gradereport_gradedist.percent
                });
                chart.yAxis[0].setExtremes(0, 100);
            } else {
                values = absolut;
                values_new = absolut_new;
                chart.yAxis[0].axisTitle.attr({
                    text: M.str.gradereport_gradedist.absolut
                });
                chart.yAxis[0].setExtremes(0, null);
            }
            chart.series[0].setData(values);
            chart.series[1].setData(values_new);
        });
        
        var cols = Y.all('#fgroup_id_grp_columns input[type=checkbox]');
        cols.on('click', function (e) {
            var column = (e.currentTarget.get('id') == 'id_grp_columns_actualcolumns') ? 0 : 1;
            if(e.currentTarget.get('checked')) {
                chart.series[column].show();
            } else {
                chart.series[column].hide();
            }
        });
    }
}