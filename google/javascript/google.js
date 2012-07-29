/*global jQuery*/
(function($){
	window.GoogleResponse = function(data) {
		if(data.name) {
			$('#ConnectGoogleButton').replaceWith('Connected to Google account ' + data.name + '. <a href="' + data.removeLink + '" id="RemoveGoogleButton">Disconnect</a>');
		}
	};
	$('#ConnectGoogleButton').livequery('click', function (e) {
		window.open('GoogleCallback/GoogleConnect').focus();
		e.stopPropagation();
		return false;
	});
	$('#RemoveGoogleButton').livequery('click', function (e) {
		$.get($(this).attr('href'));
		$(this).parent().html('<img src="google/Images/connect.png" id="ConnectGoogleButton" alt="Connect to Google" />');
		e.stopPropagation();
		return false;
	});
}(jQuery));
