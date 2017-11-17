

Espo.define('Advanced:Views.Google.Modals.SelectCalendar', 'Views.Modal', function (Dep) {

    return Dep.extend({

        cssName: 'select-folder-modal',

        template: 'advanced:google.modals.select-calendar',

        data: function () {
            return {
                calendars: this.options.calendars,
            };
        },

        events: {
            'click button[data-action="select"]': function (e) {
                var value = $(e.currentTarget).data('value');
                this.trigger('select', value);
            },
        },

        setup: function () {
            this.buttonList = [
                {
                    name: 'cancel',
                    label: 'Cancel'
                }
            ];

        },

    });
});

