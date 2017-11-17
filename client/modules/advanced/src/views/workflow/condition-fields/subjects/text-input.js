

Espo.define('Advanced:Views.Workflow.ConditionFields.Subjects.TextInput', 'View', function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow.condition-fields.subjects.text-input',

        data: function () {
            return {
                value: this.options.value,
                readOnly: this.options.readOnly
            };
        },

    });
});

