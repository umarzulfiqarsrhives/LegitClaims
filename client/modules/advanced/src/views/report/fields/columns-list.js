

Espo.define('Advanced:Views.Report.Fields.ColumnsList', 'Views.Fields.MultiEnum', function (Dep) {

    return Dep.extend({

        setupOptions: function () {
            var entityType = this.model.get('entityType');

            var fields = this.getMetadata().get('entityDefs.' + entityType + '.fields') || {};

            var itemList = [];

            Object.keys(fields).forEach(function (field) {
                if (fields[field].disabled) return;
                if (fields[field].type == 'linkMultiple') return;

                itemList.push(field);
            }, this);

            this.params.options = itemList;
        },

        setupTranslatedOptions: function () {
            var entityType = this.model.get('entityType');

            this.translatedOptions = {};

            this.params.options.forEach(function (item) {
                this.translatedOptions[item] = this.translate(item, 'fields', entityType);
            }, this);
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.setupOptions();
            this.setupTranslatedOptions();
        }

    });

});

