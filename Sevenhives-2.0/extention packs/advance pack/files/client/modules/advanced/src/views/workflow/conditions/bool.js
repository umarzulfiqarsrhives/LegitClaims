

Espo.define('Advanced:Views.Workflow.Conditions.Bool', 'Advanced:Views.Workflow.Conditions.Base', function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow.conditions.base',

        defaultConditionData: {
            comparison: 'changed'
        },

        comparisonList: [
            'changed',
            'true',
            'false'
        ],

        data: function () {
            return _.extend({
            }, Dep.prototype.data.call(this));
        },

    });
});
