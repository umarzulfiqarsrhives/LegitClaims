

Espo.define('Advanced:Views.Workflow.Actions.MakeFollowed', ['Advanced:Views.Workflow.Actions.Base', 'Model'], function (Dep, Model) {

    return Dep.extend({

        type: 'makeFollowed',

        template: 'advanced:workflow.actions.make-followed',

        setup: function () {
            Dep.prototype.setup.call(this);
        },

        afterRender: function () {

            var model = new Model();
            model.name = 'Workflow';
            model.set({
                usersToMakeToFollowIds: this.actionData.userIdList,
                usersToMakeToFollowNames: this.actionData.userNames,
                whatToFollow: this.actionData.whatToFollow
            });


            var translatedOptions = {
                targetEntity: this.translate('Target Entity', 'labels', 'Workflow')
            };
            var linkDefs = this.getMetadata().get('entityDefs.' + this.entityType + '.links');
            Object.keys(linkDefs).forEach(function (link) {
                var type = linkDefs[link].type;
                if (type !== 'belongsTo' && type !== 'belongsToParent') return;

                if (type === 'belongsTo') {
                    if (!this.getMetadata().get('scopes.' + linkDefs[link].entity + '.stream')) return;
                }
                translatedOptions[link] = this.getLanguage().translate(link, 'links', this.entityType);
            }, this);


            this.createView('whatToFollow', 'Fields.Enum', {
                mode: 'detail',
                model: model,
                el: this.options.el + ' .field-whatToFollow',
                defs: {
                    name: 'whatToFollow',
                    params: {
                        translatedOptions: translatedOptions
                    }
                },
                readOnly: true
            }, function (view) {
                view.render();
            });

            this.createView('usersToMakeToFollow', 'Fields.LinkMultiple', {
                mode: 'detail',
                model: model,
                el: this.options.el + ' .field-users-to-make-to-follow',
                foreignScope: 'User',
                defs: {
                    name: 'usersToMakeToFollow'
                },
                readOnly: true
            }, function (view) {
                view.render();
            });

        },

    });
});

