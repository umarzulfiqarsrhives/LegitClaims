

Espo.define('Advanced:Views.Campaign.Modals.MailChimpFooter', 'View', function (Dep) {

    return Dep.extend({
    
        template: 'advanced:modals.mail-chimp-campaign-footer',

        data: function () {
            var status = this.model.get('mailChimpCampaignStatus');
            var isActive = status != 'sent' && status != 'sending' && this.model.get('mailChimpCampaignWebId');
            return _.extend({
                webId: this.model.get('mailChimpCampaignWebId'),
                inactive: !isActive
            }, this);
        },
	    
	    setup: function () {
	        Dep.prototype.setup.call(this);
            this.listenTo(this.model, 'change:mailChimpCampaignWebId', function () {
		        this.reRender();
            }, this);
        },
    });
});

