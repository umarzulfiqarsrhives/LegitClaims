

Espo.define('real-estate:views/dashlets/properties', 'views/dashlets/abstract/record-list', function (Dep) {

    return Dep.extend({

        name: 'Properties',

        scope: 'RealEstateProperty',

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
                            name: 'requestType'
                        },
                        {
                            name: 'type'
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


