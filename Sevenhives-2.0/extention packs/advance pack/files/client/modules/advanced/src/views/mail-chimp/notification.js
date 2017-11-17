

Espo.define('Advanced:Views.MailChimp.Notification', 'Views.PopupNotification', function (Dep) {

    return Dep.extend({

        type: 'event',

        style: 'primary',

        template: 'advanced:mail-chimp.notification',

        closeButton: true,

        setup: function () {
            this.wait(true);

            if (this.notificationData.entityType) {
                this.getModelFactory().create(this.notificationData.entityType, function (model) {

                    model.set('lastSynced', this.notificationData.lastSynced);

                    this.createView('lastSynced', 'Fields.Datetime', {
                        model: model,
                        mode: 'detail',
                        el: this.options.el + ' .field-lastSynced',
                        defs: {
                            name: 'lastSynced'
                        },
                        readOnly: true
                    });

                    this.wait(false);
                }, this);
            }
        },

        data: function () {
            return _.extend({
                header: this.translate(this.notificationData.entityType, 'scopeNames')
            }, Dep.prototype.data.call(this));
        },
    });
});

