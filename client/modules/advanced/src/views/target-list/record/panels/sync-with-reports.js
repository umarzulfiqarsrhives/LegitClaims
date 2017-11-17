

Espo.define('Advanced:Views.TargetList.Record.Panels.SyncWithReports', 'Views.Record.Panels.Side', function (Dep) {

    return Dep.extend({

        fieldList: [
            'syncWithReportsEnabled',
            'syncWithReports',
            'syncWithReportsUnlink'
        ],

        actionList: [
          {
            "name": "syncWithReport",
            "label": "Sync Now",
            "acl": "edit",
            "action": "syncWithReports"
          }
        ],

        setup: function () {
            Dep.prototype.setup.call(this);
        },

        actionSyncWithReports: function () {
            this.notify('Please wait...');
            $.ajax({
                url: 'Report/action/syncTargetListWithReports',
                type: 'Post',
                data: JSON.stringify({
                    targetListId: this.model.id
                })
            }).done(function () {
                this.notify('Done', 'success');
            }.bind(this));

        },
    });
});

