$.Redactor.prototype.WoltLabImage = function() {
	"use strict";
	
	return {
		init: function() {
			var button = this.button.add('woltlabImage', '');
			this.button.addCallback(button, this.WoltLabImage.add);
			
			// add support for image source when editing
			// TODO: float
			var mpShowEdit = this.image.showEdit;
			this.image.showEdit = (function($image) {
				var image = $image[0];
				if (image.classList.contains('smiley')) {
					// smilies cannot be edited
					return;
				}
				
				mpShowEdit($image);
				
				// enforce title and button labels
				this.modal.setTitle(WCF.Language.get('wcf.editor.image.edit'));
				this.modal.getActionButton().text(WCF.Language.get('wcf.global.button.save'));
				this.modal.getDeleteButton().text(WCF.Language.get('wcf.global.button.delete'));
				
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
						return showError(sourceInput, WCF.Language.get('wcf.global.form.error.empty'));
					}
					else if (!source.match(this.opts.regexps.url)) {
						return showError(sourceInput, WCF.Language.get('wcf.editor.image.source.error.invalid'));
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
					image.classList.add('messageFloatObject' + WCF.String.ucfirst(float));
				}
				
				mpUpdate();
				
				// remove alt/title attribute again (not supported)
				image.removeAttribute('alt');
				image.removeAttribute('title');
			}).bind(this);
			
			// overwrite modal template
			this.opts.modal['image-edit'] = '<div class="section">'
					+ '<dl id="redactor-image-source-container">'
						+ '<dt><label for="redactor-image-source">' + WCF.Language.get('wcf.editor.image.source') + '</label></dt>'
						+ '<dd><input type="text" id="redactor-image-source" class="long"></dd>'
					+ '</dl>'
					+ '<dl>'
						+ '<dt><label for="redactor-image-link">' + WCF.Language.get('wcf.editor.image.link') + '</label></dt>'
						+ '<dd><input type="text" id="redactor-image-link" class="long"></dd>'
					+ '</dl>'
					+ '<dl>'
						+ '<dt><label for="redactor-image-float">' + WCF.Language.get('wcf.editor.image.float') + '</label></dt>'
						+ '<dd>'
							+ '<select id="redactor-image-float">'
								+ '<option value="none">' + WCF.Language.get('wcf.global.noSelection') + '</option>'
								+ '<option value="left">' + WCF.Language.get('wcf.editor.image.float.left') + '</option>'
								+ '<option value="right">' + WCF.Language.get('wcf.editor.image.float.right') + '</option>'
							+ '</select>'
						+ '</dd>'
					+ '</dl>'
					+ '<input id="redactor-image-title" style="display: none">' /* dummy because redactor expects it to be present */
					+ '<div class="formSubmit">'
						+ '<button id="redactor-modal-button-action" class="buttonPrimary">Insert</button>'
						+ '<button id="redactor-modal-button-delete" class="redactor-modal-button-offset">Delete</button>'
					+ '</div>'
				+ '</div>';
		},
		
		add: function() {
			this.modal.load('image-edit', WCF.Language.get('wcf.editor.image.insert'));
			
			this.modal.show();
			
			this.modal.getDeleteButton().hide();
			var button = this.modal.getActionButton()[0];
			button.addEventListener(WCF_CLICK_EVENT, this.WoltLabImage.insert);
			button.textContent = WCF.Language.get('wcf.global.button.insert');
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
				return showError(sourceInput, WCF.Language.get('wcf.global.form.error.empty'));
			}
			else if (!source.match(this.opts.regexps.url)) {
				return showError(sourceInput, WCF.Language.get('wcf.editor.image.source.error.invalid'));
			}
			
			// check if link is valid
			var linkInput = elById('redactor-image-link');
			var link = linkInput.value.trim();
			
			if (link !== '' && !link.match(this.opts.regexps.url)) {
				return showError(linkInput, WCF.Language.get('wcf.editor.image.link.error.invalid'));
			}
			
			var float = elById('redactor-image-float').value, className = '';
			if (float === 'left' || float === 'right') {
				className = 'messageFloatObject' + WCF.String.ucfirst(float);
			}
			
			var html = '<img src="' + WCF.String.escapeHTML(source) + '"' + (className ? ' class="' + className + '"' : '') + '>';
			if (link) {
				html = '<a href="' + WCF.String.escapeHTML(link) + '">' + html + '</a>'; 
			}
			
			this.modal.close();
			this.insert.html(html);
		}
	};
};