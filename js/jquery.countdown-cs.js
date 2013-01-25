/* http://keith-wood.name/countdown.html
 * Czech initialisation for the jQuery countdown extension
 * Written by Roman Chlebec (creamd@c64.sk) (2008) */
(function($) {
	$.countdown.regional['cs'] = {
		labels: ['roků', 'měsíců', 'týdnů', 'dní', 'hod', 'min', 'sek'],
		labels1: ['rok', 'měsíc', 'týden', 'den', 'hod', 'min', 'sek'],
		labels2: ['roky', 'měsíce', 'týdny', 'dny', 'hod', 'min', 'sek'],
		compactLabels: ['r', 'm', 't', 'd'],
		whichLabels: function(amount) {
			return (amount == 1 ? 1 : (amount >= 2 && amount <= 4 ? 2 : 0));
		},
		digits: ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
		timeSeparator: ':', isRTL: false};
	$.countdown.setDefaults($.countdown.regional['cs']);
})(jQuery);
