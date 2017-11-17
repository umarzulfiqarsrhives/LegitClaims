Espo.define('Advanced:Views.Dashlets.Report', ['Views.Dashlets.Abstract.Base', 'SearchManager', 'Advanced:ReportHelper'], function (Dep, SearchManager, ReportHelper) {

    return Dep.extend({

        name: 'Report',

        optionsView: 'Advanced:Dashlets.Options.Report',

        _template: '<div class="report-results-container" style="height: 100%;"></div>',

        setup: function () {
            this.optionsFields['report'] = {
                type: 'link',
                entity: 'Report',
                required: true,
                view: 'Advanced:Report.Fields.DashletSelect'
            };
            this.optionsFields['column'] = {
                'type': 'enum',
                'options': []
            };

            this.reportHelper = new ReportHelper(this.getMetadata(), this.getLanguage());
        },

        getListLayout: function () {
            var layout = [];
            (this.columns || []).forEach(function (item) {
                var o = {
                    "name": item
                };
                if (item == 'name') {
                    o.link = true;
                }
                layout.push(o);
            }, this);
            return layout;
        },

        displayError: function (msg) {
            msg = msg || 'error';
            this.$el.find('.report-results-container').html(this.translate(msg, 'errorMessages', 'Report'));
        },

        actionRefresh: function () {
            if (this.hasView('reportChart')) {
                this.clearView('reportChart');
            }
            this.render();
        },

        afterRender: function () {
            this.run();
        },

        run: function () {
            var reportId = this.getOption('reportId');
            if (!reportId) {
                this.displayError('selectReport');
                return;
            };

            var entityType = this.getOption('entityType');
            if (!entityType) {
                this.displayError();
                return;
            };

            var type = this.getOption('type');
            if (!type) {
                this.displayError();
                return;
            };

            this.getModelFactory().create('Report', function (model) {
                model.id = reportId;
                this.listenToOnce(model, 'sync', function () {

                    var depth = model.get('depth') || (model.get('groupBy') || []).length;
                    if (type == 'Grid' && !depth) {
                        this.displayError();
                        return;
                    };

                    var columns = this.columns = model.get('columns');
                    if (type == 'List' && !columns) {
                        this.displayError();
                        return;
                    };

                    var chartType = model.get('chartType');
                    if (type == 'Grid' && !chartType) {
                        this.displayError('noChart');
                        return;
                    };

                    var version = this.getConfig().get('version');
                    var height = '245px';

                    if (version === 'dev' || parseInt((version || '').charAt(0)) >= 4) {
                        height = '100%';
                        if (depth === 2 || ~['Pie'].indexOf(chartType)) {
                            height = 'calc(100% - 29px)';
                        }
                    }

                    this.getCollectionFactory().create(entityType, function (collection) {
                        var searchManager = new SearchManager(collection, 'report', null, this.getDateTime());
                        var where = null;
                        if (this.getOption('filtersData')) {
                            searchManager.setAdvanced(this.getOption('filtersData'));
                            where = searchManager.getWhere();
                        }

                        switch (type) {
                            case 'List':
                                collection.url = 'Report/action/runList?id=' + reportId;
                                collection.where = where;

                                this.listenToOnce(collection, 'sync', function () {
                                    this.createView('list', 'Record.List', {
                                        el: this.options.el + ' .report-results-container',
                                        collection: collection,
                                        listLayout: this.getListLayout(),
                                        checkboxes: false,
                                        rowActionsView: false,
                                    }, function (view) {
                                        this.notify(false);
                                        view.render();
                                    }.bind(this));
                                }, this);
                                collection.fetch();

                                break;

                            case 'Grid':
                                $.ajax({
                                    url: 'Report/action/run',
                                    data: {
                                        id: reportId,
                                        where: where,
                                    }
                                }).done(function (result) {
                                    var column = this.getOption('column');

                                    this.createView('reportChart', 'Advanced:Report.Reports.Charts.Grid' + depth + chartType, {
                                        el: this.options.el + ' .report-results-container',
                                        column: column,
                                        result: result,
                                        reportHelper: this.reportHelper,
                                        height: height,
                                    }, function (view) {
                                        view.render();
                                    });
                                }.bind(this));

                                break;
                        }

                    }, this);
                }, this);
                model.fetch();
            }, this);
        },

        setupActionList: function () {
            this.actionList.unshift({
                'name': 'viewReport',
                'html': this.translate('View Report', 'labels', 'Report')
            });
        },

        actionViewReport: function () {
            var reportId = this.getOption('reportId');
            if (reportId) {
                this.getRouter().navigate('#Report/view/' + reportId, {trigger: true});
            }
        },
    });
});


