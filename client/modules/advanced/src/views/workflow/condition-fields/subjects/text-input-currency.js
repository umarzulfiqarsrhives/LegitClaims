

Espo.define('Advanced:Views.Workflow.ConditionFields.Subjects.TextInputCurrency', 'View', function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow.condition-fields.subjects.text-input-currency',

        data: function () {
            return {
                value: this.options.value,
                readOnly: this.options.readOnly,
                currency: this.getConfig().get('defaultCurrency')
            };
        },

    });
});

