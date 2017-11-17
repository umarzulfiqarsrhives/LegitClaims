

Espo.define('Advanced:Views.Report.Reports.Grid2', 'Advanced:Views.Report.Reports.Base', function (Dep) {

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

                result.columns.forEach(function (column, i) {
                    $column = $('<div>').addClass('column-' + i).css('margin-bottom', '30px');

                    var exportUrl = '?entryPoint=reportAsCsv&id=' + this.model.id + '&where=' + encodeURIComponent(JSON.stringify(where)) + '&column=' + column;
                    $export = $('<a>').attr('href', exportUrl)
                                   .addClass('pull-right')
                                   .text(this.translate('Get CSV', 'labels', 'Report'));

                    $header = $('<h4>' + this.options.reportHelper.formatColumn(column, result) + '</h4>');
                    $tableContainer = $('<div>').addClass('report-table').addClass('report-table-' + i).css({
                        'overflow-y': 'auto',
                        'margin-bottom': '30px'
                    });
                    $chartContainer = $('<div>').addClass('report-chart').addClass('report-chart-' + i).css({
                        'overflow-y': 'auto',
                        'margin-bottom': '30px'
                    });

                    $column.append($export);

                    $column.append($header);
                    $column.append($tableContainer);
                    $column.append($chartContainer);

                    $container.append($column);
                }, this);

                result.columns.forEach(function (column, i) {
                    this.createView('reportTable' + i, 'Advanced:Report.Reports.Tables.Grid2', {
                        el: this.options.el + ' .report-results-container .column-' + i + ' .report-table',
                        column: column,
                        result: result,
                        reportHelper: this.options.reportHelper
                    }, function (view) {
                        view.render();
                    });

                    if (this.chartType) {
                        this.createView('reportChart' + i, 'Advanced:Report.Reports.Charts.Grid2' + this.chartType, {
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

    });

});

