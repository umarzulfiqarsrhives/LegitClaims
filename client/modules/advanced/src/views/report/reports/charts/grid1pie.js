

Espo.define('Advanced:Views.Report.Reports.Charts.Grid1Pie', 'Advanced:Views.Report.Reports.Charts.Grid1BarVertical', function (Dep) {

    return Dep.extend({

        prepareData: function () {
            var result = this.result;
            var grList = this.grList = result.grouping[0];

            var data = [];
            this.values = [];

            grList.forEach(function (gr, i) {
                var value = (this.result.reportData[gr] || {})[this.column] || 0;
                this.values.push(value);
                data.push({
                    label: this.formatGroup(0, gr),
                    data: [[0, value]],
                    value: value
                });

            }, this);

            return data;
        },

        drow: function () {
            var self = this;

            this.flotr.draw(this.$container.get(0), this.chartData, {
                shadowSize: false,
                colors: this.colorsAlt,
                pie: {
                    show: true,
                    fillOpacity: 1,
                    explode: 0,
                    lineWidth: 1,
                },
                grid: {
                    horizontalLines: false,
                    verticalLines: false,
                    outline: ''
                },

                yaxis: {
                    showLabels: false
                },
                xaxis: {
                    showLabels: false
                },
                mouse: {
                    track: true,
                    relative: true,
                    trackFormatter: function (obj) {
                        var value = self.formatNumber(obj.series.value);
                        return (obj.series.label || self.translate('-Empty-', 'labels', 'Report')) + ':<br>' + value;
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

