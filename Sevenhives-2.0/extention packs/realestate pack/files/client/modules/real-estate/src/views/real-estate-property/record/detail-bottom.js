

Espo.define('real-estate:views/real-estate-property/record/detail-bottom', 'views/record/detail-bottom', function (Dep) {

    return Dep.extend({

        setupPanels: function () {
            Dep.prototype.setupPanels.call(this);

            this.panelList.push({
                name: 'matchingRequests',
                label: 'Matching Requests',
                view: 'real-estate:views/real-estate-property/record/panels/matching-requests',
                hidden: !this.isActive(),
                create: false,
                select: false,
                rowActionsView: 'real-estate:views/real-estate-property/record/row-actions/matching-requests',
                layout: 'listForProperty',
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
                        parentView.showPanel('matchingRequests');
                    } else {
                        parentView.hidePanel('matchingRequests');
                    }
                }
            }, this);

        },

        isActive: function () {
            return !~['Completed', 'Lost', 'Canceled'].indexOf(this.model.get('status'))
        }

    });

});
