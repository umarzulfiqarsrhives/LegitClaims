

Espo.define('Advanced:Views.Workflow.Fields.EntityType', 'Views.Fields.Enum', function (Dep) {

    return Dep.extend({

        entityListToIgnore: [],

        setup: function () {
            var scopes = this.getMetadata().get('scopes');
            var entityListToIgnore = this.getMetadata().get('entityDefs.Workflow.entityListToIgnore') || [];
            this.params.options = Object.keys(scopes).filter(function (scope) {
                if (~entityListToIgnore.indexOf(scope)) {
                    return;
                }
                var defs = scopes[scope];
                return (defs.entity && (defs.tab || defs.object));
            }).sort(function (v1, v2) {
                 return this.translate(v1, 'scopeNamesPlural').localeCompare(this.translate(v2, 'scopeNamesPlural'));
            }.bind(this));

            this.params.options.unshift('');

            this.params.translation = 'Global.scopeNames';

            Dep.prototype.setup.call(this);
        },

    });

});
