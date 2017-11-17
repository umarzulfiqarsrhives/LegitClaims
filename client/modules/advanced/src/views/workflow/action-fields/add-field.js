

Espo.define('Advanced:Views.Workflow.ActionFields.AddField', 'View', function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow.action-fields.add-field',

        data: function () {
            return {
                fieldList: this.options.fieldList,
                scope: this.options.scope,
            };
        },


    });
});

