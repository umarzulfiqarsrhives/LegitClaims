

 Espo.define('Advanced:Views.Report.Fields.EmailSendingMonth', 'Views.Fields.Enum', function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);
            this.translatedOptions = {};
            var monthNames = this.translate('monthNames', 'lists');
            for (i = 0; i < monthNames.length; i++) {
                this.translatedOptions[i+1] = monthNames[i];
            }
        },
    });
});
