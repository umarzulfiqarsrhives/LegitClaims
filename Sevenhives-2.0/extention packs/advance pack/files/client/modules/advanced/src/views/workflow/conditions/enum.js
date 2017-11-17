

Espo.define('Advanced:Views.Workflow.Conditions.Enum', 'Advanced:Views.Workflow.Conditions.Base', function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow.conditions.enum',

        defaultConditionData: {
            comparison: 'equals',
            subjectType: 'value'
        },

        comparisonList: [
            'equals',
            'wasEqual',
            'notEquals',
            'wasNotEqual',
            'changed',
            'notEmpty'
        ],

        data: function () {
            return _.extend({
            }, Dep.prototype.data.call(this));
        },

        getSubjectInputViewName: function (subjectType) {
            return 'Advanced:Workflow.ConditionFields.Subjects.EnumInput';
        },

    });
});
