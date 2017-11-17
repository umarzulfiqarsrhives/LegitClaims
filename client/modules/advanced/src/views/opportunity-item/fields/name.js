

Espo.define('Advanced:Views.OpportunityItem.Fields.Name', 'Views.Fields.Varchar', function (Dep) {

    return Dep.extend({

        detailTemplate: 'advanced:opportunity-item.fields.name.detail',

        listTemplate: 'advanced:opportunity-item.fields.name.detail',

        editTemplate: 'advanced:opportunity-item.fields.name.edit',

        data: function () {
            var data = Dep.prototype.data.call(this);

            data['productSelectDisabled'] = this.isNotProduct();
            data['isProduct'] = !!this.model.get('productId');
            data['productId'] = this.model.get('productId');

            return data;
        },

        isNotProduct: function () {
            return (!this.model.get('productId') && this.model.get('name') && this.model.get('name') !== '');
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.events['click [data-action="selectProduct"]'] = this.actionSelectProduct;

            this.on('change', function () {
                this.handleSelectProductVisibility();
            }, this);
        },

        handleSelectProductVisibility: function () {
            if (this.isNotProduct()) {
                this.$el.find('[data-action="selectProduct"]').addClass('disabled');
            } else {
                this.$el.find('[data-action="selectProduct"]').removeClass('disabled');
            }
        },

        handleNameAvailability: function () {
            if (this.model.get('productId')) {
                this.$element.attr('readonly', true);
            }
        },

        actionSelectProduct: function () {
            this.notify('Loading...');

            var viewName = this.getMetadata().get('clientDefs.Product.modalViews.select') || 'Modals.SelectCategoryTreeRecords';

            this.createView('dialog', viewName, {
                scope: 'Product',
                createButton: false,
                primaryFilterName: 'available'
            }, function (view) {
                view.render();
                this.notify(false);
                this.listenToOnce(view, 'select', function (model) {
                    view.close();
                    this.selectProduct(model);
                }, this);
            }.bind(this));
        },

        selectProduct: function (product) {
            var sourcePrice = product.get('unitPrice');
            var sourceCurrency = product.get('unitPriceCurrency');
            var targetCurrency = this.model.get('unitPriceCurrency');

            var baseCurrency = this.getConfig().get('baseCurrency');
            var rates = this.getConfig().get('currencyRates') || {};

            var value = sourcePrice;
            value = value * (rates[sourceCurrency] || 1.0);
            value = value / (rates[targetCurrency] || 1.0);

            var targetPrice = Math.round(value * 100) / 100;

            this.model.set({
                productId: product.id,
                productName: product.get('name'),
                name: product.get('name'),
                unitPrice: targetPrice,
                unitPriceCurrency: targetCurrency
            });
            this.handleSelectProductVisibility();
            this.handleNameAvailability();

            this.trigger('change');
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);
        },

    });
});

