

Espo.define('real-estate:views/admin/real-estate-settings', 'views/settings/record/edit', function (Dep) {

    return Dep.extend({

        detailLayout: [
            {
                label: '',
                rows: [
                    [
                        {name: "saleMarkup"},
                        {name: "rentMarkup"},
                    ]
                ]
            }
        ],


        setup: function () {
            Dep.prototype.setup.call(this);
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);
        }

    });

});

