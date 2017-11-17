

Espo.define('Advanced:Views.Report.Reports.Charts.Base', ['View', 'lib!Flotr'], function (Dep, Flotr) {

    return Dep.extend({

        template: 'advanced:report.reports.charts.chart',

        decimalMark: '.',

        thousandSeparator: ',',

        colors: ['#6FA8D6', '#4E6CAD', '#EDC555', '#ED8F42', '#DE6666', '#7CC4A4', '#8A7CC2', '#D4729B'],

        colorsAlt: ['#6FA8D6', '#EDC555', '#ED8F42', '#7CC4A4', '#D4729B'],

        successColor: '#5ABD37',

        init: function () {
            Dep.prototype.init.call(this);

            this.flotr = Flotr;

            if (this.getPreferences().has('decimalMark')) {
                this.decimalMark = this.getPreferences().get('decimalMark')
            } else {
                if (this.getConfig().has('decimalMark')) {
                    this.decimalMark = this.getConfig().get('decimalMark')
                }
            }
            if (this.getPreferences().has('thousandSeparator')) {
                this.thousandSeparator = this.getPreferences().get('thousandSeparator')
            } else {
                if (this.getConfig().has('thousandSeparator')) {
                    this.thousandSeparator = this.getConfig().get('thousandSeparator')
                }
            }

            this.once('after:render', function () {
                $(window).on('resize.reportchart', function () {
                    this.drow();
                }.bind(this));
            }, this);

            this.once('remove', function () {
                $(window).off('resize.report-chart')
            }, this);

            this.result = this.options.result;
            this.column = this.options.column;
            this.reportHelper = this.options.reportHelper;
        },

        formatNumber: function (value) {
            if (value !== null) {
                var parts = value.toString().split(".");
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, this.thousandSeparator);
                if (parts[1] == 0) {
                    parts.splice(1, 1);
                }
                return parts.join(this.decimalMark);
            }
            return '';
        },

        afterRender: function () {
            this.chartData = this.prepareData();

            var $container = this.$container = this.$el.find('.chart-container');

            var height = this.options.height || '350px';
            $container.css('height', height);

            setTimeout(function () {
                this.drow();
            }.bind(this), 1);
        },

    });

});

