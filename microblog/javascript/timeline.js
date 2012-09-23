
window.Microblog = window.Microblog || {}

;(function($) {
	
	Microblog.Timeline = function () {
		var feed = null;
		
		var refreshTimer = null;
		var refreshTime = 10000;
		var pendingUpdate = false;
		var pendingLoad = false;
		
		var postContainer = $('<div>');
		
		var loading = false;
		
		var nextQueryOffset = 0;
		
		var refreshTimeline = function (since) {
			if (pendingUpdate) {
				return pendingUpdate;
			}

			if (!since) {
				var maxId = 0;
				$('div.microPost').each(function (index) {
					var postId = parseInt($(this).attr('data-id'));
					if (postId > maxId) {
						maxId = postId;
					}
				})
				since = maxId;
			}

			loading = true;
			pendingUpdate = getPosts({since: since, replies: 1});
			
			if (!pendingUpdate) {
				return;
			}

			pendingUpdate.done(function () {
				pendingUpdate = null;
				loading = false;
				if (feed.hasClass('autorefresh')) {
					setTimeout(function () {
						refreshTimeline();
					}, refreshTime);
				}
			})
			return pendingUpdate;
		}

		var morePosts = function () {
			if (pendingLoad) {
				return pendingLoad;
			}

			var earliest = -1;
			var rating = false;
			$('div.microPost').each(function () {
				if ($(this).attr('data-sortby') == 'rating') {
					rating = true;
				}
				if ($(this).attr('data-id') < earliest || earliest == -1) {
					earliest = $(this).attr('data-id');
				}
			})

			if (earliest) {
				var restrict = {offset: nextQueryOffset};
				pendingLoad = getPosts(restrict, true).done(function () {
					pendingLoad = null;
				});
				return pendingLoad;
			}
		}

		var getPosts = function (params, append, callback) {
			var url = $('input[name=timelineUpdateUrl]').val();
			if (!url) {
				return;
			}
			return $.get(url, params, function (data) {
				postContainer.empty();
				if (data && data.length > 0) {
					postContainer.append(data);
					nextQueryOffset = postContainer.find('.postQueryOffset').val();
					postContainer.find('div.microPost').each (function () {
						var wrapper = $('<div class="newposts">');
						var me = $(this);

						var parentId = parseInt(me.attr('data-parent'));
						if (!parentId) {
							if (append) {
								wrapper.appendTo(feed);
							} else {
								wrapper.prependTo(feed);
							}
						} else {
							var target = $('#post' + parentId);
							if (target.length) {
								var targetReplies = $('div.postReplies', target);
								wrapper.appendTo(targetReplies);
							}
						}
						wrapper.append(me);
						wrapper.effect("highlight", {}, 3000);
					})

					/*
					$('.fileUploadForm').fileupload('disable');
					$('.fileUploadForm').fileupload('enable');
					*/
					if ($.fileupload && $('.fileUploadForm').length) {
						$('.fileUploadForm').fileupload(
							'option',
							'dropZone',
							$('textarea.postContent')
						);
					}
					
				}
			});
		}

		var deletePost = function (id) {
			if (!id) {
				return;
			}
			var params = {
				'postType': 'MicroPost',
				'postID': id
			};

			SSWebServices.post('microBlog', 'deletePost', params, function (data) {
				if (data && data.response) {
					// marked as deleted, versus completely removed
					if (data.response.Deleted == 0) {
						$('#post' + id).fadeOut('slow');
					} else {
						$('#post' + id).find('div.postText').html(data.response.Content);
					}
				}
				
			})
		}

		var vote = function (id, dir) {
			var params = {
				'postType': 'MicroPost',
				'postID': id,
				'dir': dir
			};
			
			return SSWebServices.post('microBlog', 'vote', params, function (data) {
				if (data && data.response) {
					$('span.ownerVotes').each(function () {
						if ($(this).attr('data-id') == Microblog.Member.MemberID) {
							$(this).text(data.response.RemainingVotes).effect("highlight", {}, 2000);
						}
					})
				}
			})
		};

		var setOffset = function (o) {
			nextQueryOffset = o;
		}
		
		var setFeed = function (f) {
			feed = f;
			
			if (feed.hasClass('autorefresh') && !refreshTimer) {
				refreshTimer = setTimeout(function () {
					refreshTimeline();
				}, refreshTime)
			}
		}
		
		var editPost = function (id) {
			return SSWebServices.get('microBlog', 'rawPost', {id: id}, function (post) {
				if (post && post.response) {
					
					var editorField = $('<textarea name="Content" class="postContent expandable">');
					editorField.val(post.response.Content);
					
					var postId = 'post' + id;
					var postContent = $($('#' + postId).find('.postText')[0]);
					postContent.append(editorField);
					
					var save = $('<input type="button" value="Save">');
					save.insertAfter(editorField);
					
					save.click(function () {
						var data = {
							'Content'	: editorField.val()
						};

						var params = {
							postID: id,
							postType: 'MicroPost',
							data: data
						}
						
						editorField.remove()
						save.remove()
						
						SSWebServices.post('microBlog', 'savePost', params).done(function (data) {
							if (data && data.response) {
								if (typeof(Showdown) != 'undefined') {
									var converter = new Showdown.converter();
									postContent.html(converter.makeHtml(data.response.Content));
									delete converter;
								}
							}
						});
					})
				}
/*					
 					post = post.response;
					post.Content += 'derd ';
					
					*/
			})
		}

		return {
			refresh: refreshTimeline,
			more: morePosts,
			deletePost: deletePost,
			vote: vote,
			setOffset: setOffset,
			setFeed: setFeed,
			editPost: editPost
		}
	}();

	$(function () {
		Microblog.Timeline.setOffset($('.postQueryOffset').val());

		$.entwine('microblog', function ($) {
			
			$('div.postText a').entwine({
				onclick: function () {
					var postId = $(this).parents('.microPost').attr('data-id');
					Microblog.track('timeline', 'post_click', $(this).attr('href'));
					this._super();
				}
			})
			
			$('#StatusFeed').entwine({
				onmatch: function () {
					Microblog.Timeline.setFeed(this);
				}
			})
			
			$('.timeago').entwine({
				onmatch: function () {
					if ($.fn.timeago) {
						this.timeago();
					}
				}
			})

			$('textarea.expandable').entwine({
				onmatch: function () {
					this.checkContentSize();
					this._super();
				},
				onkeydown: function (e) {
					if (e.which == 13 && !$(this).hasClass('expanded-content')) {
						$(this).addClass('expanded-content');
					}
					this.checkContentSize();
				},
				checkContentSize: function () {
					if ($(this).hasClass('expanded-content')) {
						return;
					}
					if ($(this).val().length > 80) {
						$(this).addClass('expanded-content');
					}

					if ($(this).val().indexOf("\n") >= 0) {
						$(this).addClass('expanded-content');
					}
				}
			});
			
			$('a.deletePost').entwine({
				onclick:function () {
					var postId = $(this).parents('.microPost').attr('data-id');
					Microblog.Timeline.deletePost(postId);
					return false;
				}
			})
			
			$('a.vote').entwine({
				onclick: function () {
					var _this = $(this);
					var dir = $(this).attr('data-dir'); 
					Microblog.Timeline.vote($(this).attr('data-id'), dir).done(function (object) {
						if (object.response) {
							_this.siblings('.upCount').text(object.response.Up);
							_this.siblings('.downCount').text(object.response.Down);
						}
					})
					
					return false;
				}
			})

			$('div.microPost').entwine({
				onmatch: function () {
					if ($(this).attr('data-owner') == Microblog.Member.MemberID && $(this).attr('data-editable')) {
						var editId = $(this).attr('data-id');
						var button = $('<a href="#" class="editButton">edit post</a>');
						$($(this).find('.postOptions')[0]).append(button);
						button.click(function (e) {
							e.preventDefault();
							Microblog.Timeline.editPost(editId)
						})
					}
				}
			})

			$('a.moreposts').entwine({
				onclick: function () {
					var _this = this;
					// caution - leak possible!! need to switch to new 'from' stuff in entwine
					var doMore = Microblog.Timeline.more();
					if (doMore) {
						doMore.done(function () {
							_this.appendTo('#StatusFeed')
						});
					}
					return false;
				}
			})
			
			// Auto replace image URLs 
			$('a').entwine({
				onmatch: function () {
					var href = this.attr('href');
					if (href && href.length && href.lastIndexOf('.') > 0) {
						var ext = href.substr(href.lastIndexOf('.') + 1);
						if ($.inArray(ext, ['png', 'jpg', 'gif']) > -1) {
							// see if this actually has an image already
							if ($(this).find('img').length == 0) {
								var img = $('<img>').attr('src', href);
								this.text('').append(img).attr('target', '_blank');
							}
						}
					}
					this._super();
				}
			})

			$('form.replyForm').entwine({
				onmatch: function () {
					$(this).attr('action', $('#PostFormUrl').val());
					this.ajaxForm(function (data) {
						$('form.replyForm').find('textarea').removeClass('expanded-content').val('');
						Microblog.Timeline.refresh();
						if (data && data.response) {
							$('span.ownerVotes').each(function () {
								if ($(this).attr('data-id') == Microblog.Member.MemberID) {
									$(this).text(data.response.RemainingVotes).effect("highlight", {}, 2000);
								}
							})
						}

						$('form.replyForm').find('input[name=action_savepost]').removeAttr('disabled');
					})
				},
				onsubmit: function () {
					$(this).find('input[name=action_savepost]').attr('disabled', 'disabled');
					$('div.postPreview').hide();
					return true;
				}
			})

			$('#Form_PostForm').entwine({
				onmatch: function () {
					this.ajaxForm(function (data) {
						$('#Form_PostForm').find('textarea').removeClass('expanded-content').val('');
						$('#Form_PostForm').find('input[name=action_savepost]').removeAttr('disabled');
						Microblog.Timeline.refresh();
						if (data && data.response) {
							$('span.ownerVotes').each(function () {
								if ($(this).attr('data-id') == Microblog.Member.MemberID) {
									$(this).text(data.response.RemainingVotes).effect("highlight", {}, 2000);
								}
							})
						}
					})
				},
				onsubmit: function () {
					$(this).find('input[name=action_savepost]').attr('disabled', 'disabled');
					$('div.postPreview').hide();
					return true;
				}
			});

			$('a.replyToPost').entwine({
				onclick: function (e) {
					e.preventDefault();
					$(this).parent().siblings('form.replyForm').show().find('textarea').focus();
				}
			});
			
			$('input[name=uploadTrigger]').entwine({
				onclick: function () {
					$('div.uploadForm').show();
					return false;
				}
			})
			
			if (typeof(Showdown) != 'undefined') {
				var converter = new Showdown.converter();
				$('textarea.postContent.preview').entwine({
					onmatch: function () {
						var parent = $(this).parent(); //('form');
						var preview = $('<div>').addClass('postPreview').hide();
						preview.insertAfter(parent);
						$(this).keyup(function () {
							preview.html(converter.makeHtml($(this).val())).show();
						})
						this._super();
					}
				})
			}
			

			// TODO Fix issue where dynamically entered textarea.postContent isn't bound as a drop source
			$('.fileUploadForm').entwine({
				onmatch: function () {
					var uploadList = $('#uploadedFiles');
					var uploadParent = 0;
					$(this).fileupload({
						dataType: 'json',
						dropZone: $('textarea.postContent'),
						formData: function(form) {
							var formData = [
								{name: 'SecurityID', value: $('input[name=SecurityID]').val()}
								// {name: 'ID', value: $(form).find(':input[name=ID]').val()}
							];
							if (uploadParent > 0) {
								formData.push({name: 'ParentID', value: uploadParent})
							}
							return formData;
						},
						drop: function (e, data) {
							$('div.uploadForm').show();
							uploadParent = 0;
							if (e.currentTarget) {
								var parent = $(e.currentTarget).closest('div.microPost');
								if (parent.length) {
									// set the uploadParent id
									uploadParent = parent.attr('data-id');
								}
							}
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
