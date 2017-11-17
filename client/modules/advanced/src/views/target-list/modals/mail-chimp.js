

Espo.define('Advanced:Views.TargetList.Modals.MailChimp', 'Advanced:Views.Modals.MailChimpBase', function (Dep) {

    return Dep.extend({
    
        foreignEntity: 'TargetList',

        setup: function () {
            Dep.prototype.setup.call(this);
            this.header = this.translate('MailChimp List Sync', 'labels', 'TargetList');
        },

        childSetup: function () {
            this.model.defs = {
                fields: {
                    mailChimpList: {
                        type: 'base',
                        entity: 'MailChimpList',
                        view:'Advanced:MailChimp.Fields.MailChimpLink',
                    },
                    mcListGroup: {
                        type: 'base',
                        entity: 'MailChimpListGroup',
                        customTooltip: true,
						tooltipContentLabel: 'mailChimpGroup',
                        view:'Advanced:MailChimp.Fields.GroupLinkTree',
                    },
                },
            };
            this.createFieldView('base', 'Advanced:MailChimp.Fields.MailChimpLink', 'mailChimpList', false);
            this.createFieldView('base', 'Advanced:MailChimp.Fields.GroupLinkTree', 'mcListGroup', false,{listField:'mailChimpList'});
            
            this.fieldData.mailChimpList = {
                parentType: 'TargetList',
                parentId: this.options.model.id,
                parentName: this.options.model.get('name'),
                parentLabel: this.translate('Espo TargetList', 'labels','MailChimp'),
                label: this.translate('MailChimp TargetList', 'labels','MailChimp')
            };
            
            this.fieldData.mcListGroup = {
                parentType: 'TargetList',
                parentId: this.options.model.id,
                parentName: '',
                parentLabel: '',
                listField: 'mailChimpList',
                label: this.translate('MailChimp TargetListGroup', 'labels','MailChimp')
            };
            
            
        }, 
        
    });
});
