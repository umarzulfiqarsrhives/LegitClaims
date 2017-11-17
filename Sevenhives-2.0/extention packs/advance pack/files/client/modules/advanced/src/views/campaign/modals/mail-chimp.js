

Espo.define('Advanced:Views.Campaign.Modals.MailChimp', 'Advanced:Views.Modals.MailChimpBase', function (Dep) {

    return Dep.extend({
    
        foreignEntity: 'Campaign',
        hasFooter: true,
        footerView: 'Advanced:Campaign.Modals.MailChimpFooter',
        
        setup: function () {
            this.header = this.translate('MailChimp Campaign Sync', 'labels','Campaign');
            Dep.prototype.setup.call(this);
	    },
	    
	    childSetup: function () {
            this.model.defs = {
				    fields: {
					    mailChimpCampaign: {
						    type: 'base',
						    entity: 'MailChimpCampaign',
						    view:'Advanced:MailChimp.Fields.MailChimpLink',
					    },
				    },
			    };
			    this.createFieldView('base', 'Advanced:MailChimp.Fields.MailChimpCampaignLink', 'mailChimpCampaign', false);
			    this.fieldData.mailChimpCampaign = {
			        parentType: 'Campaign',
			        parentId: this.options.model.id,
			        parentName: this.options.model.get('name'),
			        parentLabel: this.translate('Espo Campaign', 'labels','MailChimp'),
			        label: this.translate('MailChimp Campaign', 'labels','MailChimp')  
			    };
			    
			    var targetListsIds = this.model.get('targetListsIds');
			    for(targetListIdIdx in targetListsIds) {
			        
			        targetListId = targetListsIds[targetListIdIdx];
			        
			        this.model.defs.fields[targetListId+'_mailChimpList'] = {
						type: 'base',
						entity: 'MailChimpList',
						view:'Advanced:MailChimp.Fields.MailChimpLink',
					};
					this.model.defs.fields[targetListId+'_mcListGroup'] = {
						type: 'base',
						entity: 'MailChimpListGroup',
						customTooltip: true,
						tooltipContentLabel: 'mailChimpGroup',
						view: 'Advanced:MailChimp.Fields.GroupLinkTree',
					};
                    this.fieldData[targetListId+'_mailChimpList'] = {
			            parentType: 'TargetList',
			            parentId: targetListId,
			            parentName: this.model.get(targetListId+'_name'),
			            parentLabel: this.translate('Espo TargetList', 'labels','MailChimp'),
			            label: this.translate('MailChimp TargetList', 'labels','MailChimp')
			        };
			        
			        this.fieldData[targetListId+'_mcListGroup'] = {
			            parentType: 'TargetList',
                        parentId: targetListId,
                        parentName: '',
                        parentLabel: '',
                        listField: targetListId+'_mailChimpList',
                        label: this.translate('MailChimp TargetListGroup', 'labels','MailChimp')
			        };
			        
					this.createFieldView('base', 'Advanced:MailChimp.Fields.MailChimpLink', targetListId+'_mailChimpList', false);
					this.createFieldView('base', 'Advanced:MailChimp.Fields.GroupLinkTree', targetListId+'_mcListGroup', false, {listField: targetListId+'_mailChimpList'});
			    }
        },

    });
});

