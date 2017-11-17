

Espo.define('Advanced:Views.Report.Reports.Charts.Grid2Line', 'Advanced:Views.Report.Reports.Charts.Grid2BarVertical', function (Dep) {

    return Dep.extend({
    
        
        drow: function () {
            var self = this;
            
            this.flotr.draw(this.$container.get(0), this.chartData, {
                shadowSize: false,
                colors: this.colorList,
                lines: {
                    show: true,
                },
                grid: {
                    horizontalLines: true,
                    verticalLines: true,
                    outline: 'sw',
                },
                yaxis: {
                    min: 0,
                    showLabels: true,
                    autoscale: true,
                    autoscaleMargin: 1,
                    tickFormatter: function (value) {
                        if (value != 0 && value % 1 == 0) {        
                            return Math.floor(value).toString();
                        }
                        return '';
                    },                    
                },
                xaxis: {
                    min: 0,
                    tickFormatter: function (value) {
                        if (value % 1 == 0) {
                            var i = parseInt(value);
                            if (i in self.firstList) {
                                return self.formatGroup(0, self.firstList[i]);
                            }
                        }
                        return '';                        
                    },
                },
                mouse: {
                    track: true,
                    relative: true,
                    trackFormatter: function (obj) {        
                        return self.sums[Math.floor(obj.x)];
                    },
                },
                legend: {
                    show: true,
                    noColumns: 8,
                    container: this.$el.find('.legend-container'),
                    labelBoxMargin: 0
                },
            });
        },
    });

});

