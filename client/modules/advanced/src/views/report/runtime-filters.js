

Espo.define('Advanced:Views.Report.RuntimeFilters', 'View', function (Dep) {

    return Dep.extend({

        template: 'advanced:report.runtime-filters',

        setup: function () {
            this.wait(true);

            this.filterList = this.options.filterList;

            this.filtersData = this.options.filtersData || {};

            this.getModelFactory().create(this.options.entityType, function (model) {
                this.model = model;
                this.getCollectionFactory().create(this.options.entityType, function (collection) {

                    Espo.require('SearchManager', function (SearchManager) {
                        this.searchManager = new SearchManager(collection, 'report', null, this.getDateTime());
                        this.wait(false);
                    }.bind(this));

                }, this);

            }, this);
        },

        afterRender: function () {
            this.options.filterList.forEach(function (name) {
                var params = this.filtersData[name] || null;
                this.createFilter(name, params);
            }, this);
        },

        createFilter: function (name, params, callback) {
            params = params || {};

            this.$el.find('.filters-row').append('<div class="filter filter-' + Espo.Utils.toDom(name) + ' col-sm-4 col-md-3" />');

            var scope = this.model.name;
            var field = name;


            if (~name.indexOf('.')) {
                var link = name.split('.')[0];
                field = name.split('.')[1];
                scope = this.getMetadata().get('entityDefs.' + this.model.name + '.links.' + link + '.entity');
            }
            if (!scope || !field) {
                return;
            }

            this.getModelFactory().create(scope, function (model) {
                this.createView('filter-' + name, 'Search.Filter', {
                    name: field,
                    model: model,
                    params: params,
                    el: this.options.el + ' .filter-' + Espo.Utils.toDom(name),
                    notRemovable: true,
                }, function (view) {
                    if (typeof callback === 'function') {
                        view.once('after:render', function () {
                            callback();
                        });
                    }
                    view.render();
                });
            }, this);
        },

        fetchRaw: function () {
            var data = {};
            this.filterList.forEach(function (name) {
                data[name] = this.getView('filter-' + name).getView('field').fetchSearch();

                var field = data[name].field || name;
                if (~name.indexOf('.') && !~field.indexOf('.')) {
                    var link = name.split('.')[0];
                    field = link + '.' + field;
                }
                data[name].field = field
            }, this);
            return data;
        },

        fetch: function () {
            var data = this.fetchRaw();
            this.searchManager.setAdvanced(data);
            return this.searchManager.getWhere();
        },

    });
});
