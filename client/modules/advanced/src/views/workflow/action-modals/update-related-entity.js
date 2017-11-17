

Espo.define('Advanced:Views.Workflow.ActionModals.UpdateRelatedEntity', 'Advanced:Views.Workflow.ActionModals.CreateEntity', function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow.action-modals.update-related-entity',

        permittedLinkTypes: ['belongsTo'],

        getLinkOptionsHtml: function () {
            var value = this.actionData.link;

            var list = Object.keys(this.getMetadata().get('entityDefs.' + this.entityType + '.links'));

            var html = '<option value="">--' + this.translate('Select') + '--</option>';

            list.forEach(function (item) {
                var defs = this.getMetadata().get('entityDefs.' + this.entityType + '.links.' + item);

                if (~this.permittedLinkTypes.indexOf(defs.type)) {
                    if (defs.entityList) {
                        defs.entityList.forEach(function (parentEntity) {
                            var selected = (item === value && this.actionData.parentEntity == parentEntity) ? 'selected' : '';

                            var label = this.translate(item, 'links' , this.entityType) + ' &raquo; ' + this.translate(parentEntity, 'scopeNames');
                            html += '<option value="' + item + '-'+parentEntity+'" ' + selected + ' data-link="'+item+'" data-parent-entity="'+parentEntity+'">' + label + '</option>';
                        }.bind(this));
                    } else {
                        var label = this.translate(item, 'links' , this.entityType);
                        html += '<option value="' + item + '" ' + (item === value ? 'selected' : '') + '>' + label + '</option>';
                    }

                }

            }, this);

            return html;
        },

        setupScope: function (callback) {

            if (this.actionData.link) {
                var scope = this.actionData.parentEntity || this.getMetadata().get('entityDefs.' + this.entityType + '.links.' + this.actionData.link + '.entity');
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

        changeLinkAction: function (e) {
            var $option = $(e.currentTarget).find('option[value="'+e.currentTarget.value+'"]');

            var value = e.currentTarget.value;

            delete this.actionData.parentEntity;
            if ($option.attr('data-link')) {
                value = $option.attr('data-link');
                this.actionData.parentEntity = $option.attr('data-parent-entity');
            }

            this.actionData.link = value;

            this.actionData.fieldList.forEach(function (field) {
                this.$el.find('.field-row[data-field="' + field + '"]').remove();
                this.clearView('field-' + field);
            }, this);
            this.actionData.fieldList = [];
            this.actionData.fields = {};

            this.handleLink();
        }

    });
});
