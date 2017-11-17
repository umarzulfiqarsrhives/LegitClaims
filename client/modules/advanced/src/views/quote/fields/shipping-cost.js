

Espo.define('Advanced:Views.Quote.Fields.ShippingCost', 'Views.Fields.Currency', function (Dep) {

    return Dep.extend({

        editTemplate: 'fields.float.edit'

    });
});

