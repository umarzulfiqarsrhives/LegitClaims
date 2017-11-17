

Espo.define('real-estate:views/dashlets/requests', 'views/dashlets/abstract/record-list', function (Dep) {

    return Dep.extend({

        name: 'Requests',

        scope: 'RealEstateRequest',

        defaultOptions: {
            sortBy: 'createdAt',
            asc: false,
            displayRecords: 5,
            expandedLayout: {
                rows: [
                    [
                        {
                            name: 'name',
                            link: true,
                        },

                    ],
                    [
                        {
                            name: 'status'
                        },
                        {
                            name: 'type'
                        },
                        {
                            name: 'propertyType'
                        }
                    ]
                ]
            },
            searchData: {
                bool: {
                    onlyMy: true
                },
                primary: 'actual'
            },
        },

    });
});


