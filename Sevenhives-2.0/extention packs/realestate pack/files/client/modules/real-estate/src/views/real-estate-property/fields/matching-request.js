

Espo.define('real-estate:views/real-estate-property/fields/matching-request', 'views/fields/link', function (Dep) {

    return Dep.extend({

        foreignScope: 'RealEstateRequest',

        setupSearch: function () {
            Dep.prototype.setupSearch.call(this);
            this.searchParams.typeOptions = ['is'];
        },

    });

});
