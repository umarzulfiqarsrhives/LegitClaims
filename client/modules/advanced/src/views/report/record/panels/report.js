

Espo.define('Advanced:Views.Report.Record.Panels.Report', ['View', 'Advanced:ReportHelper'], function (Dep, ReportHelper) {

    return Dep.extend({

        template: 'advanced:report.record.panels.report',

        setup: function () {
            var type = this.model.get('type');
            var groupBy = this.model.get('groupBy') || [];

            var reportHelper = new ReportHelper(this.getMetadata(), this.getLanguage());



            switch (type) {
                case 'Grid':
                    var depth = this.model.get('depth') || groupBy.length;
                    if (depth < 1 || depth > 2) {
                        throw new Error('Bad report');
                    }
                    var viewName = 'Advanced:Report.Reports.Grid' + depth.toString();
                    this.createView('report', viewName, {
                        el: this.options.el + ' .report-container',
                        model: this.model,
                        reportHelper: reportHelper
                    });
                    break;
                case 'List':
                    var viewName = 'Advanced:Report.Reports.List';
                    this.createView('report', viewName, {
                        el: this.options.el + ' .report-container',
                        model: this.model,
                        reportHelper: reportHelper
                    });
                    break;

            }

        },

        afterRender: function () {

        },

        actionRefresh: function () {
            var report = this.getView('report');
            if (!report.hasRuntimeFilters()) {
                report.run();
            }
        }

    });

});

