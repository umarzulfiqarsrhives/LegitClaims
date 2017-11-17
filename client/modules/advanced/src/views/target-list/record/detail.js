

Espo.define('Advanced:Views.TargetList.Record.Detail', 'Views.Record.Detail', function (Dep) {

    return Dep.extend({

        getMailChimpButton: function () {
            return this.$el.parent().find(".header-buttons .btn[data-name='mailChimpButton']");
        },

        handleMailChimpButtonStyle: function () {
            if (this.model.get('mailChimpListId') == null) {
                this.getMailChimpButton().addClass('btn-danger');
            } else {
                this.getMailChimpButton().removeClass('btn-danger');
            }
        },

        afterRender: function () {
        	Dep.prototype.afterRender.call(this);

            this.handleMailChimpButtonStyle();
            this.listenTo(this.model, 'sync', function () {
                this.handleMailChimpButtonStyle();
            }, this);
        },

    });
});


