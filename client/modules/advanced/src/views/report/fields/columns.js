

Espo.define('Advanced:Views.Report.Fields.Columns', ['Views.Fields.MultiEnum', 'Advanced:Views.Report.Fields.GroupBy'], function (Dep, GroupBy) {

    return Dep.extend({

        setupOptions: function () {
            var entityType = this.model.get('entityType');

            var fields = this.getMetadata().get('entityDefs.' + entityType + '.fields') || {};

            var itemList = [];

            itemList.push('COUNT:id');

            Object.keys(fields).forEach(function (field) {
                if (fields[field].disabled) return;
                if (~['currencyConverted', 'int', 'float'].indexOf(fields[field].type)) {
                    itemList.push('SUM:' + field);
                    itemList.push('MAX:' + field);
                    itemList.push('MIN:' + field);
                    itemList.push('AVG:' + field);
                }
            }, this);

            this.params.options = itemList;
        },

        setupTranslatedOptions: function () {
            GroupBy.prototype.setupTranslatedOptions.call(this);

            this.params.options.forEach(function (item) {
                if (item == 'COUNT:id') {
                    this.translatedOptions[item] = this.translate('COUNT', 'functions', 'Report').toUpperCase();
                }
            }, this);
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.setupOptions();
            this.setupTranslatedOptions();
        }

    });

});

