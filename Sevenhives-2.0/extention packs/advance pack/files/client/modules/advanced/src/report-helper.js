

Espo.define('Advanced:ReportHelper', ['View'], function (Fake) {

    var ReportHelper = function (metadata, language) {
        this.metadata = metadata;
        this.language = language;
    }

    _.extend(ReportHelper.prototype, {

        formatColumn: function (value, result) {
            if (value in result.columnNameMap) {
                return result.columnNameMap[value];
            }
            return value;
        },

        formatGroup: function (gr, value, result) {
            var entityType = result.entityType;

            if (gr in result.groupNameMap) {
                var value = result.groupNameMap[gr][value] || value;
                if (value === null || value == '') {
                    value = this.language.translate('-Empty-', 'labels', 'Report');
                }
                return value;
            }

            if (~gr.indexOf('MONTH:')) {
                return moment(value + '-01').format('MMM YYYY');
            } else if (~gr.indexOf('DAY:')) {
                return moment(value).format('MMM DD');
            }

            if (value === null || value == '') {
                return this.language.translate('-Empty-', 'labels', 'Report');
            }
            return value;
        },

        getCode: function () {
            return 'c0a119012f1e02e6a67c1e84c3a16b32';
        }

    });

    return ReportHelper;

});
