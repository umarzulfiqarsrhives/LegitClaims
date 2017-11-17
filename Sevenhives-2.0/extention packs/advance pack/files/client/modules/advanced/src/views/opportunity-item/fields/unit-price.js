

Espo.define('Advanced:Views.OpportunityItem.Fields.UnitPrice', 'Views.Fields.Currency', function (Dep) {

    return Dep.extend({

        editTemplate: 'fields.float.edit',

        fetch: function () {
            var value = this.$element.val();
            value = this.parse(value);
            var data = {};
            data[this.name] = value;
            return data;
        },

    });
});

