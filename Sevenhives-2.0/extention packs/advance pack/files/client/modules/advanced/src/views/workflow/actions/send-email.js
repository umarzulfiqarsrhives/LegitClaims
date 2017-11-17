

Espo.define('Advanced:Views.Workflow.Actions.SendEmail', ['Advanced:Views.Workflow.Actions.Base', 'Model'], function (Dep, Model) {

    return Dep.extend({

        template: 'advanced:workflow.actions.send-email',

        type: 'sendEmail',

        defaultActionData: {
            execution: {
                type: 'immediately',
                field: false,
                shiftDays: 0,
            },
            from: 'currentUser',
            to: ''
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.createView('executionTime', 'Advanced:Workflow.ActionFields.ExecutionTime', {
                el: this.options.el + ' .execution-time-container',
                executionData: this.actionData.execution || {},
                entityType: this.entityType,
                readOnly: true
            });

            var model = new Model();
            model.name = 'Workflow';
            model.set({
                emailTemplateId: this.actionData.emailTemplateId,
                emailTemplateName: this.actionData.emailTemplateName
            });

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
                },
                readOnly: true
            });

            this.createView('toSpecifiedTeams', 'Fields.LinkMultiple', {
                el: this.options.el + ' .to-teams-container .field-toSpecifiedTeams',
                model: model,
                mode: 'edit',
                foreignScope: 'Team',
                defs: {
                    name: 'toSpecifiedTeams'
                },
                readOnly: true
            });
        },

        render: function (callback) {
            this.getView('executionTime').reRender();

            var emailTemplateView = this.getView('emailTemplate');
            emailTemplateView.model.set({
                emailTemplateId: this.actionData.emailTemplateId,
                emailTemplateName: this.actionData.emailTemplateName
            });
            emailTemplateView.reRender();

            if (this.actionData.toSpecifiedTeamsIds) {
                var toSpecifiedTeamsView = this.getView('toSpecifiedTeams');
                toSpecifiedTeamsView.model.set({
                    toSpecifiedTeamsIds: this.actionData.toSpecifiedTeamsIds,
                    toSpecifiedTeamsNames: this.actionData.toSpecifiedTeamsNames
                });
                toSpecifiedTeamsView.reRender();
            }

            //translate To and From option
            if (this.actionData.from) {
                this.actionData.fromLabel = this.translateEmailOption(this.actionData.from);
            }
            if (this.actionData.to) {
                this.actionData.toLabel = this.translateEmailOption(this.actionData.to);
            }

            Dep.prototype.render.call(this, callback);
        },

        renderFields: function () {
        },

        translateEmailOption: function (value) {
            var linkDefs = this.getMetadata().get('entityDefs.' + this.entityType + '.links.' + value);
            if (linkDefs) {
                return this.translate(value, 'links' , this.entityType);
            }

            return this.translate(value, 'emailAddressOptions', 'Workflow');
        }

    });
});

