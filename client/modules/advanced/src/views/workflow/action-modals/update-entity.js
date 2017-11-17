

Espo.define('Advanced:Views.Workflow.ActionModals.UpdateEntity', ['Advanced:Views.Workflow.ActionModals.Base', 'Advanced:Views.Workflow.ActionModals.CreateEntity'], function (Dep, CreateEntity) {

    return Dep.extend({

        template: 'advanced:workflow.action-modals.update-entity',

        data: function () {
            return _.extend({
                scope: this.scope,
            }, Dep.prototype.data.call(this));
        },

        events: {
            'click [data-action="addField"]': function (e) {
                var $target = $(e.currentTarget);
                var field = $target.data('field');

                if (!~this.actionData.fieldList.indexOf(field)) {

                    this.actionData.fieldList.push(field);
                    this.actionData.fields[field] = {};

                    this.addField(field, false, true);
                }
            },
            'click [data-action="removeField"]': function (e) {
                var $target = $(e.currentTarget);
                var field = $target.data('field');
                this.clearView('field-' + field);

                delete this.actionData.fields[field];

                var index = this.actionData.fieldList.indexOf(field);
                this.actionData.fieldList.splice(index, 1);

                $target.parent().remove();
            }

        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);
            this.$fieldDefinitions = this.$el.find('.field-definitions');

            (this.actionData.fieldList || []).forEach(function (field) {
                this.addField(field, this.actionData.fields[field]);
            }, this);
        },

        setupScope: function (callback) {
            var scope = this.entityType;
            this.scope = scope;

            this.getModelFactory().create(scope, function (model) {
                this.model = model;

                (this.actionData.fieldList || []).forEach(function (field) {
                    var attributes = (this.actionData.fields[field] || {}).attributes || {};
                    model.set(attributes, {silent: true});
                }, this);

                callback();
            }, this);

        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.wait(true);
            this.setupScope(function () {
                this.createView('addField', 'Advanced:Workflow.ActionFields.AddField', {
                    el: this.options.el + ' .add-field-container',
                    scope: this.entityType,
                    fieldList: this.getFieldList(),
                });
                this.wait(false);

            }.bind(this));
        },


        addField: function (field, fieldData, isNew) {
            var fieldType = this.getMetadata().get('entityDefs.' + this.scope + '.fields.' + field + '.type') || 'base';
            var type = this.getMetadata().get('entityDefs.Workflow.fieldDefinitions.' + fieldType) || 'base';

            fieldData = fieldData || false;

            var fieldNameHtml = '<label>' + this.translate(field, 'fields', this.scope) + '</label>';
            var removeLinkHtml = '<a href="javascript:" class="pull-right" data-action="removeField" data-field="' + field + '"><span class="glyphicon glyphicon-remove"></span></a>';
            var html = '<div class="margin clearfix field-row" data-field="' + field + '" style="margin-left: 20px;">' + removeLinkHtml + fieldNameHtml + '<div class="field-container field" data-field="' + field + '"></div></div>';

            this.$fieldDefinitions.append($(html));

            this.createView('field-' + field, 'Advanced:Workflow.FieldDefinitions.' + Espo.Utils.upperCaseFirst(type), {
                el: this.options.el + ' .field-container[data-field="' + field + '"]',
                fieldData: fieldData,
                model: this.model,
                field: field,
                entityType: this.entityType,
                scope: this.scope,
                type: type,
                fieldType: fieldType,
                isNew: isNew
            }, function (view) {
                view.render();
            });
        },

        getFieldList: function () {
            return CreateEntity.prototype.getFieldList.call(this);
        },

        fetch: function () {
            var isValid = true;
            (this.actionData.fieldList || []).forEach(function (field) {
                isValid = this.getView('field-' + field).fetch();
                this.actionData.fields[field] = this.getView('field-' + field).fieldData;
            }, this);

            return isValid;
        },

    });
});
