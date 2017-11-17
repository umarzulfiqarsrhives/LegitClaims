

Espo.define('Advanced:Views.Workflow.Actions.UpdateEntity', 'Advanced:Views.Workflow.Actions.Base', function (Dep) {

    return Dep.extend({

        type: 'updateEntity',

        defaultActionData: {
            fieldList: [],
            fields: {},
        },

        additionalSetup: function() {
            Dep.prototype.additionalSetup.call(this);

            this.linkedEntityName = this.entityType;
        }

    });
});

