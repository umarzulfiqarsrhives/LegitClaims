

Espo.define('Advanced:Views.TargetList.Fields.SyncWithReports', 'Views.Fields.LinkMultiple', function (Dep) {

    return Dep.extend({

        getSelectPrimaryFilterName: function () {
            return 'listTargets';
        },

    });
});
