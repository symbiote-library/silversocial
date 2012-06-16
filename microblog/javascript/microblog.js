
(function($) {
	$(function () {
		
		if ($('input[name=Search]').length) {
			var curVal = $('input[name=Search]').val();
			$('input[name=Search]').focus(function () {
				if ($(this).val() == curVal) {
					$(this).val('');
				}
			})
		}

		var securityID = $('#SS_ID').val();
		var member = $("#SS_MEMBER").attr('data-object');
		
		$('#Form_PostForm').fileupload({
			dataType: 'json',
			dropZone: $('#dropZone'),
			drop: function (e, data) {
				$.each(data.files, function (index, file) {
					alert('Dropped file: ' + file.name);
				});
			},
			done: function (e, data) {
				console.log(data);
				$.each(data.result, function (index, file) {
					$('<p/>').text(file.name).appendTo(document.body);
				});
			}
		});
	})
})(jQuery);