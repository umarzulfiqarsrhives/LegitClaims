

Espo.define('Advanced:Views.Workflow.Actions.CreateEntity', 'Advanced:Views.Workflow.Actions.Base', function (Dep) {

    return Dep.extend({

        type: 'createEntity',

        defaultActionData: {
            link: false,
            fieldList: [],
            fields: {},
        },

    });
});

