

Espo.define('Advanced:Views.Report.Reports.Charts.Grid1BarVertical', 'Advanced:Views.Report.Reports.Charts.Grid2BarVertical', function (Dep) {

    return Dep.extend({

        prepareData: function () {
            var result = this.result;
            var grList = this.grList = result.grouping[0];

            var i = 0;
            var data = [[]];
            this.values = [];

            grList.forEach(function (gr) {
                var value = (this.result.reportData[gr] || {})[this.column] || 0;
                this.values.push(value);
                data[0].push([
                    i, value
                ]);
                i++;
            }, this);

            return data;
        },

        drow: function () {
            var self = this;

            this.flotr.draw(this.$container.get(0), this.chartData, {
                shadowSize: false,
                colors: this.colorsAlt,
                bars: {
                    show: true,
                    horizontal: false,
                    shadowSize: 0,
                    lineWidth: 1,
                    fillOpacity: 1,
                    barWidth: 0.5,
                },
                grid: {
                    horizontalLines: true,
                    verticalLines: false,
                    outline: 'sw'
                },
                yaxis: {
                    min: 0,
                    showLabels: true,
                    tickFormatter: function (value) {
                        if (value == 0) return '';
                        if (value % 1 == 0) {
                            return Math.floor(value).toString();
                        }
                        return '';
                    },
                },
                xaxis: {
                    min: 0,
                    noTicks: 10,
                    tickFormatter: function (value) {
                        if (value % 1 == 0) {
                            var i = parseInt(value);
                            if (i in self.grList) {
                                return self.formatGroup(0, self.grList[i]);
                            }
                        }
                        return '';
                    },
                },
                mouse: {
                    track: true,
                    relative: true,
                    position: 's',
                    trackFormatter: function (obj) {
                        var i = Math.floor(obj.x);
                        return self.formatGroup(0, self.grList[i]) + ':<br>' + self.values[i];
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

