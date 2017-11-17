

Espo.define('real-estate:views/real-estate-request/fields/property-type', 'views/fields/enum', function (Dep) {

    return Dep.extend({

        setup: function () {
            this.params.options = this.getMetadata().get('entityDefs.RealEstateProperty.fields.type.options');
            this.params.translation = 'RealEstateProperty.options.type';

            Dep.prototype.setup.call(this);

        },

    });

});
