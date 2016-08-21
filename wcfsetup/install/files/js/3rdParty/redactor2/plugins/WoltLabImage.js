$.Redactor.prototype.WoltLabImage = function() {
	"use strict";
	var StringUtil = require('WoltLabSuite/Core/StringUtil');
	
	return {
		init: function() {
			var button = this.button.add('woltlabImage', '');
			this.button.addCallback(button, this.WoltLabImage.add);
			
			// add support for image source when editing
			var mpShowEdit = this.image.showEdit;
			this.image.showEdit = (function($image) {
				var image = $image[0];
				if (image.classList.contains('smiley')) {
					// smilies cannot be edited
					return;
				}
				
				mpShowEdit($image);
				
				// enforce title and button labels
				this.modal.setTitle(this.lang.get('image-edit'));
				this.modal.getActionButton().text(this.lang.get('save'));
				this.modal.getDeleteButton().text(this.lang.get('delete'));
				
				elById('redactor-image-source').value = image.src;
				
				var float = elById('redactor-image-float');
				if (image.classList.contains('messageFloatObjectLeft')) float.value = 'left';
				else if (image.classList.contains('messageFloatObjectRight')) float.value = 'right';
				
				// hide source if image is an attachment
				if (image.classList.contains('woltlabAttachment')) {
					elRemove(elById('redactor-image-source-container'));
				}
			}).bind(this);
			
			var mpUpdate = this.image.update;
			this.image.update = (function() {
				var image = this.observe.image[0];
				
				var sourceInput = elById('redactor-image-source');
				var showError = function(inputElement, message) {
					$('<small class="innerError" />').text(message).insertAfter(inputElement);
				};
				
				if (!image.classList.contains('woltlabAttachment')) {
					// check if source is valid
					var source = sourceInput.value.trim();
					if (source === '') {
						return showError(sourceInput, this.lang.get('empty'));
					}
					else if (!source.match(this.opts.regexps.url)) {
						return showError(sourceInput, this.lang.get('image-source-invalid'));
					}
					
					// update image source
					image.src = source;
				}
				
				// remove old float classes
				image.classList.remove('messageFloatObjectLeft');
				image.classList.remove('messageFloatObjectRight');
				
				// set float behavior
				var float = elById('redactor-image-float').value;
				if (float === 'left' || float === 'right') {
					image.classList.add('messageFloatObject' + StringUtil.ucfirst(float));
				}
				
				mpUpdate();
				
				// remove alt/title attribute again (not supported)
				image.removeAttribute('alt');
				image.removeAttribute('title');
			}).bind(this);
			
			// overwrite modal template
			this.opts.modal['image-edit'] = '<div class="section">'
					+ '<dl id="redactor-image-source-container">'
						+ '<dt><label for="redactor-image-source">' + this.lang.get('image-source') + '</label></dt>'
						+ '<dd><input type="text" id="redactor-image-source" class="long"></dd>'
					+ '</dl>'
					+ '<dl>'
						+ '<dt><label for="redactor-image-link">' + this.lang.get('image-link') + '</label></dt>'
						+ '<dd><input type="text" id="redactor-image-link" class="long"></dd>'
					+ '</dl>'
					+ '<dl>'
						+ '<dt><label for="redactor-image-float">' + this.lang.get('image-float') + '</label></dt>'
						+ '<dd>'
							+ '<select id="redactor-image-float">'
								+ '<option value="none">' + this.lang.get('no-selection') + '</option>'
								+ '<option value="left">' + this.lang.get('image-float-left') + '</option>'
								+ '<option value="right">' + this.lang.get('image-float-right') + '</option>'
							+ '</select>'
						+ '</dd>'
					+ '</dl>'
					+ '<input id="redactor-image-title" style="display: none">' /* dummy because redactor expects it to be present */
					+ '<input id="redactor-image-caption" style="display: none">' /* dummy because redactor expects it to be present */
					+ '<div class="formSubmit">'
						+ '<button id="redactor-modal-button-action" class="buttonPrimary">Insert</button>'
						+ '<button id="redactor-modal-button-delete" class="redactor-modal-button-offset">Delete</button>'
					+ '</div>'
				+ '</div>';
		},
		
		add: function() {
			this.modal.load('image-edit', this.lang.get('image-insert'));
			
			this.modal.show();
			
			this.modal.getDeleteButton().hide();
			var button = this.modal.getActionButton()[0];
			button.addEventListener(WCF_CLICK_EVENT, this.WoltLabImage.insert);
			button.textContent = this.lang.get('insert');
			
			this.WoltLabModal.rebuild();
		},
		
		insert: function(event) {
			event.preventDefault();
			
			// remove any existing error messages first
			this.modal.getModal().find('.innerError').remove();
			
			var sourceInput = elById('redactor-image-source');
			var showError = function(inputElement, message) {
				$('<small class="innerError" />').text(message).insertAfter(inputElement);
			};
			
			// check if source is valid
			var source = sourceInput.value.trim();
			if (source === '') {
				return showError(sourceInput, this.lang.get('empty'));
			}
			else if (!source.match(this.opts.regexps.url)) {
				return showError(sourceInput, this.lang.get('image-source-invalid'));
			}
			
			// check if link is valid
			var linkInput = elById('redactor-image-link');
			var link = linkInput.value.trim();
			
			if (link !== '' && !link.match(this.opts.regexps.url)) {
				return showError(linkInput, this.lang.get('image-link-invalid'));
			}
			
			var float = elById('redactor-image-float').value, className = '';
			if (float === 'left' || float === 'right') {
				className = 'messageFloatObject' + StringUtil.ucfirst(float);
			}
			
			var html = '<img src="' + StringUtil.escapeHTML(source) + '"' + (className ? ' class="' + className + '"' : '') + '>';
			if (link) {
				html = '<a href="' + StringUtil.escapeHTML(link) + '">' + html + '</a>';
			}
			
			this.modal.close();
			
			this.buffer.set();
			
			this.insert.html(html);
		}
	};
};