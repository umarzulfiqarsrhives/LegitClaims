

Espo.define('Advanced:Views.Workflow.ActionModals.MakeFollowed', ['Advanced:Views.Workflow.ActionModals.Base', 'Model'], function (Dep, Model) {

    return Dep.extend({

        template: 'advanced:workflow.action-modals.make-followed',

        data: function () {
            return _.extend({

            }, Dep.prototype.data.call(this));
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            var model = new Model();
            model.name = 'Workflow';
            model.set({
                usersToMakeToFollowIds: this.actionData.userIdList,
                usersToMakeToFollowNames: this.actionData.userNames,
                whatToFollow: this.actionData.whatToFollow
            });

            var targetOptionList = [''];
            var translatedOptions = {
                targetEntity: this.translate('Target Entity', 'labels', 'Workflow')
            };

            if (this.getMetadata().get('scopes.' + this.entityType + '.stream')) {
                targetOptionList.push('targetEntity');
            }

            var linkDefs = this.getMetadata().get('entityDefs.' + this.entityType + '.links');
            Object.keys(linkDefs).forEach(function (link) {
                var type = linkDefs[link].type;
                if (type !== 'belongsTo' && type !== 'belongsToParent') return;

                if (type === 'belongsTo') {
                    if (!this.getMetadata().get('scopes.' + linkDefs[link].entity + '.stream')) return;
                }
                targetOptionList.push(link);
                translatedOptions[link] = this.getLanguage().translate(link, 'links', this.entityType);
            }, this);

            this.createView('whatToFollow', 'Fields.Enum', {
                mode: 'edit',
                model: model,
                el: this.options.el + ' .field-whatToFollow',
                defs: {
                    name: 'whatToFollow',
                    params: {
                        options: targetOptionList,
                        required: true,
                        translatedOptions: translatedOptions
                    }
                },
                readOnly: this.readOnly
            });

            this.createView('usersToMakeToFollow', 'Fields.LinkMultiple', {
                mode: 'edit',
                model: model,
                el: this.options.el + ' .field-users-to-make-to-follow',
                foreignScope: 'User',
                defs: {
                    name: 'usersToMakeToFollow'
                },
                readOnly: this.readOnly
            });
        },


        fetch: function () {
            this.getView('whatToFollow').fetchToModel();
            if (this.getView('whatToFollow').validate()) {
                return;
            }

            this.actionData.userIdList = (this.getView('usersToMakeToFollow').fetch() || {}).usersToMakeToFollowIds;
            this.actionData.userNames = (this.getView('usersToMakeToFollow').fetch() || {}).usersToMakeToFollowNames;

            this.actionData.whatToFollow = (this.getView('whatToFollow').fetch()).whatToFollow;

            return true;
        },



    });
});
