/**
 * BBCode Plugin for CKEditor
 * 
 * @author	Marcel Werk
 * @copyright 	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
(function() {
	var $pasted = false;
	var $insertedText = null;
	
	CKEDITOR.on('instanceReady', function(event) {
		/**
		 * Fixes issues with pasted html.
		 */
		event.editor.on('paste', function(ev) {
			if (ev.data.type == 'html') {
				var $value = ev.data.dataValue;
				
				// Convert <br> to line breaks.
				$value = $value.replace(/<br><\/p>/gi,"\n\n");
				$value = $value.replace(/<br>/gi, "\n");
				$value = $value.replace(/<\/p>/gi,"\n\n");
				$value = $value.replace(/&nbsp;/gi," ");
				
				// convert div-separated content into new lines
				$value = $value.replace(/<div([^>])>/gi, '');
				$value = $value.replace(/<\/div>/gi, "\n");
				
				// convert lists into new lines
				$value = $value.replace(/<\/li>/gi, "\n");
				// remove html tags
				$value = $value.replace(/<[^>]+>/g, '');
				
				// fix multiple new lines
				$value = $value.replace(/\n{3,}/gi,"\n\n");
				
				ev.data.dataValue = $value;
				
				$pasted = true;
			}
		}, null, null, 9);
		
		// prevent drag and drop of images in Firefox
		event.editor.document.on('drop', function(ev) {
			if (ev.data.$.dataTransfer) {
				var $html = ev.data.$.dataTransfer.getData('text/html');
				if (/<img src="data:image\/[a-zA-Z0-9]+;base64/.exec($html)) {
					ev.data.preventDefault(true);
				}
			}
		});
		
		event.editor.on('insertText', function(ev) {
			$insertedText = ev.data;
		}, null, null, 1);
		event.editor.on('mode', function(ev) {
			ev.editor.focus();
			
			insertFakeSubmitButton(ev);
		});
		event.editor.on('afterSetData', function(ev) {
			insertFakeSubmitButton(ev);
		});
		
		event.editor.on('key', function(ev) {
			if (ev.data.keyCode == CKEDITOR.ALT + 83) { // [Alt] + [S]
				WCF.Message.Submit.execute(ev.editor.name);
			}
		});
		
		insertFakeSubmitButton(event);
		
		// remove stupid title tag
		$(event.editor.container.$).find('.cke_wysiwyg_div').removeAttr('title');
	});
	
	/**
	 * Inserts a fake submit button, Chrome only.
	 * 
	 * @param	object		event
	 */
	function insertFakeSubmitButton(event) {
		if (event.editor.mode === 'source' || !WCF.Browser.isChrome()) {
			return;
		}
		
		// place button outside of <body> to prevent it being removed once deleting content
		$('<button accesskey="s" />').hide().appendTo($(event.editor.container.$).find('.cke_wysiwyg_div'));
		
	}
	
	/**
	 * Removes obsolete dialog elements.
	 */
	CKEDITOR.on('dialogDefinition', function(event) {
		var $tab;
		var $name = event.data.name;
		var $definition = event.data.definition;

		if ($name == 'link') {
			$definition.removeContents('target');
			$definition.removeContents('upload');
			$definition.removeContents('advanced');
			$tab = $definition.getContents('info');
			$tab.remove('emailSubject');
			$tab.remove('emailBody');
		}
		else if ($name == 'image') {
			$definition.removeContents('advanced');
			$tab = $definition.getContents('Link');
			$tab.remove('cmbTarget');
			$tab = $definition.getContents('info');
			$tab.remove('txtAlt');
			$tab.remove('basic');
		}
		else if ($name == 'table') {
			$definition.removeContents('advanced');
			$definition.width = 210;
			$definition.height = 1;
			
			$tab = $definition.getContents('info');
			
			$tab.remove('selHeaders');
			$tab.remove('cmbAlign');
			$tab.remove('txtHeight');
			$tab.remove('txtCaption');
			$tab.remove('txtSummary');
			
			// don't remove these fields as we need their default values
			$tab.get('txtBorder').style = 'display: none';
			$tab.get('txtWidth').style = 'display: none';
			$tab.get('txtCellSpace').style = 'display: none';
			$tab.get('txtCellPad').style = 'display: none';
		}
		else if ($name == 'smiley') {
			$definition.contents[0].elements[0].onClick = function(ev) {
				var $target = ev.data.getTarget();
				var $targetName = $target.getName();
				
				if ($targetName == 'a') {
					$target = $target.getChild( 0 );
				}
				else if ($targetName != 'img') {
					return;
				}
				
				var $src = $target.getAttribute('cke_src');
				var $title = $target.getAttribute('title');
				
				event.editor.insertText(' ' + $title + ' ');
				
				$definition.dialog.hide();
				ev.data.preventDefault();
			};
		}
	});
	
	/**
	 * Enables this plugin.
	 */
	CKEDITOR.plugins.add('wbbcode', {
		requires: ['htmlwriter'],
		init: function(editor) {
			editor.dataProcessor = new CKEDITOR.htmlDataProcessor(editor);
			editor.dataProcessor.toHtml = toHtml;
			editor.dataProcessor.toDataFormat = toDataFormat;
		}
	});
	
	/**
	 * Removes the unicode zero width space (0x200B).
	 * 
	 * @param	string		string
	 * @return	string
	 */
	var removeCrap = function(string) {
		var $string = '';
		
		for (var $i = 0, $length = string.length; $i < $length; $i++) {
			var $byte = string.charCodeAt($i).toString(16);
			if ($byte != '200b') {
				$string += string[$i];
			}
		}
		
		return $string;
	}

	/**
	 * Converts bbcodes to html.
	 */
	var toHtml = function(data, fixForBody) {
		if ($.trim(data) === "") return "<p></p>";
		
		// remove 0x200B (unicode zero width space)
		data = removeCrap(data);
		
		if ($insertedText !== null) {
			data = $insertedText;
			$insertedText = null;
			
			if (data == ' ') return '&nbsp;';
		}
		
		if (!$pasted) {
			// Convert & to its HTML entity.
			data = data.replace(/&/g, '&amp;');
			
			// Convert < and > to their HTML entities.
			data = data.replace(/</g, '&lt;');
			data = data.replace(/>/g, '&gt;');
		}
		
		// Convert line breaks to <br>.
		data = data.replace(/(?:\r\n|\n|\r)/g, '<br>');
		
		if ($pasted) {
			$pasted = false;
			// skip
			return data;
		}
		
		// cache code tags
		var $cachedCodes = { };
		data = data.replace(/\[code(.+?)\[\/code]/gi, function(match) {
			var $key = match.hashCode();
			$cachedCodes[$key] = match;
			return '@@' + $key + '@@';
		});
		
		// [url]
		data = data.replace(/\[url\]([^"]+?)\[\/url]/gi, '<a href="$1">$1</a>');
		data = data.replace(/\[url\='([^'"]+)'](.+?)\[\/url]/gi, '<a href="$1">$2</a>');
		data = data.replace(/\[url\=([^'"\]]+)](.+?)\[\/url]/gi, '<a href="$1">$2</a>');
		
		// [email]
		data = data.replace(/\[email\]([^"]+?)\[\/email]/gi, '<a href="mailto:$1">$1</a>');
		data = data.replace(/\[email\=([^"\]]+)](.+?)\[\/email]/gi, '<a href="mailto:$1">$2</a>');
		
		// [b]
		data = data.replace(/\[b\](.*?)\[\/b]/gi, '<b>$1</b>');
		
		// [i]
		data = data.replace(/\[i\](.*?)\[\/i]/gi, '<i>$1</i>');
		
		// [u]
		data = data.replace(/\[u\](.*?)\[\/u]/gi, '<u>$1</u>');
		
		// [s]
		data = data.replace(/\[s\](.*?)\[\/s]/gi, '<s>$1</s>');
		
		// [sub]
		data = data.replace(/\[sub\](.*?)\[\/sub]/gi, '<sub>$1</sub>');
		
		// [sup]
		data = data.replace(/\[sup\](.*?)\[\/sup]/gi, '<sup>$1</sup>');
			
		// [img]
		data = data.replace(/\[img\]([^"]+?)\[\/img\]/gi,'<img src="$1" />');
		data = data.replace(/\[img='?([^"]*?)'?,'?(left|right)'?\]\[\/img\]/gi,'<img src="$1" style="float: $2" />');
		data = data.replace(/\[img='?([^"]*?)'?\]\[\/img\]/gi,'<img src="$1" />');
		
		// [quote]
		// data = data.replace(/\[quote\]/gi, '<blockquote>');
		// data = data.replace(/\[\/quote\]/gi, '</blockquote>');
		
		// [size]
		data = data.replace(/\[size=(\d+)\](.*?)\[\/size\]/gi,'<span style="font-size: $1pt">$2</span>');
		
		// [color]
		data = data.replace(/\[color=([#a-z0-9]*?)\](.*?)\[\/color\]/gi,'<span style="color: $1">$2</span>');
		
		// [font]
		data = data.replace(/\[font='?([a-z,\- ]*?)'?\](.*?)\[\/font\]/gi,'<span style="font-family: $1">$2</span>');
		
		// [align]
		data = data.replace(/\[align=(left|right|center|justify)\](.*?)\[\/align\]/gi,'<div style="text-align: $1">$2</div>');
		
		// [*]
		data = data.replace(/\[\*\](.*?)(?=\[\*\]|\[\/list\])/gi,'<li>$1</li>');
		
		// [list]
		data = data.replace(/\[list\]/gi, '<ul>');
		data = data.replace(/\[list=1\]/gi, '<ul style="list-style-type: decimal">');
		data = data.replace(/\[\/list]/gi, '</ul>');
		
		// [table]
		data = data.replace(/\[table\]/gi, '<table border="1" cellspacing="1" cellpadding="1" style="width: 500px;">');
		data = data.replace(/\[\/table\]/gi, '</table>');
		// [tr]
		data = data.replace(/\[tr\]/gi, '<tr>');
		data = data.replace(/\[\/tr\]/gi, '</tr>');
		// [td]
		data = data.replace(/\[td\]/gi, '<td>');
		data = data.replace(/\[\/td\]/gi, '</td>');
		
		// smileys
		for (var i = 0; i < this.editor.config.smiley_descriptions.length; i++) {
			var smileyCode = this.editor.config.smiley_descriptions[i].replace(/</g, '&lt;').replace(/>/g, '&gt;');
			var regExp = new RegExp('(\\s|>|^)'+WCF.String.escapeRegExp(smileyCode)+'(?=\\s|<|$)', 'gi');
			data = data.replace(regExp, '$1<img src="' + this.editor.config.smiley_path + this.editor.config.smiley_images[i] + '" class="smiley" alt="'+smileyCode+'" />');
		}
		
		// remove "javascript:"
		data = data.replace(/(javascript):/gi, '$1<span></span>:');
		
		// insert codes
		if ($.getLength($cachedCodes)) {
			for (var $key in $cachedCodes) {
				var $regex = new RegExp('@@' + $key + '@@', 'g');
				data = data.replace($regex, $cachedCodes[$key]);
			}
		}
		
		return data;
	};
	
	/**
	 * Converts html to bbcodes.
	 */
	var toDataFormat = function(html, fixForBody) {
		if (html == '<br>' || html == '<p><br></p>') {
			return "";
		}
		
		// Convert <br> to line breaks.
		html = html.replace(/<br><\/p>/gi,"\n");
		html = html.replace(/<br(?=[ \/>]).*?>/gi, '\r\n');
		html = html.replace(/<p>/gi,"");
		html = html.replace(/<\/p>/gi,"\n");
		html = html.replace(/&nbsp;/gi," ");
		
		// [email]
		html = html.replace(/<a .*?href=(["'])mailto:(.+?)\1.*?>([\s\S]+?)<\/a>/gi, '[email=$2]$3[/email]');
		
		// [url]
		html = html.replace(/<a .*?href=(["'])(.+?)\1.*?>([\s\S]+?)<\/a>/gi, function(match, x, url, text) {
			if (url == text) return '[url]' + url + '[/url]';
			
			return "[url='" + url + "']" + text + "[/url]";
		});
		
		// [b]
		html = html.replace(/<(?:b|strong)>/gi, '[b]');
		html = html.replace(/<\/(?:b|strong)>/gi, '[/b]');
		
		// [i]
		html = html.replace(/<(?:i|em)>/gi, '[i]');
		html = html.replace(/<\/(?:i|em)>/gi, '[/i]');
		
		// [u]
		html = html.replace(/<u>/gi, '[u]');
		html = html.replace(/<\/u>/gi, '[/u]');
		
		// [s]
		html = html.replace(/<s>/gi, '[s]');
		html = html.replace(/<\/s>/gi, '[/s]');
		
		// [sub
		html = html.replace(/<sub>/gi, '[sub]');
		html = html.replace(/<\/sub>/gi, '[/sub]');
		
		// [sup]
		html = html.replace(/<sup>/gi, '[sup]');
		html = html.replace(/<\/sup>/gi, '[/sup]');
		
		// smileys
		html = html.replace(/<img [^>]*?alt="([^"]+?)" class="smiley".*?>/gi, '$1'); // firefox
		html = html.replace(/<img [^>]*?class="smiley" alt="([^"]+?)".*?>/gi, '$1'); // chrome, ie
		
		// [img]
		html = html.replace(/<img [^>]*?src=(["'])([^"']+?)\1 style="float: (left|right)".*?>/gi, "[img='$2',$3][/img]");
		html = html.replace(/<img [^>]*?src=(["'])([^"']+?)\1.*?>/gi, '[img]$2[/img]');
		
		// [quote]
		// html = html.replace(/<blockquote>/gi, '[quote]');
		// html = html.replace(/\n*<\/blockquote>/gi, '[/quote]');
		
		// [color]
		html = html.replace(/<span style="color: ?rgb\((\d{1,3}), ?(\d{1,3}), ?(\d{1,3})\);?">([\s\S]*?)<\/span>/gi, function(match, r, g, b, text) {
			var $hex = ("0123456789ABCDEF".charAt((r - r % 16) / 16) + '' + "0123456789ABCDEF".charAt(r % 16)) + '' + ("0123456789ABCDEF".charAt((g - g % 16) / 16) + '' + "0123456789ABCDEF".charAt(g % 16)) + '' + ("0123456789ABCDEF".charAt((b - b % 16) / 16) + '' + "0123456789ABCDEF".charAt(b % 16));
			
			return "[color=#" + $hex + "]" + text + "[/color]";
		});
		html = html.replace(/<span style="color: ?(.*?);?">([\s\S]*?)<\/span>/gi, "[color=$1]$2[/color]");
		
		// [size]
		html = html.replace(/<span style="font-size: ?(\d+)pt;?">([\s\S]*?)<\/span>/gi, "[size=$1]$2[/size]");
		
		// [font]
		html = html.replace(/<span style="font-family: ?(.*?);?">([\s\S]*?)<\/span>/gi, "[font='$1']$2[/font]");
		
		// [align]
		html = html.replace(/<div style="text-align: ?(left|center|right|justify);? ?">([\s\S]*?)<\/div>/gi, "[align=$1]$2[/align]");
		
		// [*]
		html = html.replace(/<li>/gi, '[*]');
		html = html.replace(/<\/li>/gi, '');
		
		// [list]
		html = html.replace(/<ul>/gi, '[list]');
		html = html.replace(/<(ol|ul style="list-style-type: decimal")>/gi, '[list=1]');
		html = html.replace(/<\/(ul|ol)>/gi, '[/list]');
		
		// [table]
		html = html.replace(/<table[^>]*>/gi, '[table]');
		html = html.replace(/<\/table>/gi, '[/table]');
		
		// remove empty <tr>s
		html = html.replace(/<tr><\/tr>/gi, '');
		// [tr]
		html = html.replace(/<tr>/gi, '[tr]');
		html = html.replace(/<\/tr>/gi, '[/tr]');
		
		// [td]+[align]
		html = html.replace(/<td style="text-align: ?(left|center|right|justify);? ?">([\s\S]*?)<\/td>/gi, "[td][align=$1]$2[/align][/td]");
		
		// [td]
		html = html.replace(/<td>/gi, '[td]');
		html = html.replace(/<\/td>/gi, '[/td]');
		
		// Remove remaining tags.
		html = html.replace(/<[^>]+>/g, '');
		
		// Restore <, > and &
		html = html.replace(/&lt;/g, '<');
		html = html.replace(/&gt;/g, '>');
		html = html.replace(/&amp;/g, '&');
		
		// Restore (and )
		html = html.replace(/%28/g, '(');
		html = html.replace(/%29/g, ')');
		
		// Restore %20
		html = html.replace(/%20/g, ' ');
		
		return html;
	};
})();
