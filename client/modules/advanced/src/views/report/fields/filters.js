

Espo.define('Advanced:Views.Report.Fields.Filters', 'Views.Fields.MultiEnum', function (Dep) {

    return Dep.extend({

        getFilterList: function () {
            var entityType = this.model.get('entityType');

            var fields = this.getMetadata().get('entityDefs.' + entityType + '.fields');
            var filterList = Object.keys(fields).filter(function (field) {
                return this.getFieldManager().checkFilter(fields[field].type) && !fields[field].disabled;
            }, this);

            filterList.sort(function (v1, v2) {
                return this.translate(v1, 'fields', entityType).localeCompare(this.translate(v2, 'fields', entityType));
            }.bind(this));

            var links = this.getMetadata().get('entityDefs.' + entityType + '.links') || {};

            var linkList = Object.keys(links).sort(function (v1, v2) {
                return this.translate(v1, 'links', entityType).localeCompare(this.translate(v2, 'links', entityType));
            }.bind(this));

            linkList.forEach(function (link) {
                var type = links[link].type
                if (type != 'belongsTo' && type != 'hasMany' && type != 'hasChildren') return;
                var scope = links[link].entity;
                if (!scope) return;

                var fields = this.getMetadata().get('entityDefs.' + scope + '.fields') || {};
                var foreignFilterList = Object.keys(fields).filter(function (field) {
                    if (~['linkMultiple', 'linkParent', 'personName'].indexOf(fields[field].type)) return;
                    return this.getFieldManager().checkFilter(fields[field].type) && !fields[field].disabled;
                }, this);
                foreignFilterList.sort(function (v1, v2) {
                    return this.translate(v1, 'fields', scope).localeCompare(this.translate(v2, 'fields', scope));
                }.bind(this));

                foreignFilterList.forEach(function (item) {
                    filterList.push(link + '.' + item);
                }, this);
            }, this);

            return filterList;
        },

        setupTranslatedOptions: function () {
            this.translatedOptions = {};

            var entityType = this.model.get('entityType');
            this.params.options.forEach(function (item) {
                var field = item;
                var scope = entityType;
                var isForeign = false;
                if (~item.indexOf('.')) {
                    isForeign = true;
                    field = item.split('.')[1];
                    var link = item.split('.')[0];
                    scope = this.getMetadata().get('entityDefs.' + entityType + '.links.' + link + '.entity');
                }
                this.translatedOptions[item] = this.translate(field, 'fields', scope);
                if (isForeign) {
                    this.translatedOptions[item] =  this.translate(link, 'links', entityType) + '.' + this.translatedOptions[item];
                }
            }, this);
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.params.options = this.getFilterList();
            this.setupTranslatedOptions();
        },

    });

});

