

Espo.define('Advanced:Views.Report.Reports.Grid1', 'Advanced:Views.Report.Reports.Base', function (Dep) {

    return Dep.extend({

        setup: function () {
            this.initReport();
        },

        run: function () {
            this.notify('Please wait...');

            $container = this.$el.find('.report-results-container');
            $container.empty();
            var where = this.getRuntimeFilters();
            $.ajax({
                url: 'Report/action/run',
                data: {
                    id: this.model.id,
                    where: where
                },
            }).done(function (result) {
                this.notify(false);

                this.result = result;

                this.storeRuntimeFilters();

                $tableContainer = $('<div>').addClass('report-table');

                var exportUrl = '?entryPoint=reportAsCsv&id=' + this.model.id + '&where=' + encodeURIComponent(JSON.stringify(where));
                $export = $('<a>').attr('href', exportUrl)
                               .addClass('pull-right')
                               .text(this.translate('Get CSV', 'labels', 'Report'));

                $container.append($export);

                $container.append($tableContainer);

                if (this.chartType) {
                    result.columns.forEach(function (column, i) {
                        $column = $('<div>').addClass('column-' + i).css('margin-bottom', '30px');
                        $header = $('<h4>' + this.options.reportHelper.formatColumn(column, result) + '</h4>');
                        $chartContainer = $('<div>').addClass('report-chart').addClass('report-chart-' + i).css({
                            'overflow-y': 'auto',
                            'margin-bottom': '30px'
                        });

                        $column.append($header);
                        $column.append($chartContainer);
                        $container.append($column);
                    }, this);
                }


                this.createView('reportTable', 'Advanced:Report.Reports.Tables.Grid1', {
                    el: this.options.el + ' .report-results-container .report-table',
                    result: result,
                    reportHelper: this.options.reportHelper
                }, function (view) {
                    view.render();
                });

                result.columns.forEach(function (column, i) {
                    if (this.chartType) {
                        this.createView('reportChart' + i, 'Advanced:Report.Reports.Charts.Grid1' + this.chartType, {
                            el: this.options.el + ' .report-results-container .column-' + i + ' .report-chart',
                            column: column,
                            result: result,
                            reportHelper: this.options.reportHelper
                        }, function (view) {
                            view.render();
                        });
                    }
                }, this);


            }.bind(this));
        },

        getPDF: function (id, where) {
            this.getRouter();
        }

    });

});

