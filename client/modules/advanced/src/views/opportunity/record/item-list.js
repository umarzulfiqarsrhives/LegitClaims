

Espo.define('Advanced:Views.Opportunity.Record.ItemList', ['Views.Base', 'Collection'], function (Dep, Collection) {

    return Dep.extend({

        template: 'advanced:opportunity.record.item-list',

        data: function () {
            return {
                itemDataList: this.itemDataList,
                mode: this.mode
            };
        },

        setup: function () {
            this.mode = this.options.mode;
            this.itemDataList = [];

            var itemList = this.model.get('itemList') || [];

            this.collection = new Collection();

            itemList.forEach(function (item, i) {
                var id = item.id || 'cid' + i;
                this.itemDataList.push({
                    num: i,
                    key: 'item-' + i,
                    id: id
                });
                this.getModelFactory().create('OpportunityItem', function (model) {
                    model.set(item);
                    this.collection.push(model);
                    this.createView('item-' + i, 'Advanced:Opportunity.Record.Item', {
                        el: this.options.el + ' .item-container-' + id,
                        model: model,
                        mode: this.mode
                    }, function (view) {
                        this.listenTo(view, 'change', function () {
                            this.trigger('change');
                        }, this);
                    }.bind(this));
                }, this);

            }, this);
        },

        fetch: function () {
            var itemList = [];
            this.itemDataList.forEach(function (item) {
                var data = this.getView(item.key).fetch();
                itemList.push(data);
            }, this);
            return {
                itemList: itemList
            };
        },

    });
});

