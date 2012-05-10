
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
		if (securityID && member) {
			$('textarea[name=Title]').focus();
			member = $.parseJSON(member);
			var viewing = $('#ViewingUser').val();
			var service = 'jsonservice/MicroBlog'
			if (viewing) { 
				service += '/getStatusUpdates';
			} else {
				service += '/getTimeline';
			}
			var lastUpdate = Date.parse('now').toString('yyyy-MM-dd HH:mm:ss');
			
			var template = $('#UserFeed div.microPost:first');
			
			if (template.length) {
				
				setInterval(function () {
					var params = {
						'memberType': 'Member',
						'memberID': member.ID,
						'sinceTime': lastUpdate,
						'SecurityID': securityID
					};
					$.get(service, params, function (data) {
						var content = $(template.html());
						if (data && data.response && data.response.items) {
							lastUpdate = Date.parse('now').toString('yyyy-MM-dd HH:mm:ss');
							$.each(data.response.items, function () {
								var content = this.Title; // $('<div>').text(this.Title).text();
								//var breakTag = '<br/>';
								//content = content.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
								var tmplData = {
									'Author': this.Author,
									'Date': Date.parse(this.Created).toString('dd/MM/yyyy HH:mm'),
									'Content': content
								}
								var content = $('#MicroPostTmpl').tmpl(tmplData);
								
								var text = content.find('.microPostContent').html();
								text = text.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>$2');
								content.find('.microPostContent').html(text);
								
								$('#UserFeed').prepend(content);
							})
						}
					})

					lastUpdate = Date.parse('now').toString('yyyy-MM-dd HH:mm:ss');
				}, 5000);
			}
		}
	})
})(jQuery);