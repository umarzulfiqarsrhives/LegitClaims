

Espo.define('Advanced:Views.Workflow.ConditionFields.SubjectType', 'View', function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow.condition-fields.subject-type',

        list: ['value', 'field'],

        data: function () {
            return {
                value: this.options.value,
                list: this.list,
                readOnly: this.options.readOnly
            };
        },

    });
});

