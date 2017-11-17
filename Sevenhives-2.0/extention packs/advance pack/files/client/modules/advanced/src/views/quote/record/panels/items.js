

Espo.define('Advanced:Views.Quote.Record.Panels.Items', 'Views.Record.Panels.Bottom', function (Dep) {

    return Dep.extend({

        template: 'advanced:quote.record.panels.items',

        setup: function () {
            Dep.prototype.setup.call(this);

            this.createView('itemList', 'Advanced:Quote.Fields.ItemList', {
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

