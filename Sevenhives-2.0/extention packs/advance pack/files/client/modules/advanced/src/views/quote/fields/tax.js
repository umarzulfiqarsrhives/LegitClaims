

Espo.define('Advanced:Views.Quote.Fields.Tax', 'Views.Fields.Link', function (Dep) {

    return Dep.extend({

        select: function (model) {
            var taxRate = model.get('rate');

            if (taxRate !== null) {
                this.model.set('taxRate', taxRate, {ui: true});
            }
            Dep.prototype.select.call(this, model);
        },

    });
});

