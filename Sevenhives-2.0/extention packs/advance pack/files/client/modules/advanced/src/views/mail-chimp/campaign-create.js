
Espo.define('Advanced:Views.MailChimp.CampaignCreate', 'Views.Modals.Edit', function (Dep) {

    return Dep.extend({


        layoutName: 'detailSmall',

        fields :{
            name: {
                type: "varchar",
                required: true
            },
            subject: {
                type: "varchar",
                required: true
            },
            list: {
                type: "base",
			    entity: "MailChimpList",
			    view: "Advanced:MailChimp.Fields.MailChimpLink",
                required: true
	        },
            type: {
                type: "enum",
                options: ["regular", "plaintext"],
                required: true
            },
            content: {
                type: "text",
                required: true
            },
            fromEmail: {
                type: "varchar",
                required: true
            },
            fromName: {
                type: "varchar",
                required: true
            },
            toName: {
                type: "enum",
                options: ["*|FNAME|*", "*|LNAME|*","*|FNAME|* *|LNAME|*"],
                required: true
            }
        },

        setup: function () {

            var self = this;

            this.buttonList = [];

            if ('saveButton' in this.options) {
                this.saveButton = this.options.saveButton;
            }


            this.buttonList.push({
                name: 'save',
                text: this.getLanguage().translate('Save'),
                style: 'primary',
                onClick: function (dialog) {
                    var editView = this.getView('edit');

                    var model = editView.model;
                    editView.once('after:save', function () {
                        this.trigger('after:save', model);
                        dialog.close();
                    }, this);

                    var $buttons = dialog.$el.find('.modal-footer button');
                    $buttons.addClass('disabled');

                    editView.once('cancel:save', function () {
                        $buttons.removeClass('disabled');
                    }, this);

                    editView.save();

                }.bind(this)
            });


            if ('fullFormButton' in this.options) {
                this.fullFormButton = this.options.fullFormButton;
            }

            this.buttonList.push({
                name: 'cancel',
                text: this.getLanguage().translate('Cancel'),
                onClick: function (dialog) {
                    dialog.close();
                }
            });

            this.scope = this.scope || this.options.scope;
            this.id = this.options.id;

            if (!this.id) {
                this.header = this.getLanguage().translate('Create');
            } else {
                this.header = this.getLanguage().translate('Edit');
            }
            this.header += ' ' + this.getLanguage().translate(this.scope, 'scopeNames');

            this.waitForView('edit');

            this.getModelFactory().create(this.scope, function (model) {
                model.defs.fields = this.fields;
                this.model = model;
                if (this.id) {
                    model.id = this.id;
                    model.once('sync', function () {
                        this.createEdit(model);
                    }, this);
                    model.fetch();
                } else {
                    model.populateDefaults();
                    if (this.options.relate) {
                        model.setRelate(this.options.relate);
                    }
                    if (this.options.attributes) {
                        model.set(this.options.attributes);
                    }
                    this.createEdit(model);
                }
            }.bind(this));
        },

        createEdit: function (model, callback) {
            var viewName = this.editViewName || this.getMetadata().get('clientDefs.' + model.name + '.recordViews.editQuick') || 'Record.EditSmall'; 
            var options = {
                model: model,
                el: this.containerSelector + ' .edit-container',
                type: 'editSmall',
                layoutName: this.layoutName || 'detailSmall',
                columnCount: this.columnCount,
                sideView: false,
                isWide: true,
                columnCount: 2,
                buttonsPosition: false,
                exit: function () {},
            };
            this.createView('edit', viewName, options, callback);
        },

    });
});
