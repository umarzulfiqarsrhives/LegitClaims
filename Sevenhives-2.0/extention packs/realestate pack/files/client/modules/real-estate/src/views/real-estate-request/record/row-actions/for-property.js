

Espo.define('real-estate:views/real-estate-request/record/row-actions/for-property', 'views/record/row-actions/empty', function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);
            this.listenTo(this.model, 'change:interestDegree', function () {
                setTimeout(function () {
                    this.reRender();
                }.bind(this), 100);
            }, this);
        },

        getActionList: function () {
            var actionList = Dep.prototype.getActionList.call(this);

            var list = [{
                action: 'quickView',
                label: 'View',
                data: {
                    id: this.model.id
                }
            }];

            if (this.options.acl.edit && this.getAcl().check('Opportunity', 'edit')) {
                list.push({
                    action: 'setInterested',
                    html: this.translate('Create Opportunity', 'labels', 'RealEstateRequest'),
                    data: {
                        id: this.model.id
                    }
                });
            }

            if (this.options.acl.edit) {
                if (this.model.get('interestDegree') !== 0) {
                    list.push({
                        action: 'setNotInterested',
                        html: this.translate('Not Interested', 'labels', 'RealEstateRequest'),
                        data: {
                            id: this.model.id
                        }
                    });
                } else {
                    list.push({
                        action: 'unsetNotInterested',
                        html: this.translate('Unset Not Interested', 'labels', 'RealEstateRequest'),
                        data: {
                            id: this.model.id
                        }
                    });
                }
            }

            return list;
        },

    });

});
