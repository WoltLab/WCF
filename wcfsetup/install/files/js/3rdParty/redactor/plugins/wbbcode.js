if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Provides the smiley button and modifies the source mode to transform HTML into BBCodes.
 * 
 * @author	Alexander Ebert, Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wbbcode = {
	/**
	 * Initializes the RedactorPlugins.wbbcode plugin.
	 */
	init: function() {
		var $identifier = this.$source.wcfIdentify();
		
		this.opts.initCallback = $.proxy(function() {
			// use stored editor contents
			var $content = $.trim(this.getOption('wOriginalValue'));
			if ($content.length) {
				this.toggle();
				this.$source.val($content);
				this.toggle();
			}
			
			delete this.opts.wOriginalValue;
		}, this);
		
		this.opts.pasteBeforeCallback = $.proxy(this._wPasteBeforeCallback, this);
		this.opts.pasteAfterCallback = $.proxy(this._wPasteAfterCallback, this);
		
		var $mpSyncClean = this.syncClean;
		var self = this;
		this.syncClean = function(html) {
			html = html.replace(/<p><br([^>]+)?><\/p>/g, '<p>@@@wcf_empty_line@@@</p>');
			return $mpSyncClean.call(self, html);
		};
		
		if (this.getOption('wAutosaveOnce')) {
			this._saveTextToStorage();
			delete this.opts.wAutosaveOnce;
		}
		
		// we do not support table heads
		var $tableButton = this.buttonGet('table');
		if ($tableButton.length) {
			var $addHead = $tableButton.data('dropdown').children('a.redactor_dropdown_add_head');
			
			// drop divider
			$addHead.prev().remove();
			
			// drop 'delete head'
			$addHead.next().remove();
			
			// drop 'add head'
			$addHead.remove();
			
			// toggle dropdown options
			$tableButton.click($.proxy(this._tableButtonClick, this));
		}
		
		// handle 'insert quote' button
		WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'insertBBCode_quote_' + $identifier, $.proxy(function(data) {
			data.cancel = true;
			
			this._handleInsertQuote();
		}, this));
		
		// handle keydown
		WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'keydown_' + $identifier, $.proxy(this._wKeydownCallback, this));
		WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'keyup_' + $identifier, $.proxy(this._wKeyupCallback, this));
	},
	
	/**
	 * Toggles features within the table dropdown.
	 * 
	 * @param	object		event
	 */
	_tableButtonClick: function(event) {
		var $button = $(event.currentTarget);
		if (!$button.hasClass('dropact')) {
			return;
		}
		
		var $current = this.getBlock() || this.getCurrent();
		var $dropdown = $button.data('dropdown');
		
		// within table
		$dropdown.children('li').show();
		var $insertTable = $dropdown.find('> li > .redactor_dropdown_insert_table').parent();
		if ($current.tagName == 'TD') {
			$insertTable.hide().next().hide();
		}
		else {
			$insertTable.nextAll().hide();
		}
	},
	
	/**
	 * Inserts a smiley, optionally trying to register a new smiley.
	 * 
	 * @param	string		smileyCode
	 * @param	string		smileyPath
	 * @param	boolean		registerSmiley
	 */
	insertSmiley: function(smileyCode, smileyPath, registerSmiley) {
		if (registerSmiley) {
			this.registerSmiley(smileyCode, smileyPath);
		}
		
		if (this.opts.visual) {
			this.bufferSet();
			
			this.$editor.focus();
			
			this.insertHtml('&nbsp;<img src="' + smileyPath + '" class="smiley" alt="' + smileyCode + '" />&nbsp;');
			
			if (this.opts.air) this.$air.fadeOut(100);
			this.sync();
		}
		else {
			this.insertAtCaret(' ' + smileyCode + ' ');
		}
	},
	
	/**
	 * Registers a new smiley, returns false if the smiley code is already registered.
	 * 
	 * @param	string		smileyCode
	 * @param	string		smileyPath
	 * @return	boolean
	 */
	registerSmiley: function(smileyCode, smileyPath) {
		if (__REDACTOR_SMILIES[smileyCode]) {
			return false;
		}
		
		__REDACTOR_SMILIES[smileyCode] = smileyPath;
		
		return true;
	},
	
	/**
	 * Overwrites $.Redactor.toggle() to transform the source mode into a BBCode view.
	 * 
	 * @see		$.Redactor.toggle()
	 * @param	string		direct
	 */
	toggle: function(direct) {
		if (this.opts.visual) {
			this.sync(undefined, true);
			this.toggleCode(direct);
			this.$source.val(this.convertFromHtml(this.$source.val()));
			
			this.buttonGet('html').children('i').removeClass('fa-square-o').addClass('fa-square');
		}
		else {
			this.$source.val(this.convertToHtml(this.$source.val()));
			this.toggleVisual();
			this._observeQuotes();
			
			this.buttonGet('html').children('i').removeClass('fa-square').addClass('fa-square-o');
		}
	},
	
	/**
	 * Converts source contents from HTML into BBCode.
	 * 
	 * @param	string		html
	 */
	convertFromHtml: function(html) {
		WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'beforeConvertFromHtml', { html: html });
		
		// revert conversion of special characters
		html = html.replace(/&trade;/gi, '\u2122');
		html = html.replace(/&copy;/gi, '\u00a9');
		html = html.replace(/&hellip;/gi, '\u2026');
		html = html.replace(/&mdash;/gi, '\u2014');
		html = html.replace(/&dash;/gi, '\u2010');
		
		// drop all new lines
		html = html.replace(/\r?\n/g, '');
		
		// convert paragraphs into single lines
		var $parts = html.split(/(<\/?(?:div|p)>)/);
		var $tmp = '';
		var $buffer = '';
		for (var $i = 0; $i < $parts.length; $i++) {
			var $part = $parts[$i];
			if ($part == '<p>' || $part == '<div>') {
				continue;
			}
			else if ($part == '</p>' || $part == '</div>') {
				$buffer = $.trim($buffer);
				if ($buffer != '@@@wcf_empty_line@@@') {
					$buffer += "\n";
				}
				
				$tmp += $buffer;
				$buffer = '';
			}
			else {
				if ($i == 0 || $i + 1 == $parts.length) {
					$tmp += $part;
				}
				else {
					$buffer += $part;
				}
			}
		}
		
		if ($buffer) {
			$tmp += $buffer;
			$buffer = '';
		}
		
		html = $tmp;
		
		html = html.replace(/@@@wcf_empty_line@@@/g, '\n');
		html = html.replace(/\n\n$/, '\n');
		
		// convert all <br> into \n
		html = html.replace(/<br>$/, '');
		html = html.replace(/<br>/g, '\n');
		
		// drop <br>, they are pointless because the editor already adds a newline after them
		html = html.replace(/<br>/g, '');
		html = html.replace(/&nbsp;/gi, " ");
		
		// [quote]
		html = html.replace(/<blockquote class="quoteBox" cite="([^"]+)?" data-author="([^"]+)?">\n?<div[^>]+>\n?<header(?:[^>]*?)>[\s\S]*?<\/header>/gi, function(match, link, author, innerContent) {
			var $quote;
			
			if (author) author = WCF.String.unescapeHTML(author);
			if (link) link = WCF.String.unescapeHTML(link);
			
			if (link) {
				$quote = "[quote='" + author + "','" + link + "']";
			}
			else if (author) {
				$quote = "[quote='" + author + "']";
			}
			else {
				$quote = "[quote]";
			}
			
			return $quote;
		});
		html = html.replace(/(?:\n*)<\/blockquote>\n?/gi, '[/quote]\n');
		
		// [email]
		html = html.replace(/<a [^>]*?href=(["'])mailto:(.+?)\1.*?>([\s\S]+?)<\/a>/gi, '[email=$2]$3[/email]');
		
		// [url]
		html = html.replace(/<a [^>]*?href=(["'])(.+?)\1.*?>([\s\S]+?)<\/a>/gi, function(match, x, url, text) {
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
		html = html.replace(/<(?:s(trike)?|del)>/gi, '[s]');
		html = html.replace(/<\/(?:s(trike)?|del)>/gi, '[/s]');
		
		// [sub]
		html = html.replace(/<sub>/gi, '[sub]');
		html = html.replace(/<\/sub>/gi, '[/sub]');
		
		// [sup]
		html = html.replace(/<sup>/gi, '[sup]');
		html = html.replace(/<\/sup>/gi, '[/sup]');
		
		// smileys
		html = html.replace(/<img [^>]*?alt="([^"]+?)" class="smiley".*?> ?/gi, '$1 '); // firefox
		html = html.replace(/<img [^>]*?class="smiley" alt="([^"]+?)".*?> ?/gi, '$1 '); // chrome, ie
		
		// attachments
		html = html.replace(/<img [^>]*?class="redactorEmbeddedAttachment" data-attachment-id="(\d+)"( style="float: (left|right)")?>/gi, function(match, attachmentID, styleTag, alignment) {
			if (alignment) {
				return '[attach=' + attachmentID + ',' + alignment + '][/attach]';
			}
			
			return '[attach=' + attachmentID + '][/attach]';
		});
		
		// [img]
		html = html.replace(/<img [^>]*?src=(["'])([^"']+?)\1 style="float: (left|right)[^"]*".*?>/gi, "[img='$2',$3][/img]");
		html = html.replace(/<img [^>]*?src=(["'])([^"']+?)\1.*?>/gi, '[img]$2[/img]');
		
		// handle [color], [size], [font] and [tt]
		var $components = html.split(/(<\/?span[^>]*>)/);
		
		var $buffer = [ ];
		var $openElements = [ ];
		var $result = '';
		
		for (var $i = 0; $i < $components.length; $i++) {
			var $value = $components[$i];
			
			if ($value == '</span>') {
				var $opening = $openElements.pop();
				var $tmp = $opening.start + $buffer.pop() + $opening.end;
				
				if ($buffer.length) {
					$buffer[$buffer.length - 1] += $tmp;
				}
				else {
					$result += $tmp;
				}
			}
			else {
				if ($value.match(/^<span style="([^"]+)">/)) {
					var $style = RegExp.$1;
					var $start;
					var $end;
					
					if ($style.match(/^color: ?rgb\((\d{1,3}), ?(\d{1,3}), ?(\d{1,3})\);?$/i)) {
						var $r = RegExp.$1;
						var $g = RegExp.$2;
						var $b = RegExp.$3;
						
						var $hex = ("0123456789ABCDEF".charAt(($r - $r % 16) / 16) + '' + "0123456789ABCDEF".charAt($r % 16)) + '' + ("0123456789ABCDEF".charAt(($g - $g % 16) / 16) + '' + "0123456789ABCDEF".charAt($g % 16)) + '' + ("0123456789ABCDEF".charAt(($b - $b % 16) / 16) + '' + "0123456789ABCDEF".charAt($b % 16));
						$start = '[color=#' + $hex + ']';
						$end = '[/color]';
					}
					else if ($style.match(/^color: ?(.*?);?$/i)) {
						$start = '[color=' + RegExp.$1 + ']';
						$end = '[/color]';
					}
					else if ($style.match(/^font-size: ?(\d+)pt;?$/i)) {
						$start = '[size=' + RegExp.$1 + ']';
						$end = '[/size]';
					}
					else if ($style.match(/^font-family: ?(.*?);?$/)) {
						$start = '[font=' + RegExp.$1.replace(/'/g, '') + ']';
						$end = '[/font]';
					}
					else {
						$start = '<span style="' + $style + '">';
						$end = '</span>';
					}
					
					$buffer[$buffer.length] = '';
					$openElements[$buffer.length] = {
						start: $start,
						end: $end
					};
				}
				else if ($value.match(/^<span class="inlineCode">/)) {
					$buffer[$buffer.length] = '';
					$openElements[$buffer.length] = {
						start: '[tt]',
						end: '[/tt]'
					};
				}
				else {
					if ($buffer.length) {
						$buffer[$buffer.length - 1] += $value;
					}
					else {
						$result += $value;
					}
				}
			}
		}
		
		html = $result;
		
		// [align]
		html = html.replace(/<(div|p) style="text-align: ?(left|center|right|justify);? ?">([\s\S]*?)\n/gi, function(match, tag, alignment, content) {
			return '[align=' + alignment + ']' + $.trim(content) + '[/align]';
		});
		
		// [*]
		html = html.replace(/<li>/gi, '[*]');
		html = html.replace(/<\/li>/gi, '');
		
		// [list]
		html = html.replace(/<ul>/gi, '[list]');
		html = html.replace(/<(ol|ul style="list-style-type: decimal")>/gi, '[list=1]');
		html = html.replace(/<ul style="list-style-type: (none|circle|square|disc|decimal|lower-roman|upper-roman|decimal-leading-zero|lower-greek|lower-latin|upper-latin|armenian|georgian)">/gi, '[list=$1]');
		html = html.replace(/<\/(ul|ol)>/gi, '[/list]');
		
		// [table]
		html = html.replace(/<table[^>]*>/gi, '[table]\n');
		html = html.replace(/<\/table>/gi, '[/table]\n');
		
		// remove <tbody>
		html = html.replace(/<tbody>([\s\S]*?)<\/tbody>/, function(match, p1) {
			return $.trim(p1);
		});
		
		// remove empty <tr>s
		html = html.replace(/<tr><\/tr>/gi, '');
		// [tr]
		html = html.replace(/<tr>/gi, '[tr]\n');
		html = html.replace(/<\/tr>/gi, '[/tr]\n');
		
		// [td]+[align]
		html = html.replace(/<td style="text-align: ?(left|center|right|justify);? ?">([\s\S]*?)<\/td>/gi, "[td][align=$1]$2[/align][/td]");
		
		// [td]
		html = html.replace(/(\t)*<td>(\t)*/gi, '[td]');
		html = html.replace(/(\t)*<\/td>/gi, '[/td]\n');
		
		// cache redactor's selection markers
		var $cachedMarkers = { };
		html.replace(/<span id="selection-marker-\d+" class="redactor-selection-marker"><\/span>/, function(match) {
			var $key = match.hashCode();
			$cachedMarkers[$key] = match.replace(/\$/g, '$$$$');
			return '@@' + $key + '@@';
		});
		
		WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'convertFromHtml', { html: html });
		
		// Remove remaining tags.
		html = html.replace(/<[^(<|>)]+>/g, '');
		
		// insert redactor's selection markers
		if ($.getLength($cachedMarkers)) {
			for (var $key in $cachedMarkers) {
				var $regex = new RegExp('@@' + $key + '@@', 'g');
				data = data.replace($regex, $cachedMarkers[$key]);
			}
		}
		
		// Restore <, > and &
		html = html.replace(/&lt;/g, '<');
		html = html.replace(/&gt;/g, '>');
		html = html.replace(/&amp;/g, '&');
		
		// Restore ( and )
		html = html.replace(/%28/g, '(');
		html = html.replace(/%29/g, ')');
		
		// Restore %20
		//html = html.replace(/%20/g, ' ');
		
		// cache source code tags to preserve leading tabs
		var $cachedCodes = { };
		for (var $i = 0, $length = __REDACTOR_SOURCE_BBCODES.length; $i < $length; $i++) {
			var $bbcode = __REDACTOR_SOURCE_BBCODES[$i];
			
			var $regExp = new RegExp('\\[' + $bbcode + '([\\S\\s]+?)\\[\\/' + $bbcode + '\\]', 'gi');
			html = html.replace($regExp, function(match) {
				var $key = match.hashCode();
				$cachedCodes[$key] = match.replace(/\$/g, '$$$$');
				return '@@' + $key + '@@';
			});
		}
		
		WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'afterConvertFromHtml', { html: html });
		
		// insert codes
		if ($.getLength($cachedCodes)) {
			for (var $key in $cachedCodes) {
				var $regex = new RegExp('@@' + $key + '@@', 'g');
				html = html.replace($regex, $cachedCodes[$key]);
			}
		}
		
		// remove all leading and trailing whitespaces, but add one empty line at the end
		html = $.trim(html);
		html += "\n";
		
		return html;
	},
	
	/**
	 * Converts source contents from BBCode to HTML.
	 * 
	 * @param	string		data
	 */
	convertToHtml: function(data) {
		WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'beforeConvertToHtml', { data: data });
		
		// remove 0x200B (unicode zero width space)
		data = this.removeZeroWidthSpace(data);
		
		// Convert & to its HTML entity.
		data = data.replace(/&/g, '&amp;');
		
		// Convert < and > to their HTML entities.
		data = data.replace(/</g, '&lt;');
		data = data.replace(/>/g, '&gt;');
		
		// cache source code tags
		var $cachedCodes = { };
		for (var $i = 0, $length = __REDACTOR_SOURCE_BBCODES.length; $i < $length; $i++) {
			var $bbcode = __REDACTOR_SOURCE_BBCODES[$i];
			
			var $regExp = new RegExp('\\[' + $bbcode + '([\\S\\s]+?)\\[\\/' + $bbcode + '\\]', 'gi');
			data = data.replace($regExp, function(match) {
				var $key = match.hashCode();
				$cachedCodes[$key] = match.replace(/\$/g, '$$$$');
				return '@@' + $key + '@@';
			});
		}
		
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
		data = data.replace(/\[s\](.*?)\[\/s]/gi, '<strike>$1</strike>');
		
		// [sub]
		data = data.replace(/\[sub\](.*?)\[\/sub]/gi, '<sub>$1</sub>');
		
		// [sup]
		data = data.replace(/\[sup\](.*?)\[\/sup]/gi, '<sup>$1</sup>');
			
		// [img]
		data = data.replace(/\[img\]([^"]+?)\[\/img\]/gi,'<img src="$1" />');
		data = data.replace(/\[img='?([^"]*?)'?,'?(left|right)'?\]\[\/img\]/gi,'<img src="$1" style="float: $2" />');
		data = data.replace(/\[img='?([^"]*?)'?\]\[\/img\]/gi,'<img src="$1" />');
		
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
		data = data.replace(/\[list=a\]/gi, '<ul style="list-style-type: lower-latin">');
		data = data.replace(/\[list=(none|circle|square|disc|decimal|lower-roman|upper-roman|decimal-leading-zero|lower-greek|lower-latin|upper-latin|armenian|georgian)\]/gi, '<ul style="list-style-type: $1">');
		data = data.replace(/\[\/list]/gi, '</ul>');
		
		// trim whitespaces within [table]
		data = data.replace(/\[table\]([\S\s]*?)\[\/table\]/gi, function(match, p1) {
			return '[table]' + $.trim(p1) + '[/table]';
		});
		
		// [table]
		data = data.replace(/\[table\]/gi, '<table border="1" cellspacing="1" cellpadding="1" style="width: 500px;">');
		data = data.replace(/\[\/table\]/gi, '</table>');
		// [tr]
		data = data.replace(/\[tr\]/gi, '<tr>');
		data = data.replace(/\[\/tr\]/gi, '</tr>');
		// [td]
		data = data.replace(/\[td\]/gi, '<td>');
		data = data.replace(/\[\/td\]/gi, '</td>');
		
		// trim whitespaces within <td>
		data = data.replace(/<td>([\S\s]*?)<\/td>/gi, function(match, p1) {
			var $tdContent = $.trim(p1);
			if (!$tdContent.length) {
				// unicode zero-width space
				$tdContent = '&#8203;';
			}
			
			return '<td>' + $tdContent + '</td>';
		});
		
		// attachments
		var $attachmentUrl = this.getOption('wAttachmentUrl');
		if ($attachmentUrl) {
			var $imageAttachmentIDs = this._getImageAttachmentIDs();
			
			data = data.replace(/\[attach=(\d+)(,[^\]]*)?\]\[\/attach\]/g, function(match, attachmentID, alignment) {
				attachmentID = parseInt(attachmentID);
				
				if (WCF.inArray(attachmentID, $imageAttachmentIDs)) {
					var $style = '';
					if (alignment) {
						if (alignment.match(/^,'?(left|right)'?/)) {
							$style = ' style="float: ' + RegExp.$1 + '"';
						}
					}
					
					return '<img src="' + $attachmentUrl.replace(/987654321/, attachmentID) + '" class="redactorEmbeddedAttachment" data-attachment-id="' + attachmentID + '"' + $style + ' />';
				}
				
				return match;
			});
		}
		
		// smileys
		for (var smileyCode in __REDACTOR_SMILIES) {
			$smileyCode = smileyCode.replace(/</g, '&lt;').replace(/>/g, '&gt;');
			var regExp = new RegExp('(\\s|>|^)' + WCF.String.escapeRegExp($smileyCode) + '(?=\\s|<|$)', 'gi');
			data = data.replace(regExp, '$1<img src="' + __REDACTOR_SMILIES[smileyCode] + '" class="smiley" alt="' + $smileyCode + '" />');
		}
		
		// remove "javascript:"
		data = data.replace(/(javascript):/gi, '$1<span></span>:');
		
		// unify line breaks
		data = data.replace(/(\r|\r\n)/g, "\n");
		
		// extract [quote] bbcodes to prevent line break handling below
		var $cachedQuotes = [ ];
		var $knownQuotes = [ ];
		for (var $i = 0; $i < 5; $i++) {
			var $foundQuotes = false;
			
			data = data.replace(/\[quote.*?\]((?!\[quote)[\s\S])*?\[\/quote\]/gi, function(match) {
				var $key = match.hashCode();
				$cachedQuotes.push({
					hashCode: $key,
					content: match.replace(/\$/g, '$$$$')
				});
				$knownQuotes.push($key.toString());
				
				$foundQuotes = true;
				
				return '@@' + $key + '@@';
			});
			
			// we found no more quotes
			if (!$foundQuotes) {
				break;
			}
		}
		
		// add newlines before and after [quote] tags
		data = data.replace(/(\[quote.*?\])/gi, '$1\n');
		data = data.replace(/(\[\/quote\])/gi, '\n$1');
		
		// drop trailing line breaks
		data = data.replace(/\n*$/, '');
		
		// convert line breaks into <p></p> or empty lines to <p><br></p>
		var $tmp = data.split("\n");
		data = '';
		for (var $i = 0, $length = $tmp.length; $i < $length; $i++) {
			var $line = $.trim($tmp[$i]);
			
			if ($line.match(/^<([a-z]+)/)) {
				data += $line;
				
				if (!this.opts.rBlockTest.test(RegExp.$1.toUpperCase())) {
					data += '<br>';
				}
			}
			else {
				if (!$line) {
					$line = '<br>';
				}
				else if ($line.match(/^@@([0-9\-]+)@@$/)) {
					if (WCF.inArray(RegExp.$1, $knownQuotes)) {
						// prevent quote being nested inside a <p> block
						data += $line;
						continue;
					}
				}
				
				data += '<p>' + $line + '</p>';
			}
		}
		
		// insert codes
		if ($.getLength($cachedCodes)) {
			for (var $key in $cachedCodes) {
				var $regex = new RegExp('@@' + $key + '@@', 'g');
				data = data.replace($regex, $cachedCodes[$key]);
			}
			
			// [tt]
			data = data.replace(/\[tt\](.*?)\[\/tt\]/gi, '<span class="inlineCode">$1</span>');
		}
		
		// preserve leading whitespaces in [code] tags
		data = data.replace(/\[code\][\S\s]*?\[\/code\]/, '<pre>$&</pre>');
		
		// insert quotes
		if ($cachedQuotes.length) {
			// [quote]
			var $unquoteString = function(quotedString) {
				return quotedString.replace(/^['"]/, '').replace(/['"]$/, '');
			};
			
			var self = this;
			var $transformQuote = function(quote) {
				return quote.replace(/\[quote([^\]]+)?\]([\S\s]*)\[\/quote\]?/gi, $.proxy(function(match, attributes, innerContent) {
					var $author = '';
					var $link = '';
					
					if (attributes) {
						attributes = attributes.substr(1);
						attributes = attributes.split(',');
						
						switch (attributes.length) {
							case 1:
								$author = attributes[0];
							break;
							
							case 2:
								$author = attributes[0];
								$link = attributes[1];
							break;
						}
						
						$author = WCF.String.escapeHTML($unquoteString($.trim($author)));
						$link = WCF.String.escapeHTML($unquoteString($.trim($link)));
					}
					
					var $quote = '<blockquote class="quoteBox" cite="' + $link + '" data-author="' + $author + '">'
						+ '<div class="container containerPadding">'
							+ '<header contenteditable="false">'
								+ '<h3>'
									+ self._buildQuoteHeader($author, $link)
								+ '</h3>'
								+ '<a class="redactorQuoteEdit"></a>'
							+ '</header>';
					
					innerContent = $.trim(innerContent);
					var $tmp = '';
					
					if (innerContent.length) {
						var $lines = innerContent.split('\n');
						for (var $i = 0; $i < $lines.length; $i++) {
							var $line = $lines[$i];
							if ($line.length === 0) {
								$line = self.opts.invisibleSpace;
							}
							else if ($line.match(/^@@([0-9\-]+)@@$/)) {
								if (WCF.inArray(RegExp.$1, $knownQuotes)) {
									// prevent quote being nested inside a <div> block
									$tmp += $line;
									continue;
								}
							}
							
							$tmp += '<div>' + $line + '</div>';
						}
					}
					else {
						$tmp = '<div>' + self.opts.invisibleSpace + '</div>';
					}
					
					$quote += $tmp;
					$quote += '</blockquote>';
					
					return $quote;
				}, this));
			};
			
			// reinsert quotes in reverse order, adding the most outer quotes first
			for (var $i = $cachedQuotes.length - 1; $i >= 0; $i--) {
				var $cachedQuote = $cachedQuotes[$i];
				var $regex = new RegExp('@@' + $cachedQuote.hashCode + '@@', 'g');
				data = data.replace($regex, $transformQuote($cachedQuote.content));
			}
		}
		
		WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'afterConvertToHtml', { data: data });
		
		return data;
	},
	
	/**
	 * Converts certain HTML elements prior to paste in order to preserve formattings.
	 * 
	 * @param	string		html
	 * @return	string
	 */
	_wPasteBeforeCallback: function(html) {
		var $levels = {
			1: 24,
			2: 22,
			3: 18,
			4: 14,
			5: 12,
			6: 10
		};
		
		// replace <h1> ... </h6> tags
		html = html.replace(/<h([1-6])[^>]+>/g, function(match, level) {
			return '[size=' + $levels[level] + ']';
		});
		html = html.replace(/<\/h[1-6]>/g, '[/size]');
		
		// convert block-level elements
		html = html.replace(/<(article|header)[^>]+>/g, '<div>');
		html = html.replace(/<\/(article|header)>/g, '</div>');
		
		// replace nested elements e.g. <div><p>...</p></div>
		html = html.replace(/<(div|p)([^>]+)?><(div|p)([^>]+)?>/g, '<p>');
		html = html.replace(/<\/(div|p)><\/(div|p)>/g, '</p>@@@wcf_break@@@');
		
		WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'beforePaste', { html: html });
		
		return html;
	},
	
	/**
	 * Restores and fixes formatting before inserting pasted HTML into the editor.
	 * 
	 * @param	string		html
	 * @return	string
	 */
	_wPasteAfterCallback: function(html) {
		// replace <p /> with <p>...<br></p>
		html = html.replace(/<p>([\s\S]*?)<\/p>/g, '<p>$1<br></p>');
		
		// drop <header />
		html = html.replace(/<header[^>]*>/g, '');
		html = html.replace(/<\/header>/g, '');
		
		html = html.replace(/<div>.*?<\/div>/g, '<p>$1</p>');
		
		// drop lonely divs
		html = html.replace(/<\/?div>/g, '');
		
		html = html.replace(/@@@wcf_break@@@/g, '<p><br></p>');
		
		// drop lonely <p> opening tags
		html = html.replace(/<p><p>/g, '<p>');
		
		// restore font size
		html = html.replace(/\[size=(\d+)\]/g, '<p><br></p><p><inline style="font-size: $1pt">');
		html = html.replace(/\[\/size\]/g, '</inline></p><p><br></p>');
		
		// handle pasting of images in Firefox
		html = html.replace(/<img([^>]+)>/g, function(match, content) {
			match = match.replace(/data-mozilla-paste-image="0"/, 'data-mozilla-paste-image="0" style="display:none"');
			return match;
		});
		
		WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'afterPaste', { html: html });
		
		return html;
	},
	
	/**
	 * Inserts an attachment with live preview.
	 * 
	 * @param	integer		attachmentID
	 */
	insertAttachment: function(attachmentID) {
		attachmentID = parseInt(attachmentID);
		var $attachmentUrl = this.getOption('wAttachmentUrl');
		var $bbcode = '[attach=' + attachmentID + '][/attach]';
		
		var $imageAttachmentIDs = this._getImageAttachmentIDs();
		
		if ($attachmentUrl && WCF.inArray(attachmentID, $imageAttachmentIDs)) {
			this.insertDynamic(
				'<img src="' + $attachmentUrl.replace(/987654321/, attachmentID) + '" class="redactorEmbeddedAttachment" data-attachment-id="' + attachmentID + '" />',
				$bbcode
			);
		}
		else {
			this.insertDynamic($bbcode);
		}
	},
	
	/**
	 * Returns a list of attachments representing an image.
	 * 
	 * @return	array<integer>
	 */
	_getImageAttachmentIDs: function() {
		// WCF.Attachment.Upload may have no been initialized yet, fallback to static data
		var $imageAttachmentIDs = this.getOption('wAttachmentImageIDs') || [ ];
		if ($imageAttachmentIDs.length) {
			delete this.opts.wAttachmentImageIDs;
			
			return $imageAttachmentIDs;
		}
		
		var $data = {
			imageAttachmentIDs: [ ]
		};
		WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'getImageAttachments_' + this.$source.wcfIdentify(), $data);
		
		return $data.imageAttachmentIDs;
	},
	
	/**
	 * Handles up/down/delete/backspace key for quote boxes.
	 * 
	 * @param	object		data
	 */
	_wKeydownCallback: function(data) {
		switch (data.event.which) {
			case $.ui.keyCode.BACKSPACE:
			case $.ui.keyCode.DELETE:
			case $.ui.keyCode.DOWN:
			case $.ui.keyCode.UP:
				// handle keys
			break;
			
			default:
				return;
			break;
		}
		
		var $current = $(this.getCurrent());
		var $parent = this.getParent();
		$parent = ($parent) ? $($parent) : $parent;
		var $quote = ($parent) ? $parent.closest('blockquote.quoteBox', this.$editor.get()[0]) : { length: 0 };
		
		switch (data.event.which) {
			// backspace key
			case $.ui.keyCode.BACKSPACE:
				if (this.isCaret()) {
					if ($quote.length) {
						// check if quote is empty
						var $isEmpty = true;
						$quote.find('div > div').each(function() {
							if ($(this).text().replace(/\u200B/, '').length) {
								$isEmpty = false;
								return false;
							}
						});
						
						if ($isEmpty) {
							// expand selection and prevent delete
							var $selection = window.getSelection();
							if ($selection.rangeCount) $selection.removeAllRanges();
							
							var $quoteRange = document.createRange();
							$quoteRange.selectNode($quote[0]);
							$selection.addRange($quoteRange);
							
							data.cancel = true;
						}
					}
				}
				else {
					// check if selection contains a quote, turn on buffer if true
					var $contents = this.getRange().cloneContents();
					if (this.containsTag($contents, 'BLOCKQUOTE')) {
						this.bufferSet();
					}
				}
			break;
			
			// delete key
			case $.ui.keyCode.DELETE:
				if (this.isCaret()) {
					if (this.isEndOfElement($current[0]) && $current.next('blockquote').length) {
						// expand selection and prevent delete
						var $selection = window.getSelection();
						if ($selection.rangeCount) $selection.removeAllRanges();
						
						var $quoteRange = document.createRange();
						$quoteRange.selectNode($current.next()[0]);
						$selection.addRange($quoteRange);
						
						data.cancel = true;
					}
				}
				else {
					// check if selection contains a quote, turn on buffer if true
					var $contents = this.getRange().cloneContents();
					if (this.containsTag($contents, 'BLOCKQUOTE')) {
						this.bufferSet();
					}
				}
			break;
			
			// arrow down
			case $.ui.keyCode.DOWN:
				if ($current.next('blockquote.quoteBox').length) {
					this.selectionStart($current.next().find('> div > div:first'));
					
					data.cancel = true;
				}
				else if ($parent) {
					if ($parent.next('blockquote.quoteBox').length) {
						this.selectionStart($parent.next().find('> div > div:first'));
						
						data.cancel = true;
					}
					else if ($quote.length) {
						var $container = $current.closest('div', $quote[0]);
						if (!$container.next().length) {
							// check if there is an element after the quote
							if ($quote.next().length) {
								this.setSelectionStart($quote.next());
							}
							else {
								this.insertingAfterLastElement($quote);
							}
							
							data.cancel = true;
						}
					} 
				}
			break;
			
			// arrow up
			case $.ui.keyCode.UP:
				if (!$parent || !$quote.length) {
					return;
				}
				
				var $container = $current.closest('div', $quote[0]);
				var $prev = $container.prev();
				if ($prev[0].tagName === 'DIV') {
					return;
				}
				else if ($prev[0].tagName === 'BLOCKQUOTE') {
					// set focus to quote text rather than the element itself
					return;
					//this.selectionEnd($prev.find('> div > div:last'));
				}
				
				var $previousElement = $quote.prev();
				if ($previousElement.length === 0) {
					var $node = $(this.opts.emptyHtml);
					$node.insertBefore($quote);
					this.selectionStart($node);
				}
				else {
					if ($previousElement[0].tagName === 'BLOCKQUOTE') {
						// set focus to quote text rather than the element itself
						this.selectionEnd($previousElement.find('> div > div:last'));
					}
					else {
						// focus is wrong if the previous element is empty (e.g. only a newline present)
						if ($.trim($previousElement.html()) == '') {
							$previousElement.html(this.opts.invisibleSpace);
						}
						
						this.selectionEnd($previousElement);
					}
				}
				
				data.cancel = true;
			break;
		}
	},
	
	/**
	 * Handles quote deletion.
	 * 
	 * @param	object		data
	 */
	_wKeyupCallback: function(data) {
		if (data.event.which !== $.ui.keyCode.BACKSPACE && data.event.which !== $.ui.keyCode.DELETE) {
			return;
		}
		
		// check for empty <blockquote>
		this.$editor.find('blockquote').each(function(index, blockquote) {
			var $blockquote = $(blockquote);
			if (!$blockquote.find('> div > header').length) {
				$blockquote.remove();
			}
		});
	},
	
	/**
	 * Initializes source editing for quotes.
	 */
	_observeQuotes: function() {
		this.$editor.find('.redactorQuoteEdit:not(.jsRedactorQuoteEdit)').addClass('jsRedactorQuoteEdit').click($.proxy(this._observeQuotesClick, this));
	},
	
	/**
	 * Handles clicks on the 'edit quote' link.
	 * 
	 * @param	object		event
	 */
	_observeQuotesClick: function(event) {
		var $header = $(event.currentTarget).closest('header');
		var $tooltip = $('<span class="redactor-link-tooltip" />');
		
		$('<a href="#">' + WCF.Language.get('wcf.bbcode.quote.edit') + '</a>').click($.proxy(function(e) {
			e.preventDefault();
			
			this._openQuoteEditOverlay($(event.currentTarget).closest('blockquote.quoteBox'), false);
			$('.redactor-link-tooltip').remove();
		}, this)).appendTo($tooltip);
		
		var $offset = $header.offset();
		$tooltip.css({
			left: $offset.left + 'px',
			top: ($offset.top + 20) + 'px'
		});
		
		$('.redactor-link-tooltip').remove();
		$tooltip.appendTo(document.body);
	},
	
	/**
	 * Opens the quote source edit dialog.
	 * 
	 * @param	jQuery		quote
	 * @param	boolean		insertQuote
	 */
	_openQuoteEditOverlay: function(quote, insertQuote) {
		if (insertQuote) {
			this.modalInit(WCF.Language.get('wcf.bbcode.quote.insert'), this.opts.modal_quote, 300, $.proxy(function() {
				$('#redactorEditQuote').click($.proxy(function() {
					var $author = $('#redactorQuoteAuthor').val();
					var $link = WCF.String.escapeHTML($('#redactorQuoteLink').val());
					
					this.insertQuoteBBCode($author, $link);
					
					this.modalClose();
				}, this));
			}, this));
		}
		else {
			this.modalInit(WCF.Language.get('wcf.bbcode.quote.edit'), this.opts.modal_quote, 300, $.proxy(function() {
				if (!insertQuote) {
					$('#redactorQuoteAuthor').val(quote.data('author'));
					
					// do not use prop() here, an empty cite attribute would yield the page URL instead
					$('#redactorQuoteLink').val(WCF.String.unescapeHTML(quote.attr('cite')));
				}
				
				$('#redactorEditQuote').click($.proxy(function() {
					var $author = $('#redactorQuoteAuthor').val();
					quote.data('author', $author);
					quote.attr('data-author', $author);
					quote.prop('cite', WCF.String.escapeHTML($('#redactorQuoteLink').val()));
					
					this._updateQuoteHeader(quote);
					
					this.modalClose();
				}, this));
			}, this));
		}
	},
	
	/**
	 * Updates the quote's source.
	 * 
	 * @param	jQuery		quote
	 */
	_updateQuoteHeader: function(quote) {
		var $author = quote.data('author');
		var $link = quote.attr('cite');
		if ($link) $link = WCF.String.escapeHTML($link);
		
		quote.find('> div > header > h3').empty().append(this._buildQuoteHeader($author, $link));	
	},
	
	/**
	 * Inserts the quote BBCode.
	 * 
	 * @param	string		author
	 * @param	string		link
	 * @param	string		html
	 * @param	string		plainText
	 */
	insertQuoteBBCode: function(author, link, html, plainText) {
		var $bbcode = '[quote]';
		if (author) {
			if (link) {
				$bbcode = "[quote='" + author + "','" + link + "']";
			}
			else {
				$bbcode = "[quote='" + author + "']";
			}
		}
		
		if (plainText) $bbcode += plainText;
		$bbcode += '[/quote]';
		
		if (this.inWysiwygMode()) {
			$bbcode = this.convertToHtml($bbcode);
			this.insertHtml($bbcode);
			
			this._observeQuotes();
			
			this.$toolbar.find('a.re-__wcf_quote').addClass('redactor_button_disabled');
		}
		else {
			this.insertAtCaret($bbcode);
		}
	},
	
	/**
	 * Builds the quote source HTML.
	 * 
	 * @param	string		author
	 * @param	string		link
	 * @return	string
	 */
	_buildQuoteHeader: function(author, link) {
		var $header = '';
		// author is empty, check if link was provided and use it instead
		if (!author && link) {
			author = link;
			link = '';
		}
		
		if (author) {
			if (link) $header += '<a href="' + link + '">';
			
			$header += WCF.Language.get('wcf.bbcode.quote.title.javascript', { quoteAuthor: WCF.String.unescapeHTML(author) });
			
			if (link) $header += '</a>';
		}
		else {
			$header = '<small>' + WCF.Language.get('wcf.bbcode.quote.title.clickToSet') + '</small>';
		}
		
		return $header;
	},
	
	_handleInsertQuote: function() {
		this._openQuoteEditOverlay(null, true);
	}
};
