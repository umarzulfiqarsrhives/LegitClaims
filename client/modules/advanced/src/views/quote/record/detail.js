

Espo.define('Advanced:Views.Quote.Record.Detail', 'Views.Record.Detail', function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);
            this.dropdownItemList.push({
                name: 'printPdf',
                label: 'Print to PDF'
            });

            this.dropdownItemList.push({
                name: 'composeEmail',
                label: 'Email PDF'
            });

            if (this.getAcl().checkModel(this.model, 'edit')) {
                this.dropdownItemList.push({
                    'label': 'Duplicate',
                    'name': 'duplicate'
                });
            }

        },

        actionPrintPdf: function () {
            this.createView('pdfTemplate', 'Modals.SelectTemplate', {
                entityType: this.model.name
            }, function (view) {
                view.render();

                this.listenToOnce(view, 'select', function (model) {
                    window.open('?entryPoint=pdf&entityType='+this.model.name+'&entityId='+this.model.id+'&templateId=' + model.id, '_blank');
                }, this);
            }.bind(this));
        },

        actionComposeEmail: function () {
            this.createView('pdfTemplate', 'views/modals/select-template', {
                entityType: this.model.name
            }, function (view) {
                view.render();
                this.listenToOnce(view, 'select', function (model) {
                    this.notify('Loading...');
                    this.ajaxPostRequest('Quote/action/getAttributesFromEmail', {
                        quoteId: this.model.id,
                        templateId: model.id
                    }).done(function (attributes) {
                        var viewName = this.getMetadata().get('clientDefs.Email.modalViews.compose') || 'views/modals/compose-email';
                        this.createView('composeEmail', viewName, {
                            attributes: attributes,
                        }, function (view) {
                            view.render(function () {
                                view.getView('edit').hideField('selectTemplate');
                            });
                            this.notify(false);
                        }, this);
                    }.bind(this));
                }, this);
            }, this);
        }

    });
});

