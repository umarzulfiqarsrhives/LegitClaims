

Espo.define('real-estate:views/real-estate-request/fields/matching-property', 'views/fields/link', function (Dep) {

    return Dep.extend({

        foreignScope: 'RealEstateProperty',

        setupSearch: function () {
            Dep.prototype.setupSearch.call(this);
            this.searchParams.typeOptions = ['is'];
        },

    });

});
