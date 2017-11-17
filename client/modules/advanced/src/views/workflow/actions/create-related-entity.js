

Espo.define('Advanced:Views.Workflow.Actions.CreateRelatedEntity', 'Advanced:Views.Workflow.Actions.Base', function (Dep) {

    return Dep.extend({

        type: 'createRelatedEntity',

        defaultActionData: {
            link: false,
            fieldList: [],
            fields: {},
        },

        additionalSetup: function() {
            Dep.prototype.additionalSetup.call(this);

            if (this.actionData.link) {
                this.linkedEntityName = this.getMetadata().get('entityDefs.' + this.entityType + '.links.' + this.actionData.link + '.entity');
            }
        }

    });
});

