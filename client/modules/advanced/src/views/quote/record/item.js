

Espo.define('Advanced:Views.Quote.Record.Item', 'Views.Base', function (Dep) {

    return Dep.extend({

        template: 'advanced:quote.record.item',

        data: function () {
            return {
                id: this.model.id,
                mode: this.mode
            };
        },

        setup: function () {
            this.mode = this.options.mode;

            this.createView('quantity', 'Fields.Float', {
                model: this.model,
                defs: {
                    name: 'quantity'
                },
                mode: this.mode,
                el: this.options.el + ' .field-quantity',
                inlineEditDisabled: true
            }, function (view) {
                this.listenTo(view, 'change', function () {
                    setTimeout(function () {
                        this.trigger('change');
                    }.bind(this), 50);
                }, this);
            }.bind(this));

            this.createView('name', 'Advanced:QuoteItem.Fields.Name', {
                model: this.model,
                defs: {
                    name: 'name',
                },
                mode: this.mode,
                el: this.options.el + ' .field-name',
                inlineEditDisabled: true
            }, function (view) {
                this.listenTo(view, 'change', function () {
                    setTimeout(function () {
                        this.trigger('change');
                    }.bind(this), 50);
                }, this);
            }.bind(this));

            this.createView('taxRate', 'Advanced:QuoteItem.Fields.TaxRate', {
                model: this.model,
                defs: {
                    name: 'taxRate',
                    params: {
                    }
                },
                mode: this.mode,
                el: this.options.el + ' .field-taxRate',
                inlineEditDisabled: true
            }, function (view) {
                this.listenTo(view, 'change', function () {
                    setTimeout(function () {
                        this.trigger('change');
                    }.bind(this), 50);
                }, this);
            }.bind(this));

            this.createView('listPrice', 'Advanced:QuoteItem.Fields.UnitPrice', {
                model: this.model,
                defs: {
                    name: 'listPrice',
                },
                mode: this.mode,
                el: this.options.el + ' .field-listPrice',
                inlineEditDisabled: true
            }, function (view) {
                this.listenTo(view, 'change', function () {
                    setTimeout(function () {
                        this.trigger('change');
                    }.bind(this), 50);
                }, this);
            }.bind(this));

            this.createView('unitPrice', 'Advanced:QuoteItem.Fields.UnitPrice', {
                model: this.model,
                defs: {
                    name: 'unitPrice',
                },
                mode: this.mode,
                el: this.options.el + ' .field-unitPrice',
                inlineEditDisabled: true
            }, function (view) {
                this.listenTo(view, 'change', function () {
                    setTimeout(function () {
                        this.trigger('change');
                    }.bind(this), 50);
                }, this);
            }.bind(this));

            this.createView('amount', 'Fields.Currency', {
                model: this.model,
                defs: {
                    name: 'amount',
                },
                mode: 'detail',
                el: this.options.el + ' .field-amount',
                inlineEditDisabled: true
            });
        },

        afterRender: function () {
            this.listenTo(this.getView('quantity'), 'change', function () {
                this.calculateAmount();
            }, this);

            this.listenTo(this.getView('listPrice'), 'change', function () {
                if (!this.model.get('unitPrice') && this.model.get('unitPrice') !== 0) {
                    this.model.set('unitPrice', this.model.get('listPrice'));
                }
                this.calculateAmount();
            }, this);

            this.listenTo(this.getView('unitPrice'), 'change', function () {
                this.calculateAmount();
            }, this);

            this.listenTo(this.getView('name'), 'change', function () {
                this.calculateAmount();
            }, this);
        },

        calculateAmount: function () {
            var quantity = this.model.get('quantity');
            var unitPrice = this.model.get('unitPrice');
            var unitPriceCurrency = this.model.get('unitPriceCurrency');

            var amount = quantity * unitPrice;
            amount = Math.round(amount * 100) / 100;
            var amountCurrency = unitPriceCurrency;

            this.model.set('amount', amount);
            this.model.set('amountCurrency', amountCurrency);
        },

        fetch: function () {
            var data = {
                id: this.model.id,
                quantity: this.model.get('quantity'),
                taxRate: this.model.get('taxRate') || 0,
                listPrice: this.model.get('listPrice'),
                listPriceCurrency: this.model.get('listPriceCurrency'),
                unitPrice: this.model.get('unitPrice'),
                unitPriceCurrency: this.model.get('unitPriceCurrency'),
                amount: this.model.get('amount'),
                amountCurrency: this.model.get('amountCurrency'),
                productId: this.model.get('productId') || null,
                productName: this.model.get('productName') || null,
                name: this.model.get('name')
            };
            return data;
        }

    });
});

