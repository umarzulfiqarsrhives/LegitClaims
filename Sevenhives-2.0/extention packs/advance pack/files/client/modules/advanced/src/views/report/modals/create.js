Espo.define('Advanced:Views.Report.Modals.Create', 'Views.Modal', function (Dep) {

    return Dep.extend({

        cssName: 'create-report',

        template: 'advanced:report.modals.create',

        data: function () {
            return {
                entityTypeList: this.entityTypeList,
                typeList: this.typeList
            };
        },

        events: {
            'click [data-action="create"]': function (e) {
                var type = $(e.currentTarget).data('type');
                var entityType = this.$el.find('[name="entityType"]').val();
                if (!entityType) {
                    var message = this.translate('fieldIsRequired', 'messages').replace('{field}', this.translate('entityType', 'fields', 'Report'));

                    var $el = this.$el.find('[name="entityType"]');
                    $el.popover({
                        placement: 'bottom',
                        container: 'body',
                        content: message,
                        trigger: 'manual',
                    }).popover('show');

                    $el.closest('.cell').addClass('has-error');

                    $el.closest('.field').one('mousedown click', function () {
                        $el.popover('destroy');
                        $el.closest('.cell').removeClass('has-error');
                    });


                    if (this._timeout) {
                        clearTimeout(this._timeout);
                    }

                    this._timeout = setTimeout(function () {
                        $el.popover('destroy');
                        $el.closest('.cell').removeClass('has-error');
                    }, 3000);
                    return;
                }

                this.trigger('create', {
                    type: type,
                    entityType: entityType
                });
            }
        },

        setup: function () {
            this.buttonList = [
                {
                    name: 'cancel',
                    label: 'Cancel',
                    onClick: function (dialog) {
                        dialog.close();
                    }
                }
            ];

            this.typeList = this.getMetadata().get('entityDefs.Report.fields.type.options');

            var scopes = this.getMetadata().get('scopes');
            var entityListToIgnore = this.getMetadata().get('entityDefs.Report.entityListToIgnore') || [];
            this.entityTypeList = Object.keys(scopes).filter(function (scope) {
                if (~entityListToIgnore.indexOf(scope)) {
                    return;
                }
                if (!this.getAcl().check(scope, 'read')) {
                    return;
                }
                var defs = scopes[scope];
                return (defs.entity && (defs.tab || defs.object));
            }, this).sort(function (v1, v2) {
                 return this.translate(v1, 'scopeNamesPlural').localeCompare(this.translate(v2, 'scopeNamesPlural'));
            }.bind(this));

            this.entityTypeList.unshift('');

            this.header = this.translate('Create Report', 'labels', 'Report');
        },

    });
});

