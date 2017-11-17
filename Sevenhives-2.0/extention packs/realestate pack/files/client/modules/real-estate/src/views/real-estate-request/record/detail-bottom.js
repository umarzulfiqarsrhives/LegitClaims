

Espo.define('real-estate:views/real-estate-request/record/detail-bottom', 'views/record/detail-bottom', function (Dep) {

    return Dep.extend({

        setupPanels: function () {
            Dep.prototype.setupPanels.call(this);

            this.panelList.push({
                name: 'matchingProperties',
                label: 'Matching Properties',
                view: 'real-estate:views/real-estate-request/record/panels/matching-properties',
                hidden: !this.isActive(),
                create: false,
                select: false,
                rowActionsView: 'real-estate:views/real-estate-request/record/row-actions/matching-properties',
                layout: 'listForRequest',
                actionList: [{
                    name: 'listMatching',
                    label: 'List',
                    action: 'listMatching'
                }]
            });

            this.listenTo(this.model, 'change:status', function () {
                if (this.isRendered()) {
                    var parentView = this.getParentView();
                    if (this.isActive()) {
                        parentView.showPanel('matchingProperties');
                    } else {
                        parentView.hidePanel('matchingProperties');
                    }
                }
            }, this);

        },

        isActive: function () {
            return !~['Completed', 'Lost', 'Canceled'].indexOf(this.model.get('status'))
        }

    });

});
