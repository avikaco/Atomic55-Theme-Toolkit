/**
 * Wordpress Theme Customizer
 * 
 * @copyright Atomic55
 * @author Alfi Rizka
 */

/* global jQuery: true */
/* global google: true */

(function(W, $) {
	"use strict";

	var ControlMap = function(element, options) {
		var THIS = this;

		this.options = options;
		this.$element = $(element);
		this.$input = this.$element.find('input[type=hidden]');
		this.$map_wrap = this.$element.find(options.map_wrap);
		this.$type = this.$element.find(options.type);
		this.$width = this.$element.find(options.width);
		this.$height = this.$element.find(options.height);
		this.MAP = null;
		this.MARKER = null;
		this.control_id = this.$input.data('customize-setting-link');
		this.value = {
			lat : this.$map_wrap.data('lat') || 0,
			lng : this.$map_wrap.data('lng') || 0,
			zoom : this.$map_wrap.data('zoom') || 18,
			map_type : this.$type.filter(':checked').val() || 'dynamic',
			width : this.$width.val() || '100%',
			height : this.$height.val() || '300px'
		};

		// init map only if section open (clicked)
		this.$element.parent().prev().one('click', function() {
			var mapOptions = {
				zoom : THIS.value.zoom,
				center : new google.maps.LatLng(THIS.value.lat || 49.8880486738, THIS.value.lng || -119.496074973),
				scrollwheel : false,
				streetViewControl : false,
				mapTypeControl : true,
				mapTypeControlOptions : {
					style : google.maps.MapTypeControlStyle.DROPDOWN_MENU,
					position : google.maps.ControlPosition.RIGHT_BOTTOM
				},
				minZoom : 16,// $map.data('zoom'),
				zoomControl : true,
				zoomControlOptions : {
					style : google.maps.ZoomControlStyle.SMALL
				},
				mapTypeId : google.maps.MapTypeId.ROADMAP
			};

			var $addressControl = $('<div/>');
			var $addressInput = $('<input type="text" placeholder="search address"/>').appendTo($addressControl);
			var $addressGeolocation = $('<img src="//cdn3.iconfinder.com/data/icons/wpzoom-developer-icon-set/500/71-20.png"/>');

			// use new visual refresh of google map.
			google.maps.visualRefresh = true;

			THIS.MAP = new google.maps.Map(THIS.$map_wrap.get(0), mapOptions);
			THIS.GEOCODER = new google.maps.Geocoder();

			if(THIS.value.lat && THIS.value.lng) {
				THIS.setMarker(THIS.value.lat, THIS.value.lng);
			}
			
			google.maps.event.addListener(THIS.MAP, 'zoom_changed', function() {
				THIS.setValue('zoom', THIS.MAP.getZoom());
			});

			// add address search
			$addressControl.css({
				index : 10,
				position : 'relative',
				marginTop : '12px',
				marginRight : '10px'
			});
			$addressInput.css({
				width : '242px',
				height : '33px'
			}).keydown(function(e) {
				if (e.keyCode === 13) {
					THIS.searchAddress($addressInput.val());
					e.preventDefault();
					return false;
				}
			});

			// add HTML geolocation if available
			if (W.navigator.geolocation) {
				$addressInput.css({
					width : '217px',
					paddingLeft : '25px'
				});
				$addressGeolocation.css({
					cursor : 'pointer',
					width : '20px',
					height : '20px',
					position : 'absolute',
					top : '7px',
					left : '3px'
				}).attr('title', 'Detect my location automatically').click(function(e) {
					navigator.geolocation.getCurrentPosition(function(position) {
						THIS.setMarker(position.coords.latitude, position.coords.longitude);
					}, function() {
						W.alert('Please allow this site to detect your location.');
					});

					e.preventDefault();
					return false;
				}).appendTo($addressControl);
			}

			THIS.MAP.controls[google.maps.ControlPosition.TOP_RIGHT].push($addressControl.get(0));
			// end of add custom control

			// bind event
			THIS.$type.change(function() {
				var type = THIS.$type.filter(':checked').val(), temp;

				if (type === 'static') {
					// static map dimension MUST in pixel, can't percent
					temp = THIS.$width.val();
					if (temp.indexOf('%')) {
						temp = temp.replace('%', 'px');
						THIS.$width.val(temp);
						THIS.setValue('width', temp);
					}
					temp = THIS.$height.val();
					if (temp.indexOf('%')) {
						temp = temp.replace('%', 'px');
						THIS.$height.val(temp);
						THIS.setValue('height', temp);
					}
				}
			}).trigger('change');
		});
	};

	ControlMap.DEFAULTS = {
		map_wrap : '.a55-control-map-wrp',
		type : "[name='a55-map-type-map']",
		width : "[name^='a55-map-width']",
		height : "[name^='a55-map-height']"
	};

	ControlMap.prototype.setValue = function(key, value) {
		var val;

		// assign to value
		this.value[key] = value;
		// convert to JSON string
		val = JSON.stringify(this.value);
		// update customizer model
		W.wp.customize._value[this.control_id].set(val);
		// update input
		this.$input.val(val);
	};

	ControlMap.prototype.setMarker = function(lat, lng) {
		var pos = new google.maps.LatLng(lat, lng), parent = this;

		this.MAP.setCenter(pos);

		// add marker if not initialize
		if (this.MARKER === null) {
			this.MARKER = new google.maps.Marker({
				map : this.MAP,
				draggable : true,
				animation : google.maps.Animation.DROP,
				position : pos
			});
			google.maps.event.addListener(this.MARKER, 'dragend', function() {
				var ltln = parent.MARKER.getPosition();
				parent.setMarker(ltln.lat(), ltln.lng());
			});
		}

		this.MARKER.setPosition(pos);
		this.setValue('lat', lat);
		this.setValue('lng', lng);
	};

	ControlMap.prototype.searchAddress = function(address) {
		var parent = this;

		if (this.GEOCODER) {
			this.GEOCODER.geocode({
				'address' : address
			}, function(results, status) {
				if (status === google.maps.GeocoderStatus.OK) {
					parent.setMarker(results[0].geometry.location.lat(), results[0].geometry.location.lng());
				} else {
					W.alert("Error: " + address + " cannot be found on Google Maps.");
				}
			});
		}
	};

	// '.customize-control-atomic55_map';
	$.fn.a55_control_map = function(option, _relatedTarget) {
		return this.each(function() {
			var $this = $(this), data = $this.data('bs.map55'), options = $.extend({}, ControlMap.DEFAULTS, typeof option === 'object' && option);

			if (!data) {
				$this.data('bs.map55', (data = new ControlMap(this, options)));
			}
			if (typeof option === 'string' && data[option]) {
				data[option](_relatedTarget);
			}
		});
	};

	$.fn.a55_control_map.Constructor = ControlMap;

	$(function() {
		$('.customize-control-atomic55_map').a55_control_map();
	});
})(window, jQuery);