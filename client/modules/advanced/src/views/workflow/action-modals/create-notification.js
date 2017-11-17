

Espo.define('Advanced:Views.Workflow.ActionModals.CreateNotification', ['Advanced:Views.Workflow.ActionModals.Base', 'Model'], function (Dep, Model) {

    return Dep.extend({

        template: 'advanced:workflow.action-modals.create-notification',

        data: function () {
            return _.extend({
                recipientOptions: this.getRecipientOptions(),
                messageTemplateHelpText: this.translate('messageTemplateHelpText', 'messages', 'Workflow').replace(/(?:\r\n|\r|\n)/g, '<br />')
            }, Dep.prototype.data.call(this));
        },

        events: {
            'change [name="recipient"]': function (e) {
            this.actionData.recipient = e.currentTarget.value;
                this.handleRecipient();
            },
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);

            this.handleRecipient();
        },

        setup: function () {
            Dep.prototype.setup.call(this);


            var model = new Model();
            model.name = 'Workflow';

            model.set({
                recipient: this.actionData.recipient,
                messageTemplate: this.actionData.messageTemplate,
                usersIds: this.actionData.userIdList,
                usersNames: this.actionData.userNames,
                specifiedTeamsIds: this.actionData.specifiedTeamsIds,
                specifiedTeamsNames: this.actionData.specifiedTeamsNames
            });

            this.createView('messageTemplate', 'Fields.Text', {
                el: this.options.el + ' .field-messageTemplate',
                model: model,
                mode: 'edit',
                defs: {
                    name: 'messageTemplate',
                    params: {
                        required: false
                    }
                }
            });

            this.createView('users', 'Fields.LinkMultiple', {
                mode: 'edit',
                model: model,
                el: this.options.el + ' .field-users',
                foreignScope: 'User',
                defs: {
                    name: 'users'
                },
                readOnly: this.readOnly
            });

            this.createView('specifiedTeams', 'Fields.LinkMultiple', {
                el: this.options.el + ' .field-specifiedTeams',
                model: model,
                mode: 'edit',
                foreignScope: 'Team',
                defs: {
                    name: 'specifiedTeams'
                },
                readOnly: this.readOnly
            });
        },

        handleRecipient: function () {
            if (this.actionData.recipient == 'specifiedUsers') {
                this.$el.find('.cell-users').removeClass('hidden');
            } else {
                this.$el.find('.cell-users').addClass('hidden');
            }

            if (this.actionData.recipient == 'specifiedTeams') {
                this.$el.find('.cell-specifiedTeams').removeClass('hidden');
            } else {
                this.$el.find('.cell-specifiedTeams').addClass('hidden');
            }
        },

        getRecipientOptions: function () {
            var html = '';

            var value = this.actionData.recipient;

            var arr = ['specifiedUsers', 'currentUser', 'teamUsers', 'specifiedTeams', 'followers'];

            arr.forEach(function (item) {
                var label = this.translate(item, 'emailAddressOptions' , 'Workflow');
                html += '<option value="' + item + '" ' + (item === value ? 'selected' : '') + '>' + label + '</option>';
            }, this);

            var list = [];
            var fieldDefs = this.getMetadata().get('entityDefs.' + this.entityType + '.fields');

            var linkDefs = this.getMetadata().get('entityDefs.' + this.entityType + '.links');

            Object.keys(linkDefs).forEach(function (link) {
                var list = [];
                if (linkDefs[link].type == 'belongsTo' && linkDefs[link].entity == 'User') {
                    var foreignEntityType = linkDefs[link].entity;
                    var fieldDefs = this.getMetadata().get('entityDefs.' + foreignEntityType + '.fields');
                    var label = this.translate(link, 'links' , this.entityType);
                    html += '<option value="' + link + '" ' + (link === value ? 'selected' : '') + '>' + label + '</option>';

                }
            }, this);

            return html;
        },

        fetch: function () {
            this.actionData.messageTemplate = (this.getView('messageTemplate').fetch() || {}).messageTemplate;

            this.actionData.recipient = this.$el.find('[name="recipient"]').val();
            if (this.actionData.recipient === 'specifiedUsers') {
                var usersData = this.getView('users').fetch() || {};
                this.actionData.userIdList = usersData.usersIds;
                this.actionData.userNames = usersData.usersNames;
            } else {
                this.actionData.userIdList = [];
                this.actionData.userNames = {};
            }

            this.actionData.specifiedTeamsIds = [];
            this.actionData.specifiedTeamsNames = {};
            if (this.actionData.recipient === 'specifiedTeams') {
                var specifiedTeamsData = this.getView('specifiedTeams').fetch() || {};
                this.actionData.specifiedTeamsIds = specifiedTeamsData.specifiedTeamsIds;
                this.actionData.specifiedTeamsNames = specifiedTeamsData.specifiedTeamsNames;
            }

            return true;
        },


    });
});
