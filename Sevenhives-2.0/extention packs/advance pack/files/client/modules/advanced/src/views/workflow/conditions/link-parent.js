

Espo.define('Advanced:Views.Workflow.Conditions.LinkParent', 'Advanced:Views.Workflow.Conditions.Base', function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow.conditions.base',

        defaultConditionData: {
            comparison: 'changed'
        },

        comparisonList: [
            'changed',
            'notEmpty',
            'isEmpty',
            'equals',
            'notEquals'
        ],

        data: function () {
            return _.extend({
            }, Dep.prototype.data.call(this));
        },

        getSubjectInputViewName: function (subjectType) {
            return 'Advanced:Workflow.ConditionFields.Subjects.LinkParent';
        },

    });
});
