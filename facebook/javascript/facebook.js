/*global jQuery*/
(function($){
	window.FacebookResponse = function(data) {
		if(data.name) {
			$('#ConnectFacebookButton').replaceWith('Connected to Facebook user ' + data.name + '. <a href="' + data.removeLink + '" id="RemoveFacebookButton">Disconnect</a>');
		}
	};
	$('#ConnectFacebookButton').livequery('click', function (e) {
		window.open('FacebookCallback/FacebookConnect').focus();
		e.stopPropagation();
		return false;
	});
	$('#RemoveFacebookButton').livequery('click', function (e) {
		$.get($(this).attr('href'));
		$(this).parent().html('<img src="facebook/Images/connect.png" id="ConnectFacebookButton" alt="Connect to Facebook" />');
		e.stopPropagation();
		return false;
	});
}(jQuery));
