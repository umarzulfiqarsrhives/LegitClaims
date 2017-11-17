

Espo.define('real-estate:views/real-estate-property/fields/request-type', 'views/fields/enum', function (Dep) {

    return Dep.extend({

        setup: function () {
            this.params.options = this.getMetadata().get('entityDefs.RealEstateRequest.fields.type.options');
            this.params.translation = 'RealEstateRequest.options.type';

            Dep.prototype.setup.call(this);

        },

    });

});
