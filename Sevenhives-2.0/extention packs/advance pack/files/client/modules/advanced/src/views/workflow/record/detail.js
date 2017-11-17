

Espo.define('Advanced:Views.Workflow.Record.Detail', 'Views.Record.Detail', function (Dep) {

    return Dep.extend({

        editModeEnabled: false,

        editModeDisabled: true,

        bottomView: 'Advanced:Workflow.Record.DetailBottom',

        duplicateAction: true

    });
});


