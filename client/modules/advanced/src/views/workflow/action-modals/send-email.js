

Espo.define('Advanced:Views.Workflow.ActionModals.SendEmail', ['Advanced:Views.Workflow.ActionModals.Base', 'Model'], function (Dep, Model) {

    return Dep.extend({

        template: 'advanced:workflow.action-modals.send-email',

        data: function () {
            return _.extend({
                fromOptions: this.getFromOptions(),
                toOptions: this.getToOptions(),
                fromEmailValue: this.actionData.fromEmail,
                toEmailValue: this.actionData.toEmail,
            }, Dep.prototype.data.call(this));
        },

        events: {
            'change [name="from"]': function (e) {
                this.actionData.from = e.currentTarget.value;
                this.handleFrom();
            },
            'change [name="to"]': function (e) {
            this.actionData.to = e.currentTarget.value;
                this.handleTo();
            },
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);

            this.handleFrom();
            this.handleTo();
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.createView('executionTime', 'Advanced:Workflow.ActionFields.ExecutionTime', {
                el: this.options.el + ' .execution-time-container',
                executionData: this.actionData.execution || {},
                entityType: this.entityType
            });

            var model = new Model();

            model.name = 'Workflow';

            model.set({
                emailTemplateId: this.actionData.emailTemplateId,
                emailTemplateName: this.actionData.emailTemplateName
            });

            if (this.actionData.toSpecifiedTeamsIds) {
                model.set({
                    toSpecifiedTeamsIds: this.actionData.toSpecifiedTeamsIds,
                    toSpecifiedTeamsNames: this.actionData.toSpecifiedTeamsNames
                });
            }

            this.createView('emailTemplate', 'Fields.Link', {
                el: this.options.el + ' .field-emailTemplate',
                model: model,
                mode: 'edit',
                foreignScope: 'EmailTemplate',
                defs: {
                    name: 'emailTemplate',
                    params: {
                        required: true
                    }
                }
            });

            this.createView('toSpecifiedTeams', 'Fields.LinkMultiple', {
                el: this.options.el + ' .to-teams-container .field-toSpecifiedTeams',
                model: model,
                mode: 'edit',
                foreignScope: 'Team',
                defs: {
                    name: 'toSpecifiedTeams'
                }
            });
        },

        handleFrom: function () {
            var value = this.actionData.from;

            if (value == 'specifiedEmailAddress') {
                this.$el.find('.from-email-container').removeClass('hidden');
            } else {
                this.$el.find('.from-email-container').addClass('hidden');
            }
        },

        handleTo: function () {
            var value = this.actionData.to;

            if (value == 'specifiedEmailAddress') {
                this.$el.find('.to-email-container').removeClass('hidden');
            } else {
                this.$el.find('.to-email-container').addClass('hidden');
            }

            if (value == 'specifiedTeams') {
                this.$el.find('.to-teams-container').removeClass('hidden');
            } else {
                this.$el.find('.to-teams-container').addClass('hidden');
            }
        },

        getFromOptions: function () {
            var html = '';

            var value = this.actionData.from;

            var arr = ['currentUser', 'specifiedEmailAddress', 'assignedUser'];

            arr.forEach(function (item) {
                var label = this.translate(item, 'emailAddressOptions' , 'Workflow');
                html += '<option value="' + item + '" ' + (item === value ? 'selected' : '') + '>' + label + '</option>';
            }, this);

            return html;
        },

        getToOptions: function () {
            var html = '';

            var value = this.actionData.to;

            var arr = ['currentUser', 'teamUsers', 'specifiedTeams', 'followers', 'specifiedEmailAddress'];

            arr.forEach(function (item) {
                var label = this.translate(item, 'emailAddressOptions' , 'Workflow');
                html += '<option value="' + item + '" ' + (item === value ? 'selected' : '') + '>' + label + '</option>';
            }, this);


            var fieldTypeList = ['email'];

            var list = [];
            var fieldDefs = this.getMetadata().get('entityDefs.' + this.entityType + '.fields');

            if ('emailAddress' in fieldDefs) {
                var item = 'targetEntity';
                var label = this.translate(item, 'emailAddressOptions' , 'Workflow');
                html += '<option value="' + item + '" ' + (item === value ? 'selected' : '') + '>' + label + '</option>';
            }

            var linkDefs = this.getMetadata().get('entityDefs.' + this.entityType + '.links');

            Object.keys(linkDefs).forEach(function (link) {
                var list = [];
                if (linkDefs[link].type == 'belongsTo') {
                    var foreignEntityType = linkDefs[link].entity;
                    if (!foreignEntityType) {
                        return;
                    }
                    var fieldDefs = this.getMetadata().get('entityDefs.' + foreignEntityType + '.fields');
                    if ('emailAddress' in fieldDefs) {
                        var label = this.translate(link, 'links' , this.entityType);
                        html += '<option value="' + link + '" ' + (link === value ? 'selected' : '') + '>' + label + '</option>';
                    }
                }
            }, this);


            return html;
        },

        fetch: function () {
            var emailTemplateView = this.getView('emailTemplate');

            emailTemplateView.fetchToModel();

            if (emailTemplateView.validate()) {
                return;
            }

            var o = emailTemplateView.fetch();

            this.actionData.emailTemplateId = o.emailTemplateId;
            this.actionData.emailTemplateName = o.emailTemplateName;

            this.actionData.from = this.$el.find('[name="from"]').val();
            this.actionData.to = this.$el.find('[name="to"]').val();

            var toSpecifiedTeams = this.getView('toSpecifiedTeams');
            toSpecifiedTeams.fetchToModel();
            var toSpecifiedTeamsVal = toSpecifiedTeams.fetch();
            this.actionData.toSpecifiedTeamsIds = toSpecifiedTeamsVal.toSpecifiedTeamsIds;
            this.actionData.toSpecifiedTeamsNames = toSpecifiedTeamsVal.toSpecifiedTeamsNames;

            this.actionData.fromEmail = this.$el.find('[name="fromEmail"]').val();
            this.actionData.toEmail = this.$el.find('[name="toEmail"]').val();

            this.actionData.execution = this.actionData.execution || {};

            this.actionData.execution.type = this.$el.find('[name="executionType"]').val();

            if (this.actionData.execution.type != 'immediately') {
                this.actionData.execution.field = this.$el.find('[name="executionField"]').val();
                this.actionData.execution.shiftDays = this.$el.find('[name="shiftDays"]').val();

                if (this.$el.find('[name="shiftDaysOperator"]').val() == 'minus') {
                    this.actionData.execution.shiftDays = (-1) * this.actionData.execution.shiftDays;
                }
            }

            return true;
        },


    });
});
