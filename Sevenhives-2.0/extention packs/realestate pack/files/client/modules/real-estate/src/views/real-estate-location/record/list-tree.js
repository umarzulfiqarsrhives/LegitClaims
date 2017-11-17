

Espo.define('real-estate:views/real-estate-location/record/list-tree', 'views/record/list-tree', function (Dep) {

    return Dep.extend({

        itemViewName: 'real-estate:views/real-estate-location/record/list-tree-item',

        getCreateAttributes: function () {
            var attributes = {};
            if (this.model) {
                attributes.addressCity = this.model.get('addressCity');
                attributes.addressStreet = this.model.get('addressStreet');
                attributes.addressState = this.model.get('addressState');
                attributes.addressPostalCode = this.model.get('addressPostalCode');
                attributes.addressCountry = this.model.get('addressCountry');
            }
            return attributes;
        }

    });
});

