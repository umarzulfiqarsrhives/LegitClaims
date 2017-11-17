

Espo.define('Advanced:Views.Workflow.Record.Edit', 'Views.Record.Edit', function (Dep) {

    return Dep.extend({

        bottomView: 'Advanced:Workflow.Record.EditBottom',

        sideView: 'Advanced:Workflow.Record.EditSide',

        fetch: function () {
            var data = Dep.prototype.fetch.call(this);

            var conditions = {};
            var actions = [];

            var conditionsView = this.getView('bottom').getView('conditions');
            if (conditionsView) {
                conditions = conditionsView.fetch();
            }
            data.conditionsAny = conditions.any || [];
            data.conditionsAll = conditions.all || [];

            var actionsView = this.getView('bottom').getView('actions');
            if (actionsView) {
                actions = actionsView.fetch();
            }

            data.actions = actions;

            return data;
        },

    });
});

