

Espo.define('Advanced:Views.Report.Record.List', 'Views.Record.List', function (Dep) {

    return Dep.extend({

        allowQuickEdit: false,

        mergeAction: false,

        exportAction: false,

        massActionList: ['remove', 'massUpdate'],

    });
});

