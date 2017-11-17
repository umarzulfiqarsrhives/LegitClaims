

Espo.define('Advanced:Views.Modals.MailChimp.SelectRecords','Views.Modals.SelectRecords', function (Dep) {

    return Dep.extend({

        createButton: true,

        create: function () {
            var self = this;
            var currentView = "Advanced:MailChimp.";
            currentView += (this.scope == 'MailChimpCampaign' ) ? "CampaignCreate" : "ListCreate";
            this.notify('Loading...');
            this.createView('quickCreate', currentView, {
                scope: this.scope,
            }, function (view) {
                view.once('after:render', function () {
                    self.notify(false);
                });
                view.render();

                self.listenToOnce(view, 'leave', function () {
                    view.close();
                    self.close();
                });
                self.listenToOnce(view, 'after:save', function (model) {
                    view.close();
                    self.trigger('select', model);
                    setTimeout(function () {
                        self.close();
                    }, 10);

                }.bind(this));
            });
        },
    });
});
