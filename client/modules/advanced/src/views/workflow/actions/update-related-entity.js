

Espo.define('Advanced:views/workflow/actions/update-related-entity', 'Advanced:views/workflow/actions/base', function (Dep) {

    return Dep.extend({

        type: 'updateRelatedEntity',

        defaultActionData: {
            link: false,
            fieldList: [],
            fields: {},
        },

        additionalSetup: function() {
            Dep.prototype.additionalSetup.call(this);

            if (this.actionData.link) {
                var linkData = this.getMetadata().get('entityDefs.' + this.entityType + '.links.' + this.actionData.link);

                this.linkedEntityName = linkData.entity || this.entityType;
                this.displayedLinkedEntityName = null;
                if (linkData.type == 'belongsToParent') {
                    this.linkedEntityName = this.actionData.parentEntity || this.linkedEntityName;
                    this.displayedLinkedEntityName = this.translate(this.actionData.link, 'links' , this.entityType) + ' &raquo; ' + this.translate(this.actionData.parentEntity, 'scopeNames');
                }
            }
        }

    });
});

