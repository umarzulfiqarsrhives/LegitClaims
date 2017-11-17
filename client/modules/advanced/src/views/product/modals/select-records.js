

Espo.define('Advanced:Views.Product.Modals.SelectRecords', 'Views.Modals.SelectRecords', function (Dep) {

    return Dep.extend({

        template: 'advanced:product.modals.select-records',

        loadCategories: function () {
            this.getCollectionFactory().create('ProductCategory', function (collection) {
                collection.url = collection.name + '/action/listTree';

                this.listenToOnce(collection, 'sync', function () {
                    this.createView('categories', 'Record.ListTree', {
                        collection: collection,
                        el: this.options.el + ' .categories-container',
                        selectable: true,
                        createDisabled: true,
                        showRoot: true,
                        rootName: this.translate('Product', 'scopeNamesPlural'),
                        buttonsDisabled: true,
                        checkboxes: false
                    }, function (view) {
                        view.render();

                        this.listenTo(view, 'select', function (model) {
                            this.currentCategoryId = null;
                            this.currentCategoryName = '';

                            if (model && model.id) {
                                this.currentCategoryId = model.id;
                                this.currentCategoryName = model.get('name');
                            }
                            this.collection.whereAdditional = null;

                            if (this.currentCategoryId) {
                                this.collection.whereAdditional = [
                                    {
                                        field: 'category',
                                        type: 'inCategory',
                                        value: model.id
                                    }
                                ];
                            }
                            this.notify('Please wait...');
                            this.listenToOnce(this.collection, 'sync', function () {
                                this.notify(false);
                            }, this);
                            this.collection.fetch();

                        }, this);
                    }.bind(this));
                }, this);
                collection.fetch();
            }, this);
        },

        loadList: function () {
            this.loadCategories();
            Dep.prototype.loadList.call(this);
        },
    });

});
