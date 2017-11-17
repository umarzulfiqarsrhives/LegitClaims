

Espo.define('Advanced:Views.Quote.Fields.Account', 'Views.Fields.Link', function (Dep) {

    return Dep.extend({

        select: function (model) {
            Dep.prototype.select.call(this, model);

            this.model.set('billingAddressStreet', model.get('billingAddressStreet'));
            this.model.set('billingAddressCity', model.get('billingAddressCity'));
            this.model.set('billingAddressState', model.get('billingAddressState'));
            this.model.set('billingAddressCountry', model.get('billingAddressCountry'));
            this.model.set('billingAddressPostalCode', model.get('billingAddressPostalCode'));

            this.model.set('shippingAddressStreet', model.get('shippingAddressStreet'));
            this.model.set('shippingAddressCity', model.get('shippingAddressCity'));
            this.model.set('shippingAddressState', model.get('shippingAddressState'));
            this.model.set('shippingAddressCountry', model.get('shippingAddressCountry'));
            this.model.set('shippingAddressPostalCode', model.get('shippingAddressPostalCode'));
        }

    });
});

