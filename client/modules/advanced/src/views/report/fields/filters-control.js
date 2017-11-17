

Espo.define('Advanced:Views.Report.Fields.FiltersControl', 'Views.Fields.Base', function (Dep) {

    return Dep.extend({

        editTemplate: 'advanced:report.fields.filters-control.edit',

        detailTemplate: 'advanced:report.fields.filters-control.detail',

        setup: function () {
            var entityType = this.model.get('entityType');

            this.wait(true);
            this.getModelFactory().create(entityType, function (model) {

                this.seed = model;
                this.wait(false);


            }, this);

            this.setupFiltersData();

            this.listenTo(this.model, 'change:filters', function () {

                var previousFilterList = Espo.Utils.clone(this.filterList);
                var toAdd = [];
                var toRemove = [];
                this.setupFiltersData();
                var filterList = this.filterList;
                filterList.forEach(function (name) {
                    if (!~previousFilterList.indexOf(name)) {
                        toAdd.push(name);
                    }
                });
                previousFilterList.forEach(function (name) {
                    if (!~filterList.indexOf(name)) {
                        toRemove.push(name);
                    }
                });

                if (this.isRendered()) {
                    toAdd.forEach(function (name) {
                        this.createFilter(name);
                    }, this);
                    toRemove.forEach(function (name) {
                        this.removeFilter(name);
                    }, this);
                }

            }, this);
        },

        setupFiltersData: function () {
            this.filterList = Espo.Utils.clone(this.model.get('filters')) || [];
        },

        afterRender: function () {
            this.filterList.forEach(function (name) {
                var params = (this.model.get('filtersData') || {})[name];
                this.createFilter(name, params);
            }, this);
        },

        removeFilter: function (name) {
            this.clearView('name-' + name);
            this.$el.find('.filters-row .filter-' + Espo.Utils.toDom(name)).remove();
        },

        createFilter: function (name, params, callback) {
            params = params || {};

            this.$el.find('.filters-row').append('<div class="filter filter-' + Espo.Utils.toDom(name) + ' col-sm-4 col-md-3" />');

            var scope = this.seed.name;
            var field = name;

            if (~name.indexOf('.')) {
                var link = name.split('.')[0];
                field = name.split('.')[1];
                scope = this.getMetadata().get('entityDefs.' + this.seed.name + '.links.' + link + '.entity');
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

        fetch: function () {
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

            return {
                'filtersData': data
            };
        },

    });

});

