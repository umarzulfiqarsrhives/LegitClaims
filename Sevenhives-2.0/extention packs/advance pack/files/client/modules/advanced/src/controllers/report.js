

Espo.define('Advanced:Controllers.Report', 'Controllers.Record', function (Dep) {

    return Dep.extend({

        create: function (options) {
            options = options || {};

            options.attributes = options.attributes || {};

            if ('type' in options) {
                options.attributes.type = options.type;
            }
            if ('entityType' in options) {
                options.attributes.entityType = options.entityType;
            }

            Dep.prototype.create.call(this, options);
        },

    });

});