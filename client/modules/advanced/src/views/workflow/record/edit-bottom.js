

Espo.define('Advanced:Views.Workflow.Record.EditBottom', 'View', function (Dep) {

    return Dep.extend({

        editMode: true,

        template: 'advanced:workflow.record.edit-bottom',

        setup: function () {

        },

        afterRender: function () {
            if (!this.model.isNew()) {
                this.showConditions();
                this.showActions();
            } else {
                if (this.model.get('entityType')) {
                    this.showConditions();
                    this.showActions();
                }
            }



            this.listenTo(this.model, 'change:entityType', function (model) {
                var entityType = model.get('entityType');

                model.set('conditionsAny', []);
                model.set('conditionsAll', []);
                model.set('actions', []);

                if (entityType) {
                    this.showConditions();
                    this.showActions();
                } else {
                    this.hideConditions();
                    this.hideActions();
                }
            }.bind(this));
        },

        showConditions: function () {
            this.$el.find('.panel-conditions').removeClass('hidden');
            this.createView('conditions', 'Advanced:Workflow.Record.Conditions', {
                model: this.model,
                el: this.options.el + ' .conditions-container',
                readOnly: !this.editMode
            }, function (view) {
                view.render();
            });
        },

        showActions: function () {
            this.$el.find('.panel-actions').removeClass('hidden');
            this.createView('actions', 'Advanced:Workflow.Record.Actions', {
                model: this.model,
                el: this.options.el + ' .actions-container',
                readOnly: !this.editMode
            }, function (view) {
                view.render();
            });
        },

        hideConditions: function () {
            this.$el.find('.panel-conditions').addClass('hidden');
            var view = this.getView('conditions');
            if (view) {
                view.remove();
            }
        },

        hideActions: function () {
            this.$el.find('.panel-actions').addClass('hidden');
            var view = this.getView('actions');
            if (view) {
                view.remove();
            }
        },

        getFields: function () {
        },

        getFieldViews: function () {
            return {};
        },
    });
});


