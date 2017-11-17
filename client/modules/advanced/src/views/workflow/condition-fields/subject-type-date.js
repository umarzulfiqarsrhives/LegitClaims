

Espo.define('Advanced:Views.Workflow.ConditionFields.SubjectTypeDate', 'View', function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow.condition-fields.subject-type',

        list: ['today', 'field'],

        data: function () {
            return {
                value: this.options.value,
                list: this.list,
                readOnly: this.options.readOnly
            };
        },

    });
});

