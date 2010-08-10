$(function () {
	$('#nav .admin-submenu .current').parents('li').addClass('current');
	$('.message-error').fadeOut(300).fadeIn(400);
	$("input, textarea").focus(function() {
		// only select if the text has not changed
		if(this.value == this.defaultValue) {
			this.select();
		}
	});
});