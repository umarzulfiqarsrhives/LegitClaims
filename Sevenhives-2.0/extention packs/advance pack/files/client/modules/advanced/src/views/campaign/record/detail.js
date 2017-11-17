

Espo.define('Advanced:Views.Campaign.Record.Detail', 'Crm:Views.Campaign.Record.Detail', function (Dep) {

    return Dep.extend({

        getMailChimpButton: function () {
            return this.$el.parent().find(".header-buttons .btn[data-name='mailChimpButton']");
        },

        handleMailChimpButtonVisibility: function () {
            if (this.model.get('type') == 'Email' || this.model.get('type') == 'Newsletter') {
                this.getMailChimpButton().removeClass('hidden');
            } else {
                this.getMailChimpButton().addClass('hidden');
            }
        },

        handleMailChimpButtonStyle: function () {
            if (this.model.get('mailChimpCampaignId') == null) {
                this.getMailChimpButton().addClass('btn-danger');
            } else {
                this.getMailChimpButton().removeClass('btn-danger');
            }
        },

        afterRender: function () {
        	Dep.prototype.afterRender.call(this);
            this.handleMailChimpButtonVisibility();
            this.listenTo(this.model, 'sync', function () {
                this.handleMailChimpButtonVisibility();
            }, this);
            this.handleMailChimpButtonStyle();
            this.listenTo(this.model, 'sync', function () {
                this.handleMailChimpButtonStyle();
            }, this);
        },

    });
});
