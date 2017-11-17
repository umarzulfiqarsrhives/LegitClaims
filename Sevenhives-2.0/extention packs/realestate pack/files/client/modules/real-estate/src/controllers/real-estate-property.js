

Espo.define('real-estate:controllers/real-estate-property', 'controllers/record', function (Dep) {

    return Dep.extend({

        listMatching: function (options) {
            var isReturn = options.isReturn;
            if (this.getRouter().backProcessed) {
                isReturn = true;
            }

            var key = this.name + 'listMatching';

            if (!isReturn) {
                var stored = this.getStoredMainView(key);
                if (stored) {
                    this.clearStoredMainView(key);
                }
            }


            this.main('real-estate:views/real-estate-property/list-matching', {
                id: options.id
            }, null, isReturn, key);

        },

    });

});
