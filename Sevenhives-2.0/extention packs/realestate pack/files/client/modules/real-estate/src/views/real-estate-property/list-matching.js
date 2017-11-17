

Espo.define('real-estate:views/real-estate-property/list-matching', ['views/main', 'search-manager', 'real-estate:views/real-estate-property/record/panels/matching-requests'], function (Dep, SearchManager, Panel) {

    return Dep.extend({

        template: 'list',

        el: '#main',

        scope: 'RealEstateProperty',

        name: 'ListMatching',

        views: {
            header: {
                el: '#main > .page-header',
                view: 'views/header'
            }
        },

        searchPanel: true,

        searchManager: null,

        setup: function () {
            this.wait(true);

            var countLoaded = 0;

            var proceed = function () {
                if (countLoaded == 2) {
                    this.wait(false);
                }
            }.bind(this);

            this.getModelFactory().create('RealEstateProperty', function (model) {
                this.model = model;
                model.id = this.options.id;

                this.model.fetch().done(function () {
                    countLoaded++;
                    proceed();
                }.bind(this));
            }, this);

            this.getCollectionFactory().create('RealEstateRequest', function (collection) {
                this.collection = collection;

                this.collection.url = 'RealEstateProperty/' + this.options.id + '/matchingRequests';

                this.collection.maxSize = this.getConfig().get('recordsPerPage') || this.collection.maxSize;

                if (this.searchPanel) {
                    this.setupSearchManager();
                }

                this.setupSorting();

                if (this.searchPanel) {
                    this.setupSearchPanel();
                }

                countLoaded++;
                proceed();

            }, this);

        },

        setupSearchPanel: function () {
            this.createView('search', 'Record.Search', {
                collection: this.collection,
                el: '#main > .search-container',
                searchManager: this.searchManager,
            }, function (view) {
                this.listenTo(view, 'reset', function () {
                    this.collection.sortBy = this.defaultSortBy;
                    this.collection.asc = this.defaultAsc;
                }, this);
            }.bind(this));
        },

        getSearchDefaultData: function () {
            return this.getMetadata().get('clientDefs.' + this.collection.name + '.defaultFilterData');
        },

        setupSearchManager: function () {
            var collection = this.collection;

            var searchManager = new SearchManager(collection, 'listMatching', false, this.getDateTime(), this.getSearchDefaultData());

            collection.where = searchManager.getWhere();
            this.searchManager = searchManager;
        },

        setupSorting: function () {
            if (!this.searchPanel) return;

            var collection = this.collection;

            this.defaultSortBy = collection.sortBy;
            this.defaultAsc = collection.asc;
        },

        getRecordViewName: function () {
            return this.getMetadata().get('clientDefs.' + this.collection.name + '.recordViews.list') || 'Record.List';
        },

        afterRender: function () {
            if (!this.isRendered()) {
                this.loadList();
            }
        },

        loadList: function () {
            this.notify('Loading...');
            if (this.collection.isFetched) {
                this.createListRecordView(false);
            } else {
                this.listenToOnce(this.collection, 'sync', function () {
                    this.createListRecordView();
                }, this);
                this.collection.fetch();
            }
        },

        createListRecordView: function (fetch) {
            var listViewName = this.getRecordViewName();
            this.createView('list', listViewName, {
                collection: this.collection,
                el: this.options.el + ' .list-container',
                type: 'listForProperty',
                rowActionsView: 'real-estate:views/real-estate-request/record/row-actions/for-property'
            }, function (view) {
                view.render();
                view.notify(false);
                if (fetch) {
                    setTimeout(function () {
                        this.collection.fetch();
                    }.bind(this), 2000);
                }
            });
        },

        getHeader: function () {
            return this.buildHeaderHtml([
                '<a href="#'+this.scope+'">' + this.getLanguage().translate(this.scope, 'scopeNamesPlural') + '</a>',
                '<a href="#'+this.scope+'/view/'+this.model.id+'">' + this.model.get('name') + '</a>',
                this.getLanguage().translate('Matching Requests', 'labels', this.scope)
            ]);
        },

        updatePageTitle: function () {
            this.setPageTitle(this.model.get('name'));
        },

        actionSetInterested: function (data) {
            Panel.prototype.actionSetInterested.call(this, data);
        },

        actionSetNotInterested: function (data) {
            Panel.prototype.actionSetNotInterested.call(this, data);
        },

        actionUnsetNotInterested: function (data) {
            Panel.prototype.actionUnsetNotInterested.call(this, data);
        }

    });
});

