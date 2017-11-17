

Espo.define('Advanced:Views.Report.Fields.OrderBy', 'Views.Fields.MultiEnum', function (Dep) {

    return Dep.extend({

        setupOptions: function () {
            var entityType = this.model.get('entityType');
            var itemList = [];

            var groupByItemList = this.model.get('groupBy') || [];
            groupByItemList.forEach(function (item) {
                var scope = entityType;
                var field = item;
                var link = null;
                if (~field.indexOf(':')) {
                    field = item.split(':')[1];
                }
                if (~field.indexOf('.')) {
                    field = item.split('.')[1];
                    link = item.split('.')[0];
                    scope = this.getMetadata().get('entityDefs.' + entityType + '.links.' + link + '.entity');
                }

                var type = this.getMetadata().get('entityDefs.' + scope + '.fields.' + field + '.type');

                switch (type) {
                    case 'enum':
                        itemList.push('LIST:' + item);
                        return;
                    case 'date':
                    case 'datetime':
                        return;
                    default:
                        if (!~this.selected.indexOf('ASC:' + item) && !~this.selected.indexOf('DESC:' + item)) {
                            itemList.push('ASC:' + item);
                            itemList.push('DESC:' + item);
                        } else {
                            if (~this.selected.indexOf('ASC:' + item)) {
                                itemList.push('ASC:' + item);
                            } else if (~this.selected.indexOf('DESC:' + item)) {
                                itemList.push('DESC:' + item);
                            }
                        }
                }
            }, this);

            var columnList = this.model.get('columns') || [];
            columnList.forEach(function (item) {
                itemList.push('ASC:' + item);
                itemList.push('DESC:' + item);
            }, this);

            this.params.options = itemList;
        },

        setupTranslatedOptions: function () {
            this.translatedOptions = {};

            this.params.options.forEach(function (item) {
                var order = item.substr(0, item.indexOf(':'));
                var p = item.substr(item.indexOf(':') + 1);

                var scope = this.model.get('entityType');
                var entityType = scope;

                var field = p;

                var func = false;
                var link = false;

                if (~p.indexOf(':')) {
                    func = p.split(':')[0];
                    p = field = p.split(':')[1];
                }

                if (~p.indexOf('.')) {
                    link = p.split('.')[0];
                    field = p.split('.')[1];
                    scope = this.getMetadata().get('entityDefs.' + entityType + '.links.' + link + '.entity');
                }
                this.translatedOptions[item] = this.translate(field, 'fields', scope);
                if (link) {
                    this.translatedOptions[item] = this.translate(link, 'links', entityType) + '.' + this.translatedOptions[item];
                }
                if (func) {
                    if (func === 'COUNT') {
                        this.translatedOptions[item] = this.translate(func, 'functions', 'Report').toUpperCase()
                    } else {
                        this.translatedOptions[item] = this.translate(func, 'functions', 'Report').toUpperCase() + ': ' + this.translatedOptions[item];
                    }
                }
                if (order != 'LIST') {
                    this.translatedOptions[item] = this.translatedOptions[item] + ' (' + this.translate(order, 'orders', 'Report').toUpperCase() + ')';
                }
            }, this);
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.setupOptions();
            this.setupTranslatedOptions();

            this.listenTo(this.model, 'change', function (model) {
                if (model.hasChanged('orderBy') || model.hasChanged('groupBy') || model.hasChanged('columns')) {
                    this.setupOptions();
                    this.setupTranslatedOptions();
                    this.render();
                }
            }, this);

        }

    });

});

