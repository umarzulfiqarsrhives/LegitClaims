

Espo.define('Advanced:Views.Report.Reports.Charts.Grid2BarVertical', 'Advanced:Views.Report.Reports.Charts.Base', function (Dep) {

    return Dep.extend({

        prepareData: function () {
            var result = this.result;

            var firstList = this.firstList = result.grouping[0];
            var secondList = this.secondList = result.grouping[1];

            if (secondList.length > 5) {
                this.colorList = this.colors;
            } else {
                this.colorList = this.colorsAlt;
            }

            var columns = [];

            var i = 0;

            this.sums = [];

            firstList.forEach(function (gr1) {
                var d = {};
                var sum = 0;
                secondList.forEach(function (gr2) {
                    if (result.reportData[gr1] && result.reportData[gr1][gr2]) {
                        d[gr2] = result.reportData[gr1][gr2][this.column] || 0;
                    }
                }, this);
                columns.push(d);

                sum = (result.sums[gr1] || {})[this.column] || 0;
                this.sums.push(sum);

                i++;
            }, this);

            var dataByGroup2 = {};

            secondList.forEach(function (gr2) {
                dataByGroup2[gr2] = [];
                columns.forEach(function (d, i) {
                    dataByGroup2[gr2].push([i, d[gr2] || 0]);
                }, this);
            }, this);

            var data = [];
            secondList.forEach(function (gr2, i) {
                var o = {
                    data: dataByGroup2[gr2],
                    label: this.formatGroup(1, gr2)
                };
                if (this.result.success && this.result.success == gr2) {
                    o.color = this.successColor;
                }
                data.push(o);
            }, this);

            return data;
        },

        formatGroup: function (i, value) {
            var gr = this.result.groupBy[i];
            return this.reportHelper.formatGroup(gr, value, this.result);
        },

        drow: function () {
            var self = this;

            this.flotr.draw(this.$container.get(0), this.chartData, {
                shadowSize: false,
                colors: this.colorList,
                bars: {
                    show: true,
                    stacked : true,
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

