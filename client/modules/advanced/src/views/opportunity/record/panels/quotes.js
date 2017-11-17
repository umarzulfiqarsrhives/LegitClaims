

Espo.define('Advanced:Views.Opportunity.Record.Panels.Quotes', 'Views.Record.Panels.Relationship', function (Dep) {

    return Dep.extend({

        actionCreateRelatedQuote: function () {
            this.notify('Loading...');
            $.ajax({
                url: 'Quote/action/getAttributesFromOpportunity',
                type: 'GET',
                data: {
                    opportunityId: this.model.id
                }
            }).done(function (attributes) {
                var viewName = this.getMetadata().get('clientDefs.Quote.modalViews.edit') || 'Modals.Edit';
                this.createView('quickCreate', 'Modals.Edit', {
                    scope: 'Quote',
                    relate: {
                        model: this.model,
                        link: 'opportunity',
                    },
                    attributes: attributes,
                }, function (view) {
                    view.render();
                    view.notify(false);
                    this.listenToOnce(view, 'after:save', function () {
                        this.collection.fetch();
                    }, this);
                }.bind(this));
            }.bind(this));
        },

    });
});

