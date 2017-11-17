

Espo.define('Advanced:Views.Workflow.ActionModals.CreateEntity', 'Advanced:Views.Workflow.ActionModals.Base', function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow.action-modals.create-entity',

        data: function () {
            return _.extend({
                link: this.actionData.link,
                linkOptions: this.getLinkOptionsHtml(),
                scope: this.scope,
            }, Dep.prototype.data.call(this));
        },

        events: {
            'change [name="link"]': function (e) {
                this.changeLinkAction(e);
            },
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

            this.handleLink();

            (this.actionData.fieldList || []).forEach(function (field) {
                this.addField(field, this.actionData.fields[field]);
            }, this);
        },

        setupScope: function (callback) {
            if (this.actionData.link) {
                var scope = this.actionData.link;
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

        setup: function () {
            Dep.prototype.setup.call(this);

            this.wait(true);
            this.setupScope(function () {
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

        handleLink: function () {
            var link = this.actionData.link;

            if (!link) {
                this.clearView('addField');
                return;
            }

            this.setupScope(function () {
                this.createView('addField', 'Advanced:Workflow.ActionFields.AddField', {
                    el: this.options.el + ' .add-field-container',
                    scope: this.scope,
                    fieldList: this.getFieldList(),
                }, function (view) {
                    view.render();
                });
            }.bind(this));
        },

        getFieldList: function () {
            var fieldDefs = this.getMetadata().get('entityDefs.' + this.scope + '.fields') || {};
            var fieldList = Object.keys(fieldDefs).filter(function(field) {
                var type = fieldDefs[field].type;
                if (fieldDefs[field].disabled) return false;
                if (!['currencyConverted'].indexOf(type)) {
                    return false;
                }
                return true;
            }).sort(function (v1, v2) {
                 return this.translate(v1, 'fields', this.scope).localeCompare(this.translate(v2, 'fields', this.scope));
            }.bind(this));
            return fieldList;
        },

        getLinkOptionsHtml: function () {
            var value = this.actionData.link;

            var html = '<option value="">--' + this.translate('Select') + '--</option>';

            var list = this.getEntityList();

            list.forEach(function (entityName) {
                var label = this.translate(entityName, 'scopeNames');
                html += '<option value="' + entityName + '" ' + (entityName === value ? 'selected' : '') + '>' + label + '</option>';
            }, this);

            return html;
        },

        fetch: function () {
            var isValid = true;
            (this.actionData.fieldList || []).forEach(function (field) {
                isValid = this.getView('field-' + field).fetch();
                this.actionData.fields[field] = this.getView('field-' + field).fieldData;
            }, this);

            return isValid;
        },

        getEntityList: function() {
            var scopes = this.getMetadata().get('scopes');

            var entityList = Object.keys(scopes).filter(function (scope) {
                var defs = scopes[scope];
                return (defs.entity && (defs.tab || defs.object));
            }).sort(function (v1, v2) {
                 return this.translate(v1, 'scopeNamesPlural').localeCompare(this.translate(v2, 'scopeNamesPlural'));
            }.bind(this));

            return entityList;
        },

        changeLinkAction: function (e) {
            this.actionData.link = e.currentTarget.value;

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
