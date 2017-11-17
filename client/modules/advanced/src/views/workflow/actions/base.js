

Espo.define('Advanced:Views.Workflow.Actions.Base', ['View', 'Model'], function (Dep, Model) {

    return Dep.extend({

        template: 'advanced:workflow.actions.base',

        defaultActionData: {
            execution: {
                type: 'immediately',
                field: false,
                shiftDays: 0,
            }
        },

        data: function () {
            return {
                entityType: this.entityType,
                actionType: this.actionType,
                linkedEntityName: this.linkedEntityName || this.entityType,
                displayedLinkedEntityName: this.displayedLinkedEntityName || this.linkedEntityName || this.entityType,
                actionData: this.actionData,
                readOnly: this.readOnly
            };
        },

        events: {
            'click [data-action="editAction"]': function () {
                this.edit();
            }
        },

        setup: function () {
            this.actionType = this.options.actionType;
            this.id = this.options.id;
            this.readOnly = this.options.readOnly;

            this.actionData = this.options.actionData || {};

            if (this.options.isNew) {
                var cloned = {};
                for (var i in this.defaultActionData) {
                    cloned[i] = Espo.Utils.clone(this.defaultActionData[i]);
                }

                if ('execution' in cloned) {
                    for (var i in cloned.execution) {
                        cloned.execution[i] = Espo.Utils.clone(cloned.execution[i]);
                    }
                }

                this.actionData = _.extend(cloned, this.actionData);
            }

            this.entityType = this.options.entityType;

            this.additionalSetup();
        },

        afterRender: function () {
            this.renderFields();
        },

        edit: function (isNew) {
            this.createView('edit', 'Advanced:Workflow.ActionModals.' + Espo.Utils.upperCaseFirst(this.actionType), {
                actionData: this.actionData,
                actionType: this.actionType,
                entityType: this.entityType,
            }, function (view) {
                view.render();
                if (isNew) {
                    this.listenToOnce(view, 'cancel', function () {
                        setTimeout(function () {
                            this.getParentView().removeAction(this.id);
                        }.bind(this), 200);
                    }, this);
                }

                this.listenToOnce(view, 'apply', function (actionData) {
                    this.actionData = actionData;
                    this.additionalSetup();
                    setTimeout(function(){
                        this.reRender();
                    }.bind(this), 200);
                }, this);

            }.bind(this));
        },

        fetch: function () {
            this.actionData.type = this.type;
            return this.actionData;
        },

        renderFields: function () {
            var self = this;
            var $fieldContainer = this.$el.find('.field-list');

            if (this.actionData.fields) {

                var model = new Model();
                model.name = this.linkedEntityName || this.entityType;

                _.each(this.actionData.fields, function(row, fieldName){

                    model.set(row.attributes);
                    model.defs = {
                        "fields": {
                        },
                        "links": {
                        }
                    }
                    model.defs.fields[fieldName] = this.getMetadata().get('entityDefs.' + model.name + '.fields.' + fieldName);
                    model.defs.links[fieldName] = this.getMetadata().get('entityDefs.' + model.name + '.links.' + fieldName);

                    var metaFieldKey = 'entityDefs.' + model.name + '.fields.' + fieldName;

                    switch (row.subjectType) {
                        case "value":
                            var viewName =  this.getMetadata().get(metaFieldKey + '.view') || this.getFieldManager().getViewName(this.getMetadata().get(metaFieldKey + '.type'));

                            this.createView('subject', viewName, {
                                el: this.options.el + ' .field-container[data-field="' + fieldName + '"]',
                                model: model,
                                defs: {
                                    name: fieldName,
                                    params: {
                                    },
                                },
                                inlineEditDisabled: true,
                                readOnly: true
                            }, function (view) {
                                setTimeout(function(){
                                    view.render();
                                }, 200);
                            });
                            break;

                        case "field":
                        case "today":
                            var fieldType = this.getMetadata().get(metaFieldKey + '.type') || 'base';
                            var type = this.getMetadata().get('entityDefs.Workflow.fieldDefinitions.' + fieldType) || 'base';

                            this.createView('field-' + fieldName, 'Advanced:Workflow.FieldDefinitions.' + Espo.Utils.upperCaseFirst(type), {
                                el: this.options.el + ' .field-container[data-field="' + fieldName + '"]',
                                fieldData: row,
                                model: this.model,
                                field: fieldName,
                                entityType: this.entityType,
                                scope: model.name,
                                type: type,
                                fieldType: fieldType,
                                isNew: false,
                                readOnly: true
                            }, function (view) {
                                view.render();
                            });
                            break;
                    }

                }.bind(this))
            }
        },

        additionalSetup: function() {
            if (this.actionData.link) {
                this.linkedEntityName = this.actionData.link;
            }
        }

    });
});
