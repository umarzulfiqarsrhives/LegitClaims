

Espo.define('Advanced:Views.TargetList.Record.Panels.Relationship', 'Views.Record.Panels.Relationship', function (Dep) {

    return Dep.extend({

        actionPopulateFromReport: function (data) {
            var link = data.link;

            var filterName = 'list' + Espo.Utils.upperCaseFirst(link);

            this.notify('Loading...');
            this.createView('dialog', 'Modals.SelectRecords', {
                scope: 'Report',
                multiple: false,
                createButton: false,
                primaryFilterName: filterName,
            }, function (dialog) {
                dialog.render();
                this.notify(false);
                dialog.once('select', function (selectObj) {
                    var data = {};

                    data.id = selectObj.id;
                    data.targetListId = this.model.id;

                    $.ajax({
                        url: 'Report/action/populateTargetList',
                        type: 'POST',
                        data: JSON.stringify(data),
                        success: function () {
                            this.notify('Linked', 'success');
                            this.collection.fetch();
                        }.bind(this)
                    });
                }.bind(this));
            }.bind(this));
        }

    });
});

