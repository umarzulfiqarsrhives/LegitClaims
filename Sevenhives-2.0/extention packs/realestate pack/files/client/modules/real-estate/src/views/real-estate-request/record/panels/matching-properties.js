

Espo.define('real-estate:views/real-estate-request/record/panels/matching-properties', 'views/record/panels/relationship', function (Dep) {

    return Dep.extend({

        scope: 'RealEstateProperty',

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

            this.getModelFactory().create('RealEstateProperty', function (model) {
                model.id = id;
                this.listenToOnce(model, 'sync', function () {
                    this.notify(false);

                    var attributes = {};
                    attributes.propertyId = model.id;
                    attributes.propertyName = model.get('name');
                    attributes.requestId = this.model.id;
                    attributes.requestName = this.model.get('name');
                    attributes.name = attributes.propertyName + ' - ' + attributes.requestName;
                    attributes.amountCurrency = model.get('priceCurrency');

                    var markupParamName = Espo.Utils.lowerCaseFirst(this.model.get('type') || '') + 'Markup';
                    attributes.amount = model.get('price') * (this.getConfig().get(markupParamName) || 0) / 100.0;
                    attributes.amount = Math.round(attributes.amount * 100) / 100;

                    var contactIdList = this.model.get('contactsIds') || [];
                    attributes.contactsIds = contactIdList;
                    attributes.contactsNames = this.model.get('contactsNames') || {};

                    attributes.contactsColumns = {};
                    contactIdList.forEach(function (id) {
                        attributes.contactsColumns[id] = {role: 'Requester'};
                    }, this);

                    var mContactIdList = model.get('contactsIds') || [];
                    var mContactNames = model.get('contactsNames') || {};
                    var mContactColumns = model.get('contactsColumns') || {};

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
                url: 'RealEstateRequest/action/setNotInterested',
                type: 'POST',
                data: JSON.stringify({
                    propertyId: model.id,
                    requestId: this.model.id
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
                url: 'RealEstateRequest/action/unsetNotInterested',
                type: 'POST',
                data: JSON.stringify({
                    propertyId: model.id,
                    requestId: this.model.id
                })
            }).done(function () {
                model.set('interestDegree', null);
            });
        },

        actionListMatching: function () {
            this.getRouter().navigate('#RealEstateRequest/listMatching?id=' + this.model.id, {trigger: true});
        }

    });

});
