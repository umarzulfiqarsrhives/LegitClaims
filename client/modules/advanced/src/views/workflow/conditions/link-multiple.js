

Espo.define('Advanced:Views.Workflow.Conditions.LinkMultiple', 'Advanced:Views.Workflow.Conditions.Base', function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow.conditions.base',

        defaultConditionData: {
            comparison: 'changed'
        },

        comparisonList: [
            'changed',
            'notEmpty',
            'isEmpty',
            'has'
        ],

        data: function () {
            return _.extend({
            }, Dep.prototype.data.call(this));
        },

        getSubjectInputViewName: function (subjectType) {
            return 'Advanced:Workflow.ConditionFields.Subjects.Link';
        },

    });
});
