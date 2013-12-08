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
        
        $.map(data.olddist, function(grade, index) {
            letters.push(index)
            absolut.push(grade.count);
            percent.push(grade.percentage);
        });
        
        $.map(data.newdist, function(grade, index) {
            absolut_new.push(grade.count);
            percent_new.push(grade.percentage);
        });
        
        var chart = new Highcharts.Chart({
            chart: {
                renderTo: 'chart_container',
                type: 'column'
            },
            title: {
                text: 'Grade distribution'
            },
            xAxis: {
                categories: letters
            },
            yAxis: {
                title: {
                    text: 'Absolute'
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
                    name: 'Actual boundaries',
                    data: absolut,
                    color: '#993300',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        backgroundColor: '#FFFFFF',
                    }
                }, {
                    name: 'New boundaries',
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

                $.map(data.olddist, function(grade, index) {
                    absolut.push(grade.count);
                    percent.push(grade.percentage);
                });
                
                var values = (mode) ? percent : absolut;
                chart.series[0].setData(values);
            }
            absolut_new = [];
            percent_new = [];
            
            $.map(data.newdist, function(grade, index) {
                absolut_new.push(grade.count);
                percent_new.push(grade.percentage);
            });
            
            var newvalues = (mode) ? percent_new : absolut_new;
            chart.series[1].setData(newvalues);
        }
        
        var uri = M.cfg.wwwroot+'/grade/report/gradedist/ajax_handler.php?courseid=' + data.courseid;
        var cfg = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            },
            form: {
                id: 'mform1',
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
            Y.io(uri, cfg); 
        });
        
        var desc = Y.all('#fgroup_id_grp_description input[type=radio]');
        desc.on('change', function (e) {
            mode = e.currentTarget.get('value');
            var values = (mode) ? percent : absolut;
            var values_new = (mode) ? percent_new : absolut_new;
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