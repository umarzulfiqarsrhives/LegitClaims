

Espo.define('real-estate:views/real-estate-property/fields/location', 'views/fields/link-category-tree', function (Dep) {

    return Dep.extend({

        select: function (model) {
            Dep.prototype.select.call(this, model);

            this.model.set('addressStreet', model.get('addressStreet'));
            this.model.set('addressCity', model.get('addressCity'));
            this.model.set('addressState', model.get('addressState'));
            this.model.set('addressCountry', model.get('addressCountry'));
            this.model.set('addressPostalCode', model.get('addressPostalCode'));
        }

    });

});
