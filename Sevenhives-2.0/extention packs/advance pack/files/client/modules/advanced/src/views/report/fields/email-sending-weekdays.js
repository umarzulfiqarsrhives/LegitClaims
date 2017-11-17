

 Espo.define('Advanced:Views.Report.Fields.EmailSendingWeekdays', 'Views.Fields.Base', function (Dep) {

    return Dep.extend({

        editTemplate: 'advanced:report.fields.email-sending-weekdays.edit',

        detailTemplate: 'advanced:report.fields.email-sending-weekdays.detail',

        data: function () {
            var weekday = this.model.get(this.name) || '';
            var weekdays = {};
            for (i = 0; i < 7; i++) {
                weekdays[i] = (weekday.indexOf(i.toString())) > -1 || false;
            }
            return _.extend({
                selectedWeekdays: weekdays,
                days: this.translate('dayNamesShort', 'lists')
            }, Dep.prototype.data.call(this));
        },

        fetch: function () {
            var data = {};
            var value = '';
            this.$element.each(function(i){
                if ($(this).is(':checked')) {
                    value += $(this).val();
                }
            });
            data[this.name] = value;
            return data;
        },

    });
});
