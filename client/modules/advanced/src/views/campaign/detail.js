

Espo.define('Advanced:Views.Campaign.Detail', 'Views.Detail', function (Dep) {

    return Dep.extend({

        relatedAttributeMap: {
            'massEmails': {
                'targetListsIds': 'targetListsIds',
                'targetListsNames': 'targetListsNames',
                'excludingTargetListsIds': 'excludingTargetListsIds',
                'excludingTargetListsNames': 'excludingTargetListsNames'
            },
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            var isDisabled = this.getMetadata().get('app.popupNotifications.mailChimpNotification.disabled') || false;
            if (!isDisabled && this.getAcl().check('MailChimp')) {
                var mailChimpButton = {
                        label: "MailChimp Sync",
                        action: "showModal",
                        data: {
                            view: "Advanced:Campaign.Modals.MailChimp",
                            name: "mailChimpButton"
                        }
                    };
                this.menu.buttons[this.menu.buttons.length] = mailChimpButton;
            }
        }
    });
});


