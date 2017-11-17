

Espo.define('real-estate:views/real-estate-property/record/panels/matching-requests', 'views/record/panels/relationship', function (Dep) {

    return Dep.extend({

        scope: 'RealEstateRequest',

        setup: function () {
            Dep.prototype.setup.call(this);

            this.listenTo(this.model, 'sync', function () {
                this.collection.fetch();
            }, this);

        },

        actionSetInterested: function (data) {
            var id = data.id;

            var model = this.collection.get(id);
            if (!model) return;

            this.notify('Please wait...');

            this.getModelFactory().create('RealEstateRequest', function (model) {
                model.id = id;

                this.listenToOnce(model, 'sync', function () {
                    this.notify(false);


                    var attributes = {};
                    attributes.propertyId = this.model.id;
                    attributes.propertyName = this.model.get('name');
                    attributes.requestId = model.id;
                    attributes.requestName = model.get('name');
                    attributes.name = attributes.propertyName + ' - ' + attributes.requestName;
                    attributes.amountCurrency = this.model.get('priceCurrency');

                    var markupParamName = Espo.Utils.lowerCaseFirst(this.model.get('requestType') || '') + 'Markup';
                    attributes.amount = this.model.get('price') * (this.getConfig().get(markupParamName) || 0) / 100.0;
                    attributes.amount = Math.round(attributes.amount * 100) / 100;

                    var contactIdList = model.get('contactsIds') || [];
                    attributes.contactsIds = contactIdList;
                    attributes.contactsNames = model.get('contactsNames') || {};

                    attributes.contactsColumns = {};
                    contactIdList.forEach(function (id) {
                        attributes.contactsColumns[id] = {role: 'Requester'};
                    }, this);

                    var mContactIdList = this.model.get('contactsIds') || [];
                    var mContactNames = this.model.get('contactsNames') || {};
                    var mContactColumns = this.model.get('contactsColumns') || {};

                    mContactIdList.forEach(function (id) {
                        attributes.contactsIds.push(id);
                        attributes.contactsNames[id] = mContactNames[id] || 'Unknown';
                        attributes.contactsColumns[id] = {role: (mContactColumns[id] || {}).role || null};
                    }, this);

                    this.createView('modal', 'views/modals/edit', {
                        scope: 'Opportunity',
                        attributes: attributes
                    }, function (view) {
                        view.render();

                        this.listenTo(view, 'after:save', function () {
                            this.collection.fetch();

                            if (this.getParentView() && this.getParentView().getView('opportunities')) {
                                this.getParentView().getView('opportunities').actionRefresh();
                            }
                        }, this);
                    }, this);

                }, this);

                model.fetch();

            }, this);
        },

        actionSetNotInterested: function (data) {
            var id = data.id;

            var model = this.collection.get(id);
            if (!model) return;

            model.set('interestDegree', 0);

            $.ajax({
                url: 'RealEstateProperty/action/setNotInterested',
                type: 'POST',
                data: JSON.stringify({
                    propertyId: this.model.id,
                    requestId: model.id
                })
            }).done(function () {
                model.set('interestDegree', 0);
            });
        },

        actionUnsetNotInterested: function (data) {
            var id = data.id;

            var model = this.collection.get(id);
            if (!model) return;

            model.set('interestDegree', null);

            $.ajax({
                url: 'RealEstateProperty/action/unsetNotInterested',
                type: 'POST',
                data: JSON.stringify({
                    propertyId: this.model.id,
                    requestId: model.id
                })
            }).done(function () {
                model.set('interestDegree', null);
            });
        },

        actionListMatching: function () {
            this.getRouter().navigate('#RealEstateProperty/listMatching?id=' + this.model.id, {trigger: true});
        }

    });

});
