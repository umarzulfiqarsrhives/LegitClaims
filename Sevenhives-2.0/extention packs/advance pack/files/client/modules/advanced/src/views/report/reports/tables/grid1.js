

Espo.define('Advanced:Views.Report.Reports.Tables.Grid1', ['View', 'Advanced:Views.Report.Reports.Tables.Grid2'], function (Dep, Grid2) {

    return Dep.extend({

        template: 'advanced:report.reports.tables.table',

        setup: function () {
            this.column = this.options.column;
            this.result = this.options.result;
            this.reportHelper = this.options.reportHelper;
        },


        formatCellValue: function (value, column, isTotal) {
            return Grid2.prototype.formatCellValue.call(this, value, column, isTotal);
        },

        formatNumber: function (value) {
            return Grid2.prototype.formatNumber.call(this, value);
        },

        afterRender: function () {
            var result = this.result;

            var groupBy = this.result.groupBy[0];

            $table = $('<table>').addClass('table').addClass('table-bordered');
            var $tr = $('<tr>');
            $tr.append($('<th>'));

            this.result.columns.forEach(function (col) {
                $tr.append($('<th>').html(this.reportHelper.formatColumn(col, this.result) + '&nbsp;'));
            }, this);
            $table.append($tr);

            var reportData = this.options.reportData;

            this.result.grouping[0].forEach(function (gr) {
                var $tr = $('<tr>');
                var html = '<a href="javascript:" data-action="showSubReport" data-group-value="'+gr+'">' + this.reportHelper.formatGroup(groupBy, gr, this.result) + '</a>&nbsp;';
                $tr.append($('<td>').html(html));

                this.result.columns.forEach(function (col) {
                    var value = 0;
                    if (gr in result.reportData) {
                        value = result.reportData[gr][col] || value;
                    }
                    $tr.append($('<td align="right">').html(this.formatCellValue(value, col)));
                }, this);

                $table.append($tr);
            }, this);

            var $tr = $('<tr>');

            $tr.append($('<td>').html('<b>' + this.translate('Total', 'labels', 'Report') + '</b>'));
            this.result.columns.forEach(function (col) {
                value = result.sums[col] || 0;

                $tr.append($('<td align="right">').html('<b>' + this.formatCellValue(value, col, true) + '</b>' + ''));
            }, this);

            $table.append($tr);

            this.$el.find('.table-container').append($table);
        }

    });

});

