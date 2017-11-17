Espo.define('crm:views/lead/record/list', 'views/record/list', function (Dep) {
    return Dep.extend({

        setup: function () {
            exportCustom = this;
            Dep.prototype.setup.call(this);
            this.buttonList.push({
                name: 'export_all',
                label: 'Expport All Data',
                style: 'success'
            });
        },


        events: {
            'click a.link': function (e) {
                e.stopPropagation();
                if (!this.scope || this.selectable ) {
                    return;
                }
                e.preventDefault();
                var id = $(e.currentTarget).data('id');
                var model = this.collection.get(id);

                var scope = this.getModelScope(id);

                this.getRouter().navigate('#' + scope + '/view/' + id, {trigger: false});
                this.getRouter().dispatch(scope, 'view', {
                    id: id,
                    model: model
                });
            },
            'click [data-action="showMore"]': function () {
                this.showMoreRecords();
            },
            'click a.sort': function (e) {
                var field = $(e.currentTarget).data('name');

                var asc = true;
                if (field === this.collection.sortBy && this.collection.asc) {
                    asc = false;
                }
                this.notify('Please wait...');
                this.collection.once('sync', function () {
                    this.notify(false);
                    this.trigger('sort', {sortBy: field, asc: asc});
                }, this);
                this.collection.sort(field, asc);
                this.deactivate();
            },
            'click .pagination a': function (e) {
                var page = $(e.currentTarget).data('page');
                if ($(e.currentTarget).parent().hasClass('disabled')) {
                    return;
                }
                this.notify('Please wait...');
                this.collection.once('sync', function () {
                    this.notify(false);
                }.bind(this));

                if (page == 'current') {
                    this.collection.fetch();
                } else {
                    this.collection[page + 'Page'].call(this.collection);
                    this.trigger('paginate');
                }
                this.deactivate();
            },

            'click .record-checkbox': function (e) {
                var $target = $(e.currentTarget);
                var id = $target.data('id');
                if (e.currentTarget.checked) {
                    this.checkRecord(id, $target);
                } else {
                    this.uncheckRecord(id, $target);
                }
            },
            'click .select-all': function (e) {
                this.checkedList = [];

                if (e.currentTarget.checked) {
                    this.$el.find('input.record-checkbox').prop('checked', true);
                    this.$el.find('.actions-button').removeAttr('disabled');
                    this.collection.models.forEach(function (model) {
                        this.checkedList.push(model.id);
                    }, this);

                    this.$el.find('.list > table tbody tr').addClass('active');
                } else {
                    if (this.allResultIsChecked) {
                        this.unselectAllResult();
                    }
                    this.$el.find('input.record-checkbox').prop('checked', false);
                    this.$el.find('.actions-button').attr('disabled', true);
                    this.$el.find('.list > table tbody tr').removeClass('active');
                }
            },
            'click .action': function (e) {

                var $el = $(e.currentTarget);
                var action = $el.data('action');
                var method = 'action' + Espo.Utils.upperCaseFirst(action);
                if (typeof this[method] == 'function') {
                    var data = $el.data();
                    this[method](data, e);
                    e.preventDefault();
                }
            },
            'click .checkbox-dropdown [data-action="selectAllResult"]': function (e) {
                this.selectAllResult();
            },
            'click .actions a.mass-action': function (e) {
                $el = $(e.currentTarget);
                if($el.data('action') != 'Expport All Data'){
                    var action = $el.data('action');
                    var method = 'massAction' + Espo.Utils.upperCaseFirst(action);
                    if (method in this) {
                        this[method]();
                    } else {
                        this.massAction(action);
                    }
                }else {
                    this.exportAllRecord();
                }

            },

            'click button[data-action="export_all"]': function (e) {
                customData = this;
                moduleName = this.scope;

                // this.$el.find('button[data-action="export_all"]').addClass('disabled');
                // this.notify('Data Downloaded Successfully...', 'success');

                // this.notify(this.translate('pleaseWait', 'messages'));
                this.exportAllRecord(moduleName);
                // this.notify('Data Exported...');
                // Espo.Ui.notify(false);

            },
        },

        exportAllRecord:function (moduleName) {
            $.ajax({
                url: moduleName,
                type: 'get',
                async: false,
                success: function (response) {
                    var xpData = [];
                    var countData = 0;
                    var dataValueArray = [];
                    var dataValue = [];
                    var temp = [];
                    $(response.list).each(function (index, value) {
                        countData = countData + 1;
                            $(value).each(function (index1, value1) {

                                dataValue.push($.trim(value1.name).replace(/[\n]+/g,' '));
                                dataValue.push($.trim(value1.firstName).replace(/[\n]+/g,' '));
                                dataValue.push($.trim(value1.lastName).replace(/[\n]+/g,' '));
                                dataValue.push($.trim(value1.emailAddress).replace(/[\n]+/g,' '));
                                dataValue.push($.trim(value1.assignedUserId).replace(/[\n]+/g,' '));
                                dataValue.push($.trim(value1.assignedUserName).replace(/[\n]+/g,' '));
                                dataValue.push($.trim(value1.phoneNumber).replace(/[\n]+/g,' '));
                                dataValue.push($.trim(value1.status).replace(/[\n]+/g,' '));
                                dataValue.push($.trim(value1.website));
                                dataValue.push($.trim(value1.title).replace(/[\n]+/g,' '));
                                dataValue.push($.trim(value1.accountName).replace(/[\n]+/g,' '));
                                dataValue.push($.trim(value1.addressCity).replace(/[\n]+/g,' '));
                                dataValue.push($.trim(value1.addressCountry).replace(/[\n]+/g,' '));
                                dataValue.push($.trim(value1.addressPostalCode).replace(/[\n]+/g,' '));
                                dataValue.push($.trim(value1.addressState).replace(/[\n]+/g,' '));
                                dataValue.push($.trim(value1.addressStreet).replace(/[\n]+/g,' '));
                                dataValue.push($.trim(value1.areaPractice).replace(/[\n]+/g,' '));
                                dataValue.push($.trim(value1.createdAt).replace(/[\n]+/g,' '));
                            });
                        dataValueArray.push(dataValue);
                        temp.push(dataValue);
                            dataValue = [];
                         if(countData % 3000 == 0 ) {
                            temp = [];
                             xpData.push(dataValueArray);
                            dataValueArray = [];
                        }

                    });
                    xpData.push(temp);
                    // console.log(xpData);
                    var heading = [];
                    heading.push('name');
                    heading.push('FirstName');
                    heading.push('LastName');
                    heading.push('EmailAddress');
                    heading.push('AssignedUserId');
                    heading.push('AssignedUserName');
                    heading.push('PhoneNumber');
                    heading.push('Status');
                    heading.push('Website');
                    heading.push('Title');
                    heading.push('AaccountName');
                    heading.push('AddressCity');
                    heading.push('AddressCountry');
                    heading.push('AddressPostalCode');
                    heading.push('AddressState');
                    heading.push('AddressStreet');
                    heading.push('AreaPractice');
                    heading.push('CreatedAt');

                    dataString = heading.join(",");
                    /*generate file*/
                    $(xpData).each(function (ArrayIndex, dataArray) {
                        var header = '';
                        var csvContent = "data:text/csv;charset=utf-8,";
                        dataArray.forEach(function(infoArray, index ){
                            /*add heading*/
                            if(index == 0){
                                csvContent += heading.join(",");
                            }
                            dataString = infoArray.join(",");
                            // csvContent += index < dataValueArray.length ? dataString+ "\n" : dataString;
                            csvContent += dataString+ "\n";
                        });

                        var encodedUri = encodeURI(csvContent);
                        var link = document.createElement("a");
                        link.setAttribute("href", encodedUri);
                        link.setAttribute("download", "my_data.csv");
                        document.body.appendChild(link); // Required for FF
                        link.click();

                    });

                }
            });

        }

    });
});

