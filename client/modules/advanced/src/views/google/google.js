

Espo.define('Advanced:Views.Google.Google', ['Views.ExternalAccount.OAuth2', 'Model'], function (Dep, Model) {

	return Dep.extend({

		template: 'advanced:google.google',

		data: function () {
			return {
				integration: this.integration,
				helpText: this.helpText,
				isConnected: this.isConnected,
				fields: this.fieldList
			};
		},

		calendarList: [],
		isConnected: false,

		events: {
			'click button[data-action="cancel"]': function () {
				this.getRouter().navigate('#ExternalAccount', {trigger: true});
			},
			'click button[data-action="save"]': function () {
				this.save();
			},
			'click [data-action="connect"]': function () {
				this.connect();
			}
		},

		setup: function () {
			this.integration = this.options.integration;
			this.id = this.options.id;


			this.helpText = false;
			if (this.getLanguage().has(this.integration, 'help', 'ExternalAccount')) {
				this.helpText = this.translate(this.integration, 'help', 'ExternalAccount');
			}

			this.fieldList = [];

			this.dataFieldList = [];

			this.model = new Model();
			this.model.id = this.id;
			this.model.name = 'ExternalAccount';
			this.model.urlRoot = 'ExternalAccount';

			this.model.defs = {
				fields: {
					enabled: {
						type: 'bool'
					},

					calendarDirection: {
					    type: 'enum',
						options: ["EspoToGC","GCToEspo","Both"],
						default: 'Both',

					},

					calendarStartDate: {
						required: true,
						type: 'date'
					},

					calendarEntityTypes: {
					    type: 'base',
						view: 'Advanced:Google.Fields.LabeledArray',
						options: ["Call","Meeting"],
						default: ["Call","Meeting"],
						tooltip: true,
						required: true,
					},

					calendarDefaultEntity: {
					    type: 'enum',
						options: ["Call", "Meeting"],
						default: "Meeting",
						tooltip: true,
					},

					calendarMainCalendar: {
					    type: 'base',
						view: 'Advanced:Google.Fields.MainCalendar',
						required: true,
					},

					calendarMonitoredCalendars: {
					    type: 'base',
						view: 'Advanced:Google.Fields.MonitoredCalendars',
					},
				}
			};

			this.wait(true);

			this.model.populateDefaults();

			for(i in this.model.defs.fields) {
    		    this.createFieldView(this.model.defs.fields[i].type, this.model.defs.fields[i].view || null, i, false);
		    }


			this.listenToOnce(this.model, 'sync', function () {

				$.ajax({
					url: 'ExternalAccount/action/getOAuth2Info?id=' + this.id,
					dataType: 'json'
				}).done(function (respose) {
					this.clientId = respose.clientId;
					this.redirectUri = respose.redirectUri;
					if (respose.isConnected) {
						this.isConnected = true;
						this.loadCalendars();
					}
					this.wait(false);
				}.bind(this));

			}, this);
			this.model.fetch();
		},

		loadCalendars: function () {

		    $.ajax({
                type: 'GET',
                url: 'GoogleCalendar/action/usersCalendars',
                error: function (xhr) {
                 //   Espo.Ui.error(self.translate('couldNotConnectToImap', 'messages', 'InboundEmail'));
                    xhr.errorIsHandled = true;
                },
            }).done(function (calendars) {
                this.model.calendarList = calendars;
                this.checkCalendars();
            }.bind(this));
		},

		checkCalendars: function () {

		    var mainCalendar = this.model.get('calendarMainCalendarId');

            if (!(mainCalendar in this.model.calendarList)) {
                this.model.set('calendarMainCalendarId','');
                this.model.set('calendarMainCalendarName','');
                this.getView('calendarMainCalendar').render();
            }

            var monitoredCalendars = this.model.get('calendarMonitoredCalendarsIds') || [];
            var monitoredCalendarsNames = this.model.get('calendarMonitoredCalendarsNames') || [];
            var render = false;

            for (key in monitoredCalendars) {
                if (!(monitoredCalendars[key] in this.model.calendarList)) {
                    delete monitoredCalendarsNames[monitoredCalendars[key]];
                    monitoredCalendars.splice(key, 1);
                    render = true;
                }
            }
            if (monitoredCalendars.length == 0) {
                render = true;
            }
            if (render) {
                this.model.set('calendarMonitoredCalendarsIds', monitoredCalendars);
                this.model.set('calendarMonitoredCalendarsNames',monitoredCalendarsNames);

                this.getView('calendarMonitoredCalendars').render();
            }

		},

		afterRender: function () {
			if (!this.model.get('enabled')) {
				this.$el.find('.data-panel').addClass('hidden');
			}

			if (this.isConnected) {
			    this.$el.find('.data-panel-connected').removeClass('hidden'); 
			} else {
			    this.$el.find('.data-panel-connected').addClass('hidden'); 
			}
			this.showCalendarFields();

			this.listenTo(this.model, 'change:enabled', function () {
				if (this.model.get('enabled')) {
					this.$el.find('.data-panel').removeClass('hidden');
				} else {
					this.$el.find('.data-panel').addClass('hidden');
				}
			}, this);

			this.listenTo(this.model, 'change:calendarDirection', function () {
				this.showCalendarFields();
			}, this);


		},

		showCalendarFields: function() {
		    var calendarDirection = this.model.get('calendarDirection');

		    switch (calendarDirection) {
			    case 'EspoToGC':
			        this.hideField('calendarMonitoredCalendars');
			        this.hideField('calendarDefaultEntity');
			        break;
			    case 'GCToEspo':
			        this.showField('calendarMonitoredCalendars');
			        this.showField('calendarDefaultEntity');
			        break;
			    case 'Both':
			        this.showField('calendarMonitoredCalendars');
			        this.showField('calendarDefaultEntity');
			        break;
			    default:
			        this.hideField('calendarMonitoredCalendars');
			        this.hideField('calendarDefaultEntity');
			}

		},

		createFieldView: function (type, view, name, readOnly, params) {
		    var fieldView = view || this.getFieldManager().getViewName(type);
			this.createView(name, fieldView, {
				model: this.model,
				el: this.options.el + ' .field-' + name,
				defs: {
					name: name,
					params: params
				},
				mode: readOnly ? 'detail' : 'edit',
				readOnly: readOnly,
			});
			this.fieldList.push(name);
		},

		save: function () {
		    this.model.unset("accessToken");
		    this.model.unset("refreshToken");
		    this.model.unset("tokenType");


			this.fieldList.forEach(function (field) {
				var view = this.getView(field);
				if (!view.readOnly) {
					view.fetchToModel();
				}
			}, this);
			var notValid = false;
			if (this.model.get('enabled')) {
			    this.fieldList.forEach(function (field) {
				    notValid = this.getView(field).validate() || notValid;
			    }, this);
			    notValid |= this.validate();
			}
			if (notValid) {
				this.notify('Not valid', 'error');
				return;
			}
			this.listenToOnce(this.model, 'sync', function () {
				this.notify('Saved', 'success');
				if (!this.model.get('enabled')) {
					this.setNotConnected();
				}
			}, this);
			this.notify('Saving...');
			this.model.save();
		},

		popup: function (options, callback) {
			options.windowName = options.windowName ||  'ConnectWithOAuth';
			options.windowOptions = options.windowOptions || 'location=0,status=0,width=800,height=400';
			options.callback = options.callback || function(){ window.location.reload(); };

			var self = this;

			var path = options.path;

			var arr = [];
			var params = (options.params || {});
			for (var name in params) {
				if (params[name]) {
					arr.push(name + '=' + encodeURI(params[name]));
				}
			}
			path += '?' + arr.join('&');

			var parseUrl = function (str) {
				var code = null;
				var error = null;

				str = str.substr(str.indexOf('?') + 1, str.length);
				str.split('&').forEach(function (part) {
					var arr = part.split('=');
					var name = decodeURI(arr[0]);
					var value = decodeURI(arr[1] || '');

					if (name == 'code') {
						code = value;
					}
					if (name == 'error') {
						error = value;
					}
				}, this);
				if (code) {
					return {
						code: code,
					}
				} else if (error) {
					return {
						error: error,
					}
				}
			}

			popup = window.open(path, options.windowName, options.windowOptions);
			interval = window.setInterval(function () {
				if (popup.closed) {
					window.clearInterval(interval);
				} else {
					var res = parseUrl(popup.location.href.toString());
					if (res) {
						callback.call(self, res);
						popup.close();
						window.clearInterval(interval);
					}
				}
			}, 500);
		},

		connect: function () {
			this.notify('Please wait...');
			this.popup({
				path: this.getMetadata().get('integrations.' + this.integration + '.params.endpoint'),
				params: {
					client_id: this.clientId,
					redirect_uri: this.redirectUri,
					scope: this.getMetadata().get('integrations.' + this.integration + '.params.scope'),
					response_type: 'code',
					access_type: 'offline',
					approval_prompt: 'force'
				}
			}, function (res) {
				if (res.error) {
					this.notify(false);
					return;
				}
				if (res.code) {
					this.$el.find('[data-action="connect"]').addClass('disabled');
					$.ajax({
						url: 'ExternalAccount/action/authorizationCode',
						type: 'POST',
						data: JSON.stringify({
							'id': this.id,
							'code': res.code
						}),
						dataType: 'json',
						error: function () {
							this.$el.find('[data-action="connect"]').removeClass('disabled');
						}.bind(this)
					}).done(function (response) {
						this.notify(false);
						if (response === true) {
							this.setConneted();
						} else {
							this.setNotConneted();
						}
						this.$el.find('[data-action="connect"]').removeClass('disabled');
					}.bind(this));

				} else {
					this.notify('Error occured', 'error');
				}
			});
		},

        setConneted: function () {
            this.isConnected = true;
            this.$el.find('[data-action="connect"]').addClass('hidden');;
            this.$el.find('.connected-label').removeClass('hidden');
            this.$el.find('.data-panel-connected').removeClass('hidden');

            this.loadCalendars();
        },

        setNotConnected: function () {
            this.isConnected = false;
            this.$el.find('[data-action="connect"]').removeClass('hidden');;
            this.$el.find('.connected-label').addClass('hidden');
            this.$el.find('.data-panel-connected').addClass('hidden');
        },

        validate: function () {
            var defaultEntity = this.model.get('calendarDefaultEntity');
            var entities = this.model.get('calendarEntityTypes');
            var enititesView = this.getView('calendarEntityTypes');
            var defaultEntityView = this.getView('calendarDefaultEntity');

            if (defaultEntityView.$el.is(':visible')) {
                var defaultIsInList = false;
                for (key in entities) {
                    var label = this.model.get(entities[key] + 'IdentificationLabel');
                    if ((label == null || label == '') && defaultEntity != entities[key]) {
                        var msg = this.translate('emptyNotDefaultEnitityLabel', 'messages','GoogleCalendar');
                        enititesView.showValidationMessage(msg, '[name="translatedValue"]:last');
                        return true;
                    }

                    if (entities[key] == defaultEntity) {
                        defaultIsInList = true;
                    }
                }

                if (!defaultIsInList) {
                    var msg = this.translate('defaultEntityIsRequiredInList', 'messages','GoogleCalendar');
                    defaultEntityView.showValidationMessage(msg);
                    return true;
                }
            }

            return false;
        },

        hideField : function (field) {
             this.$el.find('.cell-' + field).addClass('hidden');
        },

        showField : function (field) {
             this.$el.find('.cell-' + field).removeClass('hidden');
        },

	});

});
