

Espo.define('Advanced:Views.Report.Record.Edit', ['Views.Record.Edit', 'Advanced:Views.Report.Record.Detail'], function (Dep, Detail) {

    return Dep.extend({

        setup: function () {
            if (!this.model.get('type')) {
                throw new Error();
            }
            if (this.model.get('isInternal')) {
                this.layoutName = 'detail';
            } else {
                this.layoutName = 'detail' + this.model.get('type');
            }

            if (this.model.get('type') == 'List' && this.model.isNew() && !this.model.has('columns')) {
                if (this.getMetadata().get('entityDefs.' + this.model.get('entityType') + '.fields.name')) {
                    this.model.set('columns', ['name']);
                }
            }

            Dep.prototype.setup.call(this);

        },
        
        afterRender: function () {
            this.handleDoNotSendEmptyReportVisibility();
            this.listenTo(this.model, 'change:emailSendingInterval', function () {
                this.handleDoNotSendEmptyReportVisibility();
            }, this);
        },

        handleDoNotSendEmptyReportVisibility: function() {
            var fieldName = "emailSendingDoNotSendEmptyReport";
            if (this.model.get('type') == 'List') {
                if (this.model.get("emailSendingInterval") == "") {
                    this.hideField(fieldName);
                } else {
                    this.showField(fieldName);
                }
            }  else {
                this.hideField(fieldName);
            }
        },

    });

});

