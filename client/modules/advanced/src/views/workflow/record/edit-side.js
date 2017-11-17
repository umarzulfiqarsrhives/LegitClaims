

Espo.define('Advanced:Views.Workflow.Record.EditSide', 'Views.Record.EditSide', function (Dep) {

    return Dep.extend({

        panels: [
            {
                name: 'default',
                label: false,
                view: 'Advanced:Workflow.Record.Panels.Side'
            }
        ],

    });
});