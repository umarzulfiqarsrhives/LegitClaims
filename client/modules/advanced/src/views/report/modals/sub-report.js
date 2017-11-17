Espo.define('Advanced:Views.Report.Modals.SubReport', ['Views.Modal', 'Advanced:ReportHelper'], function (Dep, ReportHelper) {

    return Dep.extend({

        cssName: 'sub-report',

        _template: '<div class="list-container">{{{list}}}</div>',

        setup: function () {
            this.buttonList = [
                {
                    name: 'cancel',
                    label: 'Close'
                }
            ];

            var result = this.options.result;

            var reportHelper = new ReportHelper(this.getMetadata(), this.getLanguage());
            var groupValue = this.options.groupValue;

            this.header = this.model.get('name') + ': ' + reportHelper.formatGroup(result.groupBy[0], groupValue, result);

            this.createView('list', 'Advanced:Record.ListForReport', {
                el: this.options.el + ' .list-container',
                collection: this.collection,
                type: 'listSmall',
                reportId: this.model.id,
                groupValue: groupValue
            });
        },

    });
});

