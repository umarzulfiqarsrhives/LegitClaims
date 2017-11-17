

Espo.define('Advanced:Views.Workflow.ConditionFields.Subjects.LinkParent', 'View', function (Dep) {

    return Dep.extend({

        _template: '<div class="field-container" style="display: inline-block">{{{field}}}</div>',

        data: function () {
            return {
                list: this.getMetadata().get('entityDefs.' + this.options.entityType + '.fields.' + this.options.field + '.options') || [],
                field: this.options.field,
                value: this.options.value,
                entityType: this.options.entityType,
                readOnly: this.options.readOnly
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.field = this.options.field;
            this.entityType = this.options.entityType;
            this.conditionData = this.options.conditionData || {};

            this.idName = this.field + 'Id';
            this.nameName = this.field + 'Name';
            this.typeName = this.field + 'Type';

            this.getModelFactory().create(this.entityType, function (model) {
                model.set(this.idName, this.conditionData.value);
                model.set(this.nameName, this.conditionData.valueName);
                model.set(this.typeName, this.conditionData.valueType);

                this.createView('field', 'Fields.LinkParent', {
                    el: this.options.el + ' .field-container',
                    mode: 'edit',
                    model: model,
                    readOnly: this.options.readOnly,
                    readOnlyDisabled: !this.options.readOnly,
                    inlineEditDisabled: this.options.readOnly,
                    defs: {
                        name: this.options.field
                    }
                });
            }, this);
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);
            this.$el.find('input').addClass('input-sm');
            this.$el.find('.btn').addClass('btn-sm');
        },

        fetch: function () {
            var view = this.getView('field');
            var data = view.fetch();
            var fieldValueMap = {};
            fieldValueMap[this.idName] = data[this.idName];
            fieldValueMap[this.typeName] = data[this.typeName];

            return {
                value: data[this.idName],
                valueName: data[this.nameName],
                valueType: data[this.typeName],
                fieldValueMap: fieldValueMap
            };
        }

    });
});

