

Espo.define('Advanced:Views.Report.List', 'Views.List', function (Dep) {

    return Dep.extend({

        createButton: false,

        actionCreate: function (data) {
            this.createView('createModal', 'Advanced:Report.Modals.Create', {}, function (view) {
                view.render();

                this.listenToOnce(view, 'create', function (data) {
                    view.close();
                    this.getRouter().dispatch('Report', 'create', {
                        entityType: data.entityType,
                        type: data.type
                    });
                    this.getRouter().navigate('#Report/create/entityType=' + data.entityType + '&type=' + data.type, {trigger: false});
                }, this);

            }.bind(this));

        }

    });
});
