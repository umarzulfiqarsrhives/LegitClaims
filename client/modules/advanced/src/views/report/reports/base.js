

Espo.define('Advanced:Views.Report.Reports.Base', 'View', function (Dep) {

    return Dep.extend({

        template: 'advanced:report.reports.base',

        data: function () {
        },

        events: {
            'click [data-action="run"]': function () {
                this.run();
            },
            'click [data-action="showSubReport"]': function (e) {
                var groupValue = $(e.currentTarget).data('group-value');

                this.getCollectionFactory().create(this.model.get('entityType'), function (collection) {
                    collection.url = 'Report/action/runList?id=' + this.model.id + '&groupValue=' + encodeURIComponent(groupValue);

                    if (this.hasRuntimeFilters()) {
                        collection.where = this.lastFetchedWhere;
                    }

                    this.notify('Please wait...');
                    this.listenToOnce(collection, 'sync', function () {
                        this.createView('subReport', 'Advanced:Report.Modals.SubReport', {
                            model: this.model,
                            result: this.result,
                            groupValue: groupValue,
                            collection: collection
                        }, function (view) {
                            view.notify(false);
                            view.render();
                        });
                    }, this);

                    collection.fetch();

                }, this);


            }
        },

        initReport: function () {
            if (!this.hasRuntimeFilters()) {
                this.once('after:render', function () {
                    this.run();
                }, this);
            }

            this.chartType = this.model.get('chartType');

            if (this.hasRuntimeFilters()) {
                this.createRuntimeFilters();
                this.on('after:render', function () {
                    this.$el.find('.report-control-panel').removeClass('hidden');
                }, this);
            }
        },

        createRuntimeFilters: function () {
            var filtersData = this.getStorage().get('state', this.getFilterStorageKey()) || null;

            this.createView('runtimeFilters', 'Advanced:Report.RuntimeFilters', {
                el: this.options.el + ' .report-runtime-filters-contanier',
                entityType: this.model.get('entityType'),
                filterList: this.model.get('runtimeFilters'),
                filtersData: filtersData
            });

        },

        hasRuntimeFilters: function () {
            if ((this.model.get('runtimeFilters') || []).length) {
                return true;
            }
        },

        getRuntimeFilters: function () {
            if (this.hasRuntimeFilters()) {
                this.lastFetchedWhere = this.getView('runtimeFilters').fetch();
                return this.lastFetchedWhere;
            }
            return null;
        },

        getFilterStorageKey: function () {
            return 'report-filters-' + this.model.id;
        },

        storeRuntimeFilters: function (where) {
            if (this.hasRuntimeFilters()) {
                var filtersData = this.getView('runtimeFilters').fetchRaw();

                this.getStorage().set('state', this.getFilterStorageKey(), filtersData);
            }
        }

    });

});

