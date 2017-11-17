

Espo.define('Advanced:Views.Quote.Fields.ItemList', ['Views.Fields.Base', 'Model'], function (Dep, Model) {

    return Dep.extend({

        detailTemplate: 'advanced:quote.fields.item-list.detail',

        listTemplate: 'advanced:quote.fields.item-list.detail',

        editTemplate: 'advanced:quote.fields.item-list.edit',

        lastNumber: 0,

        events: {
            'click [data-action="removeItem"]': function (e) {
                var id = $(e.currentTarget).attr('data-id');
                this.removeItem(id);
            },
            'click [data-action="addItem"]': function (e) {
                this.addItem();
            }
        },

        data: function () {
            return {
                showFields: (this.model.get('itemList') || []).length > 0
            };
        },

        getAttributeList: function () {
            return ['itemList'];
        },

        setMode: function (mode) {
            Dep.prototype.setMode.call(this, mode);
            if (this.isRendered()) {
                this.getView('shippingCost').setMode(mode);
            }
        },

        setup: function () {
            this.listenTo(this.model, 'change:amountCurrency', function (model, v, o) {
                if (!o.ui) return;
                var currency = this.model.get('amountCurrency');
                var itemList = Espo.Utils.cloneDeep(this.model.get('itemList') || []);
                itemList.forEach(function (item) {
                    item.listPriceCurrency = currency;
                    item.unitPriceCurrency = currency;
                    item.amountCurrency = currency;
                }, this);

                this.model.set('preDiscountedAmountCurrency', currency);
                this.model.set('shippingCostCurrency', currency);
                this.model.set('taxAmountCurrency', currency);
                this.model.set('grandTotalAmountCurrency', currency);
                this.model.set('discountAmountCurrency', currency);

                this.model.set('itemList', itemList);
            }, this);

            this.listenTo(this.model, 'change:taxRate', function (model, v, o) {
                if (!o.ui) return;
                var taxRate = this.model.get('taxRate') || 0;
                var itemList = Espo.Utils.cloneDeep(this.model.get('itemList') || []);
                itemList.forEach(function (item) {
                    item.taxRate = taxRate;
                }, this);
                this.model.set('itemList', itemList);
                this.calculateAmount();
            }, this);

            this.listenTo(this.model, 'change:shippingCost', function (model, v, o) {
                if (!o.ui) return;
                this.calculateAmount();
            }, this);

            this.createView('preDiscountedAmount', 'Fields.Currency', {
                el: this.options.el + ' .field-preDiscountedAmount',
                model: this.model,
                mode: 'detail',
                inlineEditDisabled: true,
                defs: {
                    name: 'preDiscountedAmount'
                }
            });

            this.createView('discountAmount', 'Fields.Currency', {
                el: this.options.el + ' .field-discountAmount',
                model: this.model,
                mode: 'detail',
                inlineEditDisabled: true,
                defs: {
                    name: 'discountAmount'
                }
            });

            this.createView('amount', 'Fields.Currency', {
                el: this.options.el + ' .field-amount-bottom',
                model: this.model,
                mode: 'detail',
                inlineEditDisabled: true,
                defs: {
                    name: 'amount'
                }
            });

            this.createView('taxAmount', 'Fields.Currency', {
                el: this.options.el + ' .field-taxAmount',
                model: this.model,
                mode: 'detail',
                inlineEditDisabled: true,
                defs: {
                    name: 'taxAmount'
                }
            });

            this.createView('shippingCost', 'Advanced:Quote.Fields.ShippingCost', {
                el: this.options.el + ' .field-shippingCost',
                model: this.model,
                mode: this.mode,
                inlineEditDisabled: true,
                defs: {
                    name: 'shippingCost'
                }
            });

            this.createView('grandTotalAmount', 'Fields.Currency', {
                el: this.options.el + ' .field-grandTotalAmount',
                model: this.model,
                mode: 'detail',
                inlineEditDisabled: true,
                defs: {
                    name: 'grandTotalAmount'
                }
            });
        },

        handleCurrencyField: function () {
            var recordView = this.getParentView().getParentView().getParentView();

            var itemList = this.model.get('itemList') || [];

            if (itemList.length) {
                this.showAdditionalFields();
                if (recordView.setFieldReadOnly) {
                    recordView.setFieldReadOnly('amount');
                }
            } else {
                if (recordView.setFieldNotReadOnly) {
                    recordView.setFieldNotReadOnly('amount');
                }
                this.hideAdditionalFields();
            }
        },

        showAdditionalFields: function () {
            this.$el.find('.currency-row').removeClass('hidden');
            this.$el.find('.totals-row').removeClass('hidden');
        },

        hideAdditionalFields: function () {
            this.$el.find('.currency-row').addClass('hidden');
            this.$el.find('.totals-row').addClass('hidden');
        },

        afterRender: function () {
            this.$container = this.$el.find('.container');

            this.handleCurrencyField();

            if (this.mode == 'edit') {
                var model = new Model();

                model.set('currency', this.model.get('amountCurrency') || this.getPreferences().get('defaultCurrency') || this.getConfig().get('defaultCurrency'));

                this.listenTo(model, 'change:currency', function () {
                    this.model.set('amountCurrency', model.get('currency'), {ui: true});
                }, this);

                this.createView('currency', 'Fields.Enum', {
                    el: this.options.el + ' .field-currency',
                    model: model,
                    mode: 'edit',
                    defs: {
                        name: 'currency',
                        params: {
                            options: this.getConfig().get('currencyList') || []
                        }
                    }
                }, function (view) {
                    view.render();
                }.bind(this));
            }

            this.createView('itemList', 'Advanced:Quote.Record.ItemList', {
                el: this.options.el + ' .item-list-container',
                model: this.model,
                mode: this.mode
            }, function (view) {
                this.listenTo(view, 'after:render', function () {
                    if (this.mode == 'edit') {
                        this.$el.find('.item-list-internal-container').sortable({
                            handle: '.drag-icon',
                            stop: function () {
                                var idList = [];
                                this.$el.find('.item-list-internal-container').children().each(function (i, el) {
                                    idList.push($(el).attr('data-id'));
                                });
                                this.reOrder(idList);
                            }.bind(this),
                        });
                    }
                }, this);
                view.render();

                this.listenTo(view, 'change', function () {
                    this.trigger('change');
                    this.calculateAmount();
                }, this);
            }.bind(this));

            if (this.model.isNew()) {
                var itemList = this.model.get('itemList') || [];
                itemList.forEach(function (item) {
                    if (!item.id) {
                        var id = 'cid' + this.lastNumber;
                        this.lastNumber++;
                        item.id = id;
                    }
                }, this);
                this.calculateAmount();
            }
        },

        fetchItemList: function () {
            return (this.getView('itemList').fetch() || {}).itemList || [];
        },

        fetch: function () {
            var data = {};
            if (this.hasView('currency')) {
                data.amountCurrency = this.getView('currency').fetch().currency;
            }
            data.itemList = this.fetchItemList();
            return data;
        },

        addItem: function () {
            var id = 'cid' + this.lastNumber;
            this.lastNumber++;
            var data = {
                id: id,
                quantity: 1,
                listPriceCurrency: this.model.get('amountCurrency'),
                unitPriceCurrency: this.model.get('amountCurrency'),
                isTaxable: true,
                taxRate: this.model.get('taxRate') || 0
            };
            var itemList = Espo.Utils.clone(this.fetchItemList());
            itemList.push(data);
            this.model.set('itemList', itemList);
            this.calculateAmount();
        },

        removeItem: function (id) {
            var itemList = Espo.Utils.clone(this.fetchItemList());
            var index = -1;
            itemList.forEach(function (item, i) {
                if (item.id === id) {
                    index = i;
                }
            }, this);

            if (~index) {
                itemList.splice(index, 1);
            }
            this.model.set('itemList', itemList);
            this.calculateAmount();
        },

        calculateAmount: function () {
            var itemList = this.model.get('itemList') || [];

            var currency = this.model.get('amountCurrency');

            var amount = 0;
            itemList.forEach(function(item) {
                amount += item.amount || 0;
            }, this);

            amount = Math.round(amount * 100) / 100;
            this.model.set('amount', amount);

            var preDiscountedAmount = 0;
            itemList.forEach(function(item) {
                preDiscountedAmount += (item.listPrice || 0) * (item.quantity || 0);
            }, this);
            preDiscountedAmount = Math.round(preDiscountedAmount * 100) / 100;
            this.model.set({
                'preDiscountedAmount': preDiscountedAmount,
                'preDiscountedAmountCurrency': currency
            });

            var taxAmount = 0;
            itemList.forEach(function(item) {
                taxAmount += (item.amount || 0) * ((item.taxRate || 0) / 100.0);
            }, this);
            taxAmount = Math.round(taxAmount * 100) / 100;
            this.model.set({
                'taxAmount': taxAmount,
                'taxAmountCurrency': currency
            });

            var shippingCost = this.model.get('shippingCost') || 0;

            var discountAmount = preDiscountedAmount - amount;
            discountAmount = Math.round(discountAmount * 100) / 100;
            this.model.set({
                'discountAmount': discountAmount,
                'discountAmountCurrency': currency
            });

            var grandTotalAmount = amount + taxAmount + shippingCost;
            grandTotalAmount = Math.round(grandTotalAmount * 100) / 100;
            this.model.set({
                'grandTotalAmount': grandTotalAmount,
                'grandTotalAmountCurrency': currency
            });


        },

        reOrder: function (idList) {
            var orderedItemList = [];
            var itemList = this.model.get('itemList') || [];

            idList.forEach(function (id) {
                itemList.forEach(function (item) {
                    if (item.id === id) {
                        orderedItemList.push(item);
                    }
                }, this);
            }, this);

            this.model.set('itemList', orderedItemList);
        }

    });
});

