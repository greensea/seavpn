function help_toggle(topic) {
	var c = $("#help_" + topic);
	
	if (c.css("display") == "none") {
		c.slideDown(200).fadeIn();
	}
	else {
		c.slideUp(200).fadeOut(function () {
			c.css("display", "none");
		});
	}
}
