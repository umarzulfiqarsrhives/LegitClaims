

Espo.define('real-estate:views/real-estate-property/fields/address-map', 'views/fields/base', function (Dep) {

    return Dep.extend({

        detailTemplate: 'real-estate:real-estate-property/fields/address-map/detail',

        addressField: 'address',

        height: 300,

        data: function () {
            var data = Dep.prototype.data.call(this);
            return data;
        },

        setup: function () {
            this.listenTo(this.model, 'change:interestDegree', function () {
                this.reRender();
            }, this);

            this.listenTo(this.model, 'sync', function () {
                if (this.isRendered()) {
                    this.reRender();
                }
            }, this);

            this.listenTo(this.model, 'after:save', function () {
                if (this.isRendered()) {
                    this.reRender();
                }
            }, this);
        },

        hasAddress: function () {
            return this.addressData.city || this.addressData.postalCode;
        },

        afterRender: function () {
            this.addressData = {
                city: this.model.get(this.addressField + 'City'),
                street: this.model.get(this.addressField + 'Street'),
                postalCode: this.model.get(this.addressField + 'PostalCode'),
                country: this.model.get(this.addressField + 'Country'),
                state: this.model.get(this.addressField + 'State')
            };

            if (this.hasAddress()) {
                if (window.google && window.google.maps) {
                    this.initMap();
                } else {
                    window.mapapiloaded = function () {
                        this.initMap();
                    }.bind(this);
                    var src = 'https://maps.googleapis.com/maps/api/js?callback=mapapiloaded';
                    var s = document.createElement('script');
                    s.setAttribute('async', 'async');
                    s.src = src;
                    document.head.appendChild(s);
                }
            }
        },

        initMap: function () {
            this.$el.find('.map').css('height', this.height + 'px');

            var geocoder = new google.maps.Geocoder();

            var map = new google.maps.Map(this.$el.find('.map').get(0), {
                zoom: 15,
                center: {lat: 0, lng: 0},
                scrollwheel: false
            });

            var address = '';


            if (this.addressData.street) {
                address += this.addressData.street;
            }

            if (this.addressData.city) {
                if (address != '') {
                    address += ', ';
                }
                address += this.addressData.city;
            }

            if (this.addressData.state) {
                if (address != '') {
                    address += ', ';
                }
                address += this.addressData.state;
            }

            if (this.addressData.postalCode) {
                if (this.addressData.state || this.addressData.city) {
                    address += ' ';
                } else {
                    if (address) {
                        address += ', ';
                    }
                }
                address += this.addressData.postalCode;
            }

            if (this.addressData.country) {
                if (address != '') {
                    address += ', ';
                }
                address += this.addressData.country;
            }

            geocoder.geocode({'address': address}, function(results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    map.setCenter(results[0].geometry.location);
                    var marker = new google.maps.Marker({
                        map: map,
                        position: results[0].geometry.location
                    });
                }
            }.bind(this));

        }

    });

});
