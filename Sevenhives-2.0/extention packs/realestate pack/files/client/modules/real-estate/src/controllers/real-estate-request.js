

Espo.define('real-estate:controllers/real-estate-request', 'controllers/record', function (Dep) {

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


            this.main('real-estate:views/real-estate-request/list-matching', {
                id: options.id
            }, null, isReturn, key);

        },

        getSettingsModel: function () {
            var model = this.getConfig().clone();
            model.defs = this.getConfig().defs;

            return model;
        },

        settings: function () {
            var model = this.getSettingsModel();

            model.once('sync', function () {
                model.id = '1';
                this.main('views/settings/edit', {
                    model: model,
                    headerTemplate: 'real-estate:admin/header-real-estate-settings',
                    recordView: 'real-estate:views/admin/real-estate-settings'
                });
            }, this);
            model.fetch();
        }
    });

});
