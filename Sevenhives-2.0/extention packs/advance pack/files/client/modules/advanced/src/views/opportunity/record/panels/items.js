

Espo.define('Advanced:Views.Opportunity.Record.Panels.Items', 'Views.Record.Panels.Bottom', function (Dep) {

    return Dep.extend({

        template: 'advanced:opportunity.record.panels.items',

        setup: function () {
            Dep.prototype.setup.call(this);

            this.createView('itemList', 'Advanced:Opportunity.Fields.ItemList', {
                model: this.model,
                el: this.options.el + ' .field-itemList',
                defs: {
                    name: 'itemList'
                },
                mode: this.mode
            });
        },

        getFields: function () {
            var fields = {};
            fields.itemList = this.getView('itemList');
            return fields;
        },

    });
});

