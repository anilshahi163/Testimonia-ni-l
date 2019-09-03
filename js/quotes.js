(function ($, Drupal, drupalSettings) {
	Drupal.behaviors.my_custom_behavior = {
		attach: function (context, settings) {
			var strings = drupalSettings.myVar;
			


		}
	}

})(jQuery, Drupal, drupalSettings);

jQuery('#quote-carousel').carousel({
	pause: true,
	interval: 4000,
});