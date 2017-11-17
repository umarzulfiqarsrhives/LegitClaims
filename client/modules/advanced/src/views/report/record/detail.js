

Espo.define('Advanced:Views.Report.Record.Detail', 'Views.Record.Detail', function (Dep) {

    return Dep.extend({

        editModeDisabled: true,

        duplicateAction: true,

        bottomView: "Advanced:Report.Record.DetailBottom",

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
