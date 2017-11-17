

Espo.define('real-estate:views/real-estate-property/fields/contacts', 'views/fields/link-multiple-with-role', function (Dep) {

    return Dep.extend({

        getSelectFilters: function () {
            if (this.model.get('accountId')) {
                return {
                    'account': {
                        type: 'equals',
                        field: 'accountId',
                        value: this.model.get('accountId'),
                        valueName: this.model.get('accountName'),
                    }
                };
            }
        },

    });

});
