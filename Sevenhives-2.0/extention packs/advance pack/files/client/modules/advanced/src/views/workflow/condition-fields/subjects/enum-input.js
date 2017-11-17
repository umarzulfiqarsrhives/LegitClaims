

Espo.define('Advanced:Views.Workflow.ConditionFields.Subjects.EnumInput', 'View', function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow.condition-fields.subjects.enum-input',

        data: function () {
            return {
                readOnly: this.options.readOnly
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.field = this.options.field;
            this.entityType = this.options.entityType;
            this.conditionData = this.options.conditionData || {};

            this.getModelFactory().create(this.entityType, function (model) {
                model.set(this.field, this.conditionData.value);

                var viewName = this.getMetadata().get('entityDefs.' + this.entityType + '.fields.' + this.field + '.view') || 'Fields.Enum';
                this.createView('field', viewName, {
                    el: this.options.el + ' .field-container',
                    mode: 'edit',
                    model: model,
                    readOnly: this.options.readOnly,
                    defs: {
                        name: this.options.field
                    }
                });
            }, this);
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);
            this.$el.find('select').addClass('input-sm');
        },

        fetch: function () {
            var view = this.getView('field');
            var data = view.fetch();
            return {
                value: data[this.field]
            };
        }

    });
});

