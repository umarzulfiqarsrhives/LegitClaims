

Espo.define('Advanced:Views.Report.Reports.List', 'Advanced:Views.Report.Reports.Base', function (Dep) {

    return Dep.extend({

        setup: function () {
            this.initReport();
        },

        getListLayout: function () {
            var layout = [];
            (this.model.get('columns') || []).forEach(function (item) {
                var o = {
                    'name': item
                };
                if (item == 'name') {
                    o.link = true;
                }
                layout.push(o);
            }, this);
            return layout;
        },

        run: function () {
            this.notify('Please wait...');

            $container = this.$el.find('.report-results-container');
            $container.empty();

            $listContainer = $('<div>').addClass('report-list');

            $container.append($listContainer);

            this.getCollectionFactory().create(this.model.get('entityType'), function (collection) {
                collection.url = 'Report/action/runList?id=' + this.model.id;

                collection.where = this.getRuntimeFilters();

                this.listenToOnce(collection, 'sync', function () {
                    this.storeRuntimeFilters();

                    this.createView('list', 'Advanced:Record.ListForReport', {
                        el: this.options.el + ' .report-list',
                        collection: collection,
                        listLayout: this.getListLayout(),
                        displayTotalCount: true,
                        reportId: this.model.id,
                        runtimeWhere: collection.where
                    }, function (view) {
                        this.notify(false);
                        view.render();
                    }.bind(this));
                }, this);

                collection.fetch();

            }, this);

        },

    });

});
