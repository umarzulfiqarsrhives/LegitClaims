

Espo.define('real-estate:views/real-estate-property/fields/name', 'views/fields/varchar', function (Dep) {

    return Dep.extend({

        listLinkTemplate: 'real-estate:real-estate-property/fields/name/list-link',

        data: function () {
            var data = Dep.prototype.data.call(this);
            if (this.model.get('interestDegree') === 0) {
                data.isNotInterested = true;
            }
            return data;
        },

        setup: function () {
            this.listenTo(this.model, 'change:interestDegree', function () {
                this.reRender();
            }, this);
        }

    });

});
