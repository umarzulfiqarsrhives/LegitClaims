

Espo.define('Advanced:Views.Product.List', 'Views.List', function (Dep) {

    return Dep.extend({

        template: 'advanced:product.list',

        quickCreate: false,

        currentCategoryId: null,

        currentCategoryName: '',

        afterRender: function () {
            Dep.prototype.afterRender.call(this);
            if (!this.hasView('categories')) {
                this.loadCategories();
            }
        },

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
                        checkboxes: false,
                        showEditLink: this.getAcl().check('ProductCategory', 'edit')
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

        getCreateAttributes: function () {
            return {
                categoryId: this.currentCategoryId,
                categoryName: this.currentCategoryName
            };
        },

    });

});
