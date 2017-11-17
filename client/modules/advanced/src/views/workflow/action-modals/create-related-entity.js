

Espo.define('Advanced:Views.Workflow.ActionModals.CreateRelatedEntity', 'Advanced:Views.Workflow.ActionModals.CreateEntity', function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow.action-modals.create-related-entity',

        getLinkOptionsHtml: function () {
            var value = this.actionData.link;

            var list = Object.keys(this.getMetadata().get('entityDefs.' + this.entityType + '.links') || []).sort(function (v1, v2) {
                 return this.translate(v1, 'links', this.scope).localeCompare(this.translate(v2, 'links', this.scope));
            }.bind(this));

            var html = '<option value="">--' + this.translate('Select') + '--</option>';

            list.forEach(function (item) {
                var defs = this.getMetadata().get('entityDefs.' + this.entityType + '.links.' + item);
                if ((defs.type != 'hasMany' && defs.type != 'hasChildren')) {
                    return;
                }
                var label = this.translate(item, 'links' , this.entityType);
                html += '<option value="' + item + '" ' + (item === value ? 'selected' : '') + '>' + label + '</option>';
            }, this);

            return html;
        },

        setupScope: function (callback) {
            if (this.actionData.link) {
                var scope = this.getMetadata().get('entityDefs.' + this.entityType + '.links.' + this.actionData.link + '.entity');
                this.scope = scope;

                if (scope) {
                    this.wait(true);
                    this.getModelFactory().create(scope, function (model) {
                        this.model = model;

                        (this.actionData.fieldList || []).forEach(function (field) {
                            var attributes = (this.actionData.fields[field] || {}).attributes || {};
                            model.set(attributes, {silent: true});
                        }, this);

                        callback();
                    }, this);
                } else {
                    throw new Error;
                }
            } else {
                this.model = null;
                callback();
            }
        },

    });
});
