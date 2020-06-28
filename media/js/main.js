jQuery(document).ready(function() {

	setInterval(function() {
		jQuery('.back-to-future').each(function() {

			let diff = parseInt(jQuery(this).attr('data-diff'));
			diff = diff - 1;

			if(diff > 0) {

				let out = "";

				let minutesLeft = ~~(diff / 60);

				if(minutesLeft > 1) {
					out = ~~(minutesLeft) + "мин.";

					let secondsLeft = ~~(diff - minutesLeft * 60);
					out += " " + ~~(secondsLeft) + "сек.";
				} else {
					out = ~~(diff) + "сек.";
				}

				jQuery(this).html(out);
				jQuery(this).attr('data-diff', diff);
			} else {
				// jQuery(this).html("0 сек.");
				location.replace('/');
			}

		});
	}, 1000);

});