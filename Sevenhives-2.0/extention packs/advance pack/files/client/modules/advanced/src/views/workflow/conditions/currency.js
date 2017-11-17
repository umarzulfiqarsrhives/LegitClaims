

Espo.define('Advanced:Views.Workflow.Conditions.Currency', 'Advanced:Views.Workflow.Conditions.Float', function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow.conditions.base',

        getSubjectInputViewName: function (subjectType) {
            return 'Advanced:Workflow.ConditionFields.Subjects.TextInputCurrency';
        },

    });
});
