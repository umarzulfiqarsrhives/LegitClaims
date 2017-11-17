

Espo.define('real-estate:views/real-estate-request/record/panels/opportunities', 'views/record/panels/relationship', function (Dep) {

    return Dep.extend({

        filterList: ['all', 'open', 'won', 'lost']

    });

});
