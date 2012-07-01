
window.Microblog = window.Microblog || {}

;(function($) {
	
	Microblog.Timeline = function () {
		var feed = $('#StatusFeed');
		
		var refreshTime = 10000;
		var pendingUpdate = false;
		var pendingLoad = false;
		
		var loading = false;
		
		var refreshTimeline = function (since) {
			if (pendingUpdate) {
				return pendingUpdate;
			}

			if (!since) {
				var times = $('abbr.postTime:first');
				// top down latest, so take the first one's time
				since = $(times).attr('data-created');
			}
			
			loading = true;
			pendingUpdate = getPosts({since: since});
			
			pendingUpdate.done(function () {
				pendingUpdate = null;
				loading = false;
				setTimeout(function () {
					refreshTimeline();
				}, refreshTime);
			})
			return pendingUpdate;
		}
		
		setTimeout(function () {
			refreshTimeline();
		}, refreshTime)
		
		var morePosts = function () {
			if (pendingLoad) {
				return pendingLoad;
			}
			var allPosts = $('.microPost');
			var earliest = $(allPosts[allPosts.length-1]).attr('data-id');
			if (earliest) {
				pendingLoad = getPosts({before: earliest}, true);
				return pendingLoad;
			}
		}

		var getPosts = function (params, append, callback) {
			var url = $('input[name=timelineUpdateUrl]').val();
			if (!url) {
				return;
			}
			return $.get(url, params, function (data) {
				if (data && data.length > 0) {
					var newPosts = $('<div class="newposts">');
					if (append) {
						newPosts.appendTo(feed);
					} else {
						newPosts.prependTo(feed);
					}

					newPosts.append(data);
					newPosts.effect("highlight", {}, 3000);
				}
			});
		}

		return {
			refresh: refreshTimeline,
			more: morePosts
		}
	}();

	$(function () {

		$.entwine('microblog', function ($) {
			$('.timeago').entwine({
				onmatch: function () {
					if ($.fn.timeago) {
						this.timeago();
					}
				}
			})
			$('#Form_PostForm_Content').entwine({
				onmatch: function () {
					this.keydown(function (e) {
						if (e.which == 13) {
							$(this).addClass('expanded-content');
						}
					})
				}
			});
			
			$('.moreposts').entwine({
				onclick: function () {
					var _this = this;
					// caution - leak possible!! need to switch to new 'from' stuff in entwine
					Microblog.Timeline.more().done(function () {
						_this.appendTo('#StatusFeed')
					});
					
					return false;
				}
			})
			
			$('#Form_PostForm').entwine({
				onmatch: function () {
					this.ajaxForm(function (done) {
						$('#Form_PostForm_Content').empty();
						Microblog.Timeline.refresh();
					})
				}
			});
			
			$('.fileUploadForm').entwine({
				onmatch: function () {
					var uploadList = $('#uploadedFiles');
					$(this).fileupload({
						dataType: 'json',
						dropZone: $('#dropZone'),
						formData: function(form) {
							var formData = [
								{name: 'SecurityID', value: $('input[name=SecurityID]').val()}
								// {name: 'ID', value: $(form).find(':input[name=ID]').val()}
							];
							return formData;
						},
						drop: function (e, data) {
							$.each(data.files, function (index, file) {
								var li = $('<li class="pending">').appendTo(uploadList).text(file.name);
								li.attr('data-name', file.name);
								$('<span>0%</span>').appendTo(li);
								file.listElem = li;
							});
						},
						done: function (e, data) {
							
							if (data.result && data.files[0] && data.files[0].listElem) {
								if (data.result.ID) {
									data.files[0].listElem.find('span').text('100%');
								} else if (data.result[0]) {
									data.files[0].listElem.find('span').text(data.result[0].message).css('color', 'red');
								} else {
									data.files[0].listElem.find('span').text('Err').css('color', 'red');
								}
							} 
							
							Microblog.Timeline.refresh();
						},
						
						send: function(e, data) {
							if (data.dataType && data.dataType.substr(0, 6) === 'iframe') {
								// Iframe Transport does not support progress events.
								// In lack of an indeterminate progress bar, we set
								// the progress to 100%, showing the full animated bar:
								data.total = 1;
								data.loaded = 1;
								$(this).data('fileupload').options.progress(e, data);
							}
						},
						progress: function(e, data) {
							// if (data.context) {
								var value = parseInt(data.loaded / data.total * 100, 10) + '%';
								if (data.files[0] && data.files[0].listElem) {
									data.files[0].listElem.find('span').text(value);
								}
								// data.contextElem.find('span')
								// data.context.find('.ss-uploadfield-item-status').html((data.total == 1)?ss.i18n._t('UploadField.LOADING'):value);
								// data.context.find('.ss-uploadfield-item-progressbarvalue').css('width', value);
							// }
						}
						/*errorMessages: {
							// errorMessages for all error codes suggested from the plugin author, some will be overwritten by the config comming from php
							1: ss.i18n._t('UploadField.PHP_MAXFILESIZE'),
							2: ss.i18n._t('UploadField.HTML_MAXFILESIZE'),
							3: ss.i18n._t('UploadField.ONLYPARTIALUPLOADED'),
							4: ss.i18n._t('UploadField.NOFILEUPLOADED'),
							5: ss.i18n._t('UploadField.NOTMPFOLDER'),
							6: ss.i18n._t('UploadField.WRITEFAILED'),
							7: ss.i18n._t('UploadField.STOPEDBYEXTENSION'),
							maxFileSize: ss.i18n._t('UploadField.TOOLARGESHORT'),
							minFileSize: ss.i18n._t('UploadField.TOOSMALL'),
							acceptFileTypes: ss.i18n._t('UploadField.INVALIDEXTENSIONSHORT'),
							maxNumberOfFiles: ss.i18n._t('UploadField.MAXNUMBEROFFILESSHORT'),
							uploadedBytes: ss.i18n._t('UploadField.UPLOADEDBYTES'),
							emptyResult: ss.i18n._t('UploadField.EMPTYRESULT')
						},*/
					});
				}
			})
		})
	})
	
})(jQuery);