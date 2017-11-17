

Espo.define('Advanced:Views.Workflow.ActionModals.Base', 'Views.Modal', function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow.action-modals.base',

        data: function () {
            return {};
        },

        setup: function () {
            this.actionData = this.options.actionData || {};
            this.actionType = this.options.actionType;
            this.entityType = this.options.entityType;

            this.buttonList = [
                {
                    name: 'apply',
                    label: 'Apply',
                    style: 'primary',
                    onClick: function (dialog) {
                        if (this.fetch()) {
                            this.trigger('apply', this.actionData);
                            this.close();
                        }
                    }.bind(this),
                },
                {
                    name: 'cancel',
                    label: 'Cancel',
                    onClick: function (dialog) {
                        this.trigger('cancel');
                        dialog.close();
                    }.bind(this)
                }
            ];

            this.header = this.translate(this.actionType, 'actionTypes', 'Workflow');
        },

    });
});
