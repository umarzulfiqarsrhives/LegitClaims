

Espo.define('Advanced:Views.Record.ListForReport', 'Views.Record.List', function (Dep) {

    return Dep.extend({

        checkAllResultMassActionList: ['export'],

        export: function () {
            var data = {};
            if (this.allResultIsChecked) {
                data.id = this.options.reportId;

                if ('runtimeWhere' in this.options) {
                    data.where = this.options.runtimeWhere
                }
                if ('groupValue' in this.options) {
                    data.groupValue = this.options.groupValue
                }

                $.ajax({
                    url: 'Report/action/exportList',
                    type: 'GET',
                    data: data,
                    success: function (data) {
                        if ('id' in data) {
                            window.location = '?entryPoint=download&id=' + data.id;
                        }
                    }
                });
            } else {
                data.ids = this.checkedList;

                $.ajax({
                    url: this.scope + '/action/export',
                    type: 'GET',
                    data: data,
                    success: function (data) {
                        if ('id' in data) {
                            window.location = '?entryPoint=download&id=' + data.id;
                        }
                    }
                });
            }
        },

    });

});