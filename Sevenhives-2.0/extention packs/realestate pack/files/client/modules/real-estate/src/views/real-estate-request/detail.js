

Espo.define('real-estate:views/real-estate-request/detail', 'views/detail', function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            if (['Completed', 'Canceled', 'Lost'].indexOf(this.model.get('status')) == -1) {
                this.menu.buttons.push({
                    label: 'Matching Properties',
                    name: 'matchingProperties',
                    link: '#RealEstateRequest/listMatching?id='+ this.model.id
                });
            }
        }

    });
});

