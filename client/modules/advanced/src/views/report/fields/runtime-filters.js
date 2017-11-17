

Espo.define('Advanced:Views.Report.Fields.RuntimeFilters', ['Views.Fields.MultiEnum', 'Advanced:Views.Report.Fields.Filters'], function (Dep, Filters) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            this.params.options = Filters.prototype.getFilterList.call(this);

            Filters.prototype.setupTranslatedOptions.call(this);
        },

    });

});

