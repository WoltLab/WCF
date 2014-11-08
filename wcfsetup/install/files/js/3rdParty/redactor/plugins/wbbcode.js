if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Provides the smiley button and modifies the source mode to transform HTML into BBCodes.
 * 
 * @author	Alexander Ebert, Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wbbcode = function() {
	"use strict";
	
	return {
		/**
		 * Initializes the RedactorPlugins.wbbcode plugin.
		 */
		init: function() {
			var $identifier = this.$textarea.wcfIdentify();
			
			this.opts.initCallback = $.proxy(function() {
				// use stored editor contents
				var $content = $.trim(this.wutil.getOption('woltlab.originalValue'));
				if ($content.length) {
					this.wutil.replaceText($content);
					
					// ensure that the caret is not within a quote tag
					this.wutil.selectionEndOfEditor();
				}
				
				delete this.opts.woltlab.originalValue;
				
				$(document).trigger('resize');
			}, this);
			
			this.opts.pasteBeforeCallback = $.proxy(this.wbbcode._pasteBeforeCallback, this);
			this.opts.pasteCallback = $.proxy(this.wbbcode._pasteCallback, this);
			
			var $mpCleanOnSync = this.clean.onSync;
			this.clean.onSync = function(html) {
				html = html.replace(/<p><br([^>]+)?><\/p>/g, '<p>@@@wcf_empty_line@@@</p>');
				return $mpCleanOnSync.call(self, html);
			};
			
			if (this.wutil.getOption('woltlab.autosaveOnce')) {
				this.wutil.saveTextToStorage();
				delete this.opts.woltlab.autosaveOnce;
			}
			
			// we do not support table heads
			var $tableButton = this.button.get('table');
			if ($tableButton.length) {
				var $dropdown = $tableButton.data('dropdown');
				
				// remove head add/delete
				$dropdown.find('.redactor-dropdown-add_head').parent().remove();
				$dropdown.find('.redactor-dropdown-delete_head').parent().remove();
				
				// add visual divider
				$('<li class="dropdownDivider" />').insertBefore($dropdown.find('.redactor-dropdown-delete_table').parent());
				
				// toggle dropdown options
				$tableButton.click($.proxy(this.wbbcode._tableButtonClick, this));
			}
			
			// handle 'insert quote' button
			WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'insertBBCode_quote_' + $identifier, $.proxy(function(data) {
				data.cancel = true;
				
				this.wbbcode._handleInsertQuote();
			}, this));
			
			// handle keydown
			WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'keydown_' + $identifier, $.proxy(this.wbbcode._keydownCallback, this));
			WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'keyup_' + $identifier, $.proxy(this.wbbcode._keyupCallback, this));
			
			// disable automatic synchronization
			this.code.sync = function() { };
			
			// fix button label for source toggling
			var $tooltip = $('.redactor-toolbar-tooltip-html:not(.jsWbbcode)').addClass('jsWbbcode').text(WCF.Language.get('wcf.bbcode.button.toggleBBCode'));
			
			this.code.toggle = (function() {
				if (this.opts.visual) {
					this.code.startSync();
					
					this.code.showCode();
					this.$textarea.val(this.wbbcode.convertFromHtml(this.$textarea.val()));
					
					this.button.get('html').children('i').removeClass('fa-square-o').addClass('fa-square');
					$tooltip.text(WCF.Language.get('wcf.bbcode.button.toggleHTML'));
				}
				else {
					this.$textarea.val(this.wbbcode.convertToHtml(this.$textarea.val()));
					this.code.offset = this.$textarea.val().length;
					this.code.showVisual();
					this.wbbcode._fixQuotes();
					this.wutil.selectionEndOfEditor();
					this.wbbcode._observeQuotes();
					
					this.button.get('html').children('i').removeClass('fa-square').addClass('fa-square-o');
					$tooltip.text(WCF.Language.get('wcf.bbcode.button.toggleBBCode'));
				}
			}).bind(this);
			
			// insert a new line if user clicked into the editor and the last children is a quote (same behavior as arrow down)
			this.wutil.setOption('clickCallback', (function(event) {
				if (event.target === this.$editor[0]) {
					if (this.$editor[0].lastElementChild && this.$editor[0].lastElementChild.tagName === 'BLOCKQUOTE') {
						this.wutil.setCaretAfter($(this.$editor[0].lastElementChild));
					}
				}
			}).bind(this));
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
			
			var $current = this.selection.getBlock() || this.selection.getCurrent();
			var $dropdown = $button.data('dropdown');
			
			// within table
			$dropdown.children('li').show();
			var $insertTable = $dropdown.find('> li > .redactor-dropdown-insert_table').parent();
			if ($current.tagName == 'TD') {
				$insertTable.hide();
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
				this.wbbcode.registerSmiley(smileyCode, smileyPath);
			}
			
			if (this.opts.visual) {
				this.insert.html('&nbsp;<img src="' + smileyPath + '" class="smiley" alt="' + smileyCode + '" />&nbsp;', false);
			}
			else {
				this.wutil.insertAtCaret(' ' + smileyCode + ' ');
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
		 * Converts source contents from HTML into BBCode.
		 * 
		 * @param	string		html
		 */
		convertFromHtml: function(html) {
			WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'beforeConvertFromHtml', { html: html });
			
			// remove data-redactor-tag="" attribute
			html = html.replace(/(<[^>]+?) data-redactor-tag="[^"]+"/g, '$1');
			
			// remove zero-width space sometimes slipping through
			html = html.replace(/&#(8203|x200b);/g, '');
			
			// revert conversion of special characters
			html = html.replace(/&trade;/gi, '\u2122');
			html = html.replace(/&copy;/gi, '\u00a9');
			html = html.replace(/&hellip;/gi, '\u2026');
			html = html.replace(/&mdash;/gi, '\u2014');
			html = html.replace(/&dash;/gi, '\u2010');
			
			// preserve newlines in <pre> tags
			var $cachedPreTags = { };
			html = html.replace(/<pre>[\s\S]+?<\/pre>/g, function(match) {
				var $uuid = WCF.getUUID();
				$cachedPreTags[$uuid] = match;
				
				return '@@@' + $uuid + '@@@';
			});
			
			// drop all new lines
			html = html.replace(/\r?\n/g, '');
			
			// restore <pre> tags
			if ($.getLength($cachedPreTags)) {
				$.each($cachedPreTags, function(key, value) {
					html = html.replace('@@@' + key + '@@@', value);
				});
			}
			
			// remove empty links
			html = html.replace(/<a[^>]*?><\/a>/g, '');
			
			// drop empty paragraphs
			html = html.replace(/<p><\/p>/g, '');
			
			// remove <br> right in front of </p> (does not match <p><br></p> since it has been converted already)
			html = html.replace(/<br( \/)?><\/p>/g, '</p>');
			
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
			html = html.replace(/<blockquote class="quoteBox" cite="([^"]+)?" data-author="([^"]+)?"[^>]*?>\n?<div[^>]+>\n?<header[^>]*?>[\s\S]*?<\/header>/gi, function(match, link, author, innerContent) {
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
			
			// remove empty links
			html = html.replace(/<a[^>]+><\/a>/, '');
			
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
			
			// [sub]
			html = html.replace(/<sub>/gi, '[sub]');
			html = html.replace(/<\/sub>/gi, '[/sub]');
			
			// [sup]
			html = html.replace(/<sup>/gi, '[sup]');
			html = html.replace(/<\/sup>/gi, '[/sup]');
			
			// [s]
			html = html.replace(/<(?:s(trike)?|del)>/gi, '[s]');
			html = html.replace(/<\/(?:s(trike)?|del)>/gi, '[/s]');
			
			// smileys
			html = html.replace(/<img [^>]*?alt="([^"]+?)" class="smiley".*?> ?/gi, '$1 '); // firefox
			html = html.replace(/<img [^>]*?class="smiley" alt="([^"]+?)".*?> ?/gi, '$1 '); // chrome, ie
			
			// attachments
			html = html.replace(/<img [^>]*?class="redactorEmbeddedAttachment" data-attachment-id="(\d+)"( style="([^"]+)")?>/gi, function(match, attachmentID, styleTag, style) {
				if (style && style.match(/float: (left|right)/i)) {
					return '[attach=' + attachmentID + ',' + RegExp.$1 + '][/attach]';
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
			var $pixelToPoint = {
				11: 8, 
				13: 10,
				16: 12,
				19: 14,
				24: 18,
				29: 22,
				32: 24,
				48: 36
			};
			
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
					if ($value.match(/^<span/)) {
						if ($value.match(/^<span(?:.*?)style="([^"]+)"(?:[^>]*?)>/)) {
							var $style = RegExp.$1;
							var $start;
							var $end;
							
							if ($style.match(/color: ?rgb\((\d{1,3}), ?(\d{1,3}), ?(\d{1,3})\);?/i)) {
								var $r = RegExp.$1;
								var $g = RegExp.$2;
								var $b = RegExp.$3;
								
								var $hex = ("0123456789ABCDEF".charAt(($r - $r % 16) / 16) + '' + "0123456789ABCDEF".charAt($r % 16)) + '' + ("0123456789ABCDEF".charAt(($g - $g % 16) / 16) + '' + "0123456789ABCDEF".charAt($g % 16)) + '' + ("0123456789ABCDEF".charAt(($b - $b % 16) / 16) + '' + "0123456789ABCDEF".charAt($b % 16));
								$start = '[color=#' + $hex + ']';
								$end = '[/color]';
							}
							else if ($style.match(/color: ?([^;]+);?/i)) {
								$start = '[color=' + RegExp.$1 + ']';
								$end = '[/color]';
							}
							else if ($style.match(/font-size: ?(\d+)(pt|px);?/i)) {
								if (RegExp.$2 == 'pt') {
									$start = '[size=' + RegExp.$1 + ']';
									$end = '[/size]';
								}
								else {
									if ($pixelToPoint[RegExp.$1]) {
										$start = '[size=' + $pixelToPoint[RegExp.$1] + ']';
										$end = '[/size]';
									}
									else {
										// unsupported size
										$start = '';
										$end = '';
									}
								}
							}
							else if ($style.match(/font-family: ?([^;]+);?/)) {
								$start = "[font='" + RegExp.$1.replace(/'/g, '') + "']";
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
							// unrecognized span, ignore
							$buffer[$buffer.length] = '';
							$openElements[$buffer.length] = {
								start: '',
								end: ''
							};
						}
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
			if (html.length) {
				html += "\n";
			}
			
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
			data = this.wutil.removeZeroWidthSpace(data);
			
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
			data = data.replace(/\[url\]([^"]+?)\[\/url]/gi, '<a href="$1">$1</a>' + this.opts.invisibleSpace);
			data = data.replace(/\[url\='([^'"]+)'](.+?)\[\/url]/gi, '<a href="$1">$2</a>' + this.opts.invisibleSpace);
			data = data.replace(/\[url\=([^'"\]]+)](.+?)\[\/url]/gi, '<a href="$1">$2</a>' + this.opts.invisibleSpace);
			
			// [email]
			data = data.replace(/\[email\]([^"]+?)\[\/email]/gi, '<a href="mailto:$1">$1</a>' + this.opts.invisibleSpace);
			data = data.replace(/\[email\=([^"\]]+)](.+?)\[\/email]/gi, '<a href="mailto:$1">$2</a>' + this.opts.invisibleSpace);
			
			// [b]
			data = data.replace(/\[b\]([\s\S]*?)\[\/b]/gi, '<b>$1</b>');
			
			// [i]
			data = data.replace(/\[i\]([\s\S]*?)\[\/i]/gi, '<i>$1</i>');
			
			// [u]
			data = data.replace(/\[u\]([\s\S]*?)\[\/u]/gi, '<u>$1</u>');
			
			// [s]
			data = data.replace(/\[s\]([\s\S]*?)\[\/s]/gi, '<strike>$1</strike>');
			
			// [sub]
			data = data.replace(/\[sub\]([\s\S]*?)\[\/sub]/gi, '<sub>$1</sub>');
			
			// [sup]
			data = data.replace(/\[sup\]([\s\S]*?)\[\/sup]/gi, '<sup>$1</sup>');
				
			// [img]
			data = data.replace(/\[img\]([^"]+?)\[\/img\]/gi,'<img src="$1" />');
			data = data.replace(/\[img='?([^"]*?)'?,'?(left|right)'?\]\[\/img\]/gi,'<img src="$1" style="float: $2" />');
			data = data.replace(/\[img='?([^"]*?)'?\]\[\/img\]/gi,'<img src="$1" />');
			
			// [size]
			data = data.replace(/\[size=(\d+)\]([\s\S]*?)\[\/size\]/gi,'<span style="font-size: $1pt">$2</span>');
			
			// [color]
			data = data.replace(/\[color=([#a-z0-9]*?)\]([\s\S]*?)\[\/color\]/gi,'<span style="color: $1">$2</span>');
			
			// [font]
			data = data.replace(/\[font='?([a-z,\- ]*?)'?\]([\s\S]*?)\[\/font\]/gi,'<span style="font-family: $1">$2</span>');
			
			// [align]
			data = data.replace(/\[align=(left|right|center|justify)\]([\s\S]*?)\[\/align\]/gi,'<div style="text-align: $1">$2</div>');
			
			// [*]
			data = data.replace(/\[\*\](.*?)(?=\[\*\]|\[\/list\])/gi,'<li>$1</li>');
			
			// [list]
			data = data.replace(/\[list\]/gi, '<ul>');
			data = data.replace(/\[list=1\]/gi, '<ul style="list-style-type: decimal">');
			data = data.replace(/\[list=a\]/gi, '<ul style="list-style-type: lower-latin">');
			data = data.replace(/\[list=(none|circle|square|disc|decimal|lower-roman|upper-roman|decimal-leading-zero|lower-greek|lower-latin|upper-latin|armenian|georgian)\]/gi, '<ul style="list-style-type: $1">');
			data = data.replace(/\[\/list]\n?/gi, '</ul>\n');
			
			// trim whitespaces within [table]
			data = data.replace(/\[table\]([\S\s]*?)\[\/table\]/gi, function(match, p1) {
				return '[table]' + $.trim(p1) + '[/table]';
			});
			
			// [table]
			data = data.replace(/\[table\]\n*/gi, '<table border="1" cellspacing="1" cellpadding="1" style="width: 500px;">');
			data = data.replace(/\[\/table\](\n*)?/gi, function(match, newlines) {
				if (newlines) {
					// tables cause an additional newline if there already was a newline afterwards
					if (newlines.match(/\n/g).length > 2) {
						newlines = newlines.replace(/^\n/, '');
					}
					
					return '</table>' + newlines;
				}
				
				return '</table>';
			});
			// [tr]
			data = data.replace(/\[tr\]\n*/gi, '<tr>');
			data = data.replace(/\[\/tr\]\n*/gi, '</tr>');
			// [td]
			data = data.replace(/\[td\]\n*/gi, '<td>');
			data = data.replace(/\[\/td\]\n*/gi, '</td>');
			
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
			var $attachmentUrl = this.wutil.getOption('woltlab.attachmentUrl');
			if ($attachmentUrl) {
				var $imageAttachmentIDs = this.wbbcode._getImageAttachmentIDs();
				
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
				var $smileyCode = smileyCode.replace(/</g, '&lt;').replace(/>/g, '&gt;');
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
			
			var $parts = data.split(/(\[(?:\/quote|quote|quote='[^']*?'(?:,'[^']*?')?|quote="[^"]*?"(?:,"[^"]*?")?)\])/);
			var $lostQuote = WCF.getUUID();
			while (true) {
				var $foundClosingTag = false;
				for (var $i = 0; $i < $parts.length; $i++) {
					var $part = $parts[$i];
					if ($part === '[/quote]') {
						$foundClosingTag = true;
						
						var $content = '';
						var $previous = $parts.slice(0, $i);
						var $foundOpenTag = false;
						while ($previous.length) {
							var $prev = $previous.pop();
							$content = $prev + $content;
							if ($prev.match(/^\[quote/)) {
								$part = $content + $part;
								
								var $key = WCF.getUUID();
								$cachedQuotes.push({
									hashCode: $key,
									content: $part.replace(/\$/g, '$$$$')
								});
								$knownQuotes.push($key);
								
								$part = '@@' + $key + '@@';
								$foundOpenTag = true;
								break;
							}
						}
						
						if (!$foundOpenTag) {
							$previous = $parts.slice(0, $i);
							$part = $lostQuote;
						}
						
						// rebuild the array
						$parts = $previous.concat($part, $parts.slice($i + 1));
						
						break;
					}
				}
				
				if (!$foundClosingTag) {
					break;
				}
			}
			
			data = $parts.join('');
			
			// restore unmatched closing quote tags
			data = data.replace(new RegExp($lostQuote, 'g'), '[/quote]');
			
			// drop trailing line breaks
			data = data.replace(/\n*$/, '');
			
			// convert line breaks into <p></p> or empty lines to <p><br></p>
			var $tmp = data.split("\n");
			
			data = '';
			for (var $i = 0, $length = $tmp.length; $i < $length; $i++) {
				var $line = $.trim($tmp[$i]);
				
				if ($line.match(/^<([a-z]+)/)) {
					if (!this.reIsBlock.test(RegExp.$1.toUpperCase())) {
						$line = '<p>' + $line + '</p>';
					}
					
					data += $line;
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
			
			// insert quotes
			if ($cachedQuotes.length) {
				// [quote]
				var $unquoteString = function(quotedString) {
					return quotedString.replace(/^['"]/, '').replace(/['"]$/, '');
				};
				
				var self = this;
				var $transformQuote = function(quote) {
					return quote.replace(/\[quote(=['"].+['"])?\]([\S\s]*)\[\/quote\]/gi, function(match, attributes, innerContent) {
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
										+ self.wbbcode._buildQuoteHeader($author, $link)
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
						$quote += '</div></blockquote>';
						
						return $quote;
					});
				};
				
				// reinsert quotes in reverse order, adding the most outer quotes first
				for (var $i = $cachedQuotes.length - 1; $i >= 0; $i--) {
					var $cachedQuote = $cachedQuotes[$i];
					var $regex = new RegExp('@@' + $cachedQuote.hashCode + '@@', 'g');
					data = data.replace($regex, $transformQuote($cachedQuote.content));
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
			
			WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'afterConvertToHtml', { data: data });
			
			return data;
		},
		
		/**
		 * Converts certain HTML elements prior to paste in order to preserve formattings.
		 * 
		 * @param	string		html
		 * @return	string
		 */
		_pasteBeforeCallback: function(html) {
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
			html = html.replace(/<\/(div|p)><\/(div|p)>/g, '</p>');
			html = html.replace(/<(div|p)><br><\/(div|p)>/g, '<p>');
			
			WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'beforePaste', { html: html });
			
			return html;
		},
		
		/**
		 * Restores and fixes formatting before inserting pasted HTML into the editor.
		 * 
		 * @param	string		html
		 * @return	string
		 */
		_pasteCallback: function(html) {
			// reduce successive <br> by one
			html = html.replace(/<br[^>]*>(<br[^>]*>)+/g, '$1');
			
			// replace <p>...</p> with <p>...</p><p><br></p>
			/*html = html.replace(/<p>([\s\S]*?)<\/p>/g, function(match, content) {
				if (content.match(/<br( \/)?>$/)) {
					return match;
				}
				
				return '<p>' + content + '</p><p><br></p>';
			});*/
			
			// restore font size
			html = html.replace(/\[size=(\d+)\]/g, '<p><span style="font-size: $1pt">');
			html = html.replace(/\[\/size\]/g, '</span></p>');
			
			WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'afterPaste', { html: html });
			
			return html;
			
			// TODO
			
			// handle pasting of images in Firefox
			html = html.replace(/<img([^>]+)>/g, function(match, content) {
				match = match.replace(/data-mozilla-paste-image="0"/, 'data-mozilla-paste-image="0" style="display:none"');
				return match;
			});
			
			
			
			return html;
		},
		
		/**
		 * Inserts an attachment with live preview.
		 * 
		 * @param	integer		attachmentID
		 */
		insertAttachment: function(attachmentID) {
			attachmentID = parseInt(attachmentID);
			var $attachmentUrl = this.wutil.getOption('woltlab.attachmentUrl');
			var $bbcode = '[attach=' + attachmentID + '][/attach]';
			
			var $imageAttachmentIDs = this.wbbcode._getImageAttachmentIDs();
			
			if ($attachmentUrl && WCF.inArray(attachmentID, $imageAttachmentIDs)) {
				this.wutil.insertDynamic(
					'<img src="' + $attachmentUrl.replace(/987654321/, attachmentID) + '" class="redactorEmbeddedAttachment" data-attachment-id="' + attachmentID + '" />',
					$bbcode
				);
			}
			else {
				this.wutil.insertDynamic($bbcode);
			}
		},
		
		/**
		 * Returns a list of attachments representing an image.
		 * 
		 * @return	array<integer>
		 */
		_getImageAttachmentIDs: function() {
			// WCF.Attachment.Upload may have no been initialized yet, fallback to static data
			var $imageAttachmentIDs = this.wutil.getOption('woltlab.attachmentImageIDs') || [ ];
			if ($imageAttachmentIDs.length) {
				delete this.opts.wAttachmentImageIDs;
				
				return $imageAttachmentIDs;
			}
			
			var $data = {
				imageAttachmentIDs: [ ]
			};
			WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'getImageAttachments_' + this.$textarea.wcfIdentify(), $data);
			
			return $data.imageAttachmentIDs;
		},
		
		/**
		 * Handles up/down/delete/backspace key for quote boxes.
		 * 
		 * @param	object		data
		 */
		_keydownCallback: function(data) {
			switch (data.event.which) {
				case $.ui.keyCode.BACKSPACE:
				case $.ui.keyCode.DELETE:
				case $.ui.keyCode.DOWN:
				case $.ui.keyCode.ENTER:
				case $.ui.keyCode.UP:
				case 83: // [S]
					// handle keys
				break;
				
				default:
					return;
				break;
			}
			
			this.selection.get();
			var $current = $(this.selection.getCurrent());
			var $parent = this.selection.getParent();
			$parent = ($parent) ? $($parent) : $parent;
			var $quote = ($parent) ? $parent.closest('blockquote.quoteBox', this.$editor.get()[0]) : { length: 0 };
			
			switch (data.event.which) {
				// backspace key
				case $.ui.keyCode.BACKSPACE:
					if (this.wutil.isCaret()) {
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
				break;
				
				// delete key
				case $.ui.keyCode.DELETE:
					if (this.wutil.isCaret()) {
						if (this.wutil.isEndOfElement($current[0]) && $current.next('blockquote').length) {
							// expand selection and prevent delete
							var $selection = window.getSelection();
							if ($selection.rangeCount) $selection.removeAllRanges();
							
							var $quoteRange = document.createRange();
							$quoteRange.selectNode($current.next()[0]);
							$selection.addRange($quoteRange);
							
							data.cancel = true;
						}
					}
				break;
				
				// arrow down
				case $.ui.keyCode.DOWN:
					if ($current.next('blockquote').length) {
						this.caret.setStart($current.next().find('> div > div:first'));
						
						data.cancel = true;
					}
					else if ($parent) {
						if ($parent.next('blockquote').length) {
							this.caret.setStart($parent.next().find('> div > div:first'));
							
							data.cancel = true;
						}
						else if ($quote.length) {
							var $container = $current.closest('div', $quote[0]);
							if (!$container.next().length) {
								// check if there is an element after the quote
								if ($quote.next().length) {
									this.caret.setStart($quote.next());
								}
								else {
									this.wutil.setCaretAfter($quote);
								}
								
								data.cancel = true;
							}
						} 
					}
				break;
				
				// enter
				case $.ui.keyCode.ENTER:
					if ($quote.length) {
						// prevent Redactor's default behavior for <blockquote>
						this.keydown.blockquote = false;
						this.keydown.enterWithinBlockquote = true;
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
						// TODO
						// set focus to quote text rather than the element itself
						return;
						//this.selectionEnd($prev.find('> div > div:last'));
					}
					
					var $previousElement = $quote.prev();
					if ($previousElement.length === 0) {
						this.wutil.setCaretBefore($quote);
					}
					else {
						if ($previousElement[0].tagName === 'BLOCKQUOTE') {
							// set focus to quote text rather than the element itself
							this.caret.sendEnd($previousElement.find('> div > div:last'));
						}
						else {
							// focus is wrong if the previous element is empty (e.g. only a newline present)
							if ($.trim($previousElement.html()) == '') {
								$previousElement.html(this.opts.invisibleSpace);
							}
							
							this.caret.setEnd($previousElement);
						}
					}
					
					data.cancel = true;
				break;
				
				// [S]
				case 83:
					var $submitEditor = false;
					if (navigator.platform.match(/^Mac/)) {
						if (data.event.ctrlKey && data.event.altKey) {
							$submitEditor = true;
						}
					}
					else if (data.event.altKey) {
						$submitEditor = true;
					}
					
					if ($submitEditor) {
						var $data = { cancel: false };
						WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'submitEditor_' + this.$textarea.wcfIdentify(), $data);
						
						if ($data.cancel) {
							data.cancel = true;
						}
					}
				break;
			}
		},
		
		/**
		 * Handles quote deletion.
		 * 
		 * @param	object		data
		 */
		_keyupCallback: function(data) {
			if (data.event.which !== $.ui.keyCode.BACKSPACE && data.event.which !== $.ui.keyCode.DELETE) {
				return;
			}
			
			// check for empty <blockquote
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
			this.$editor.find('.redactorQuoteEdit:not(.jsRedactorQuoteEdit)').addClass('jsRedactorQuoteEdit').click($.proxy(this.wbbcode._observeQuotesClick, this));
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
				
				this.wbbcode._openQuoteEditOverlay($(event.currentTarget).closest('blockquote.quoteBox'), false);
				$('.redactor-link-tooltip').remove();
			}, this)).appendTo($tooltip);
			
			var $offset = $header.offset();
			$tooltip.css({
				left: $offset.left + 'px',
				top: ($offset.top + 20) + 'px'
			});
			
			$('.redactor-link-tooltip').remove();
			$tooltip.appendTo(document.body);
			
			// prevent the cursor being placed in the quote header
			$('<div contenteditable="true" />').appendTo(document.body).focus().remove();
		},
		
		/**
		 * Opens the quote source edit dialog.
		 * 
		 * @param	jQuery		quote
		 * @param	boolean		insertQuote
		 */
		_openQuoteEditOverlay: function(quote, insertQuote) {
			this.modal.load('quote', WCF.Language.get('wcf.bbcode.quote.' + (insertQuote ? 'insert' : 'edit')), 400);
			
			var $button = this.modal.createActionButton(this.lang.get('save'));
			if (insertQuote) {
				$button.click($.proxy(function() {
					var $author = $('#redactorQuoteAuthor').val();
					var $link = WCF.String.escapeHTML($('#redactorQuoteLink').val());
					
					var $quote = this.wbbcode.insertQuoteBBCode($author, $link);
					if ($quote !== null) {
						// set caret inside the quote
						this.caret.setStart($quote.find('> div > div')[0]);
					}
					
					this.modal.close();
				}, this));
			}
			else {
				$('#redactorQuoteAuthor').val(quote.data('author'));
				
				// do not use prop() here, an empty cite attribute would yield the page URL instead
				$('#redactorQuoteLink').val(WCF.String.unescapeHTML(quote.attr('cite')));
				
				$button.click($.proxy(function() {
					var $author = $('#redactorQuoteAuthor').val();
					quote.data('author', $author);
					quote.attr('data-author', $author);
					quote.prop('cite', WCF.String.escapeHTML($('#redactorQuoteLink').val()));
					
					this.wbbcode._updateQuoteHeader(quote);
					
					this.modal.close();
				}, this));
			}
			
			this.modal.show();
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
			
			quote.find('> div > header > h3').empty().append(this.wbbcode._buildQuoteHeader($author, $link));	
		},
		
		/**
		 * Inserts the quote BBCode.
		 * 
		 * @param	string		author
		 * @param	string		link
		 * @param	string		html
		 * @param	string		plainText
		 * @return	jQuery
		 */
		insertQuoteBBCode: function(author, link, html, plainText) {
			var $openTag = '[quote]';
			var $closingTag = '[/quote]';
			
			if (author) {
				if (link) {
					$openTag = "[quote='" + author + "','" + link + "']";
				}
				else {
					$openTag = "[quote='" + author + "']";
				}
			}
			
			var $quote = null;
			if (this.wutil.inWysiwygMode()) {
				var $innerHTML = (plainText) ? this.wbbcode.convertToHtml(plainText) : '';
				var $id = WCF.getUUID();
				var $html = this.wbbcode.convertToHtml($openTag + $id + $closingTag);
				$html = $html.replace($id, $innerHTML);
				
				// assign a unique id in order to recognize the inserted quote
				$html = $html.replace(/(<p>)?<blockquote/, '$1<blockquote id="' + $id + '"');
				
				this.insert.html($html, false);
				
				$quote = this.$editor.find('#' + $id);
				if ($quote.length) {
					// quote may be empty if $innerHTML was empty, fix it
					var $inner = $quote.find('> div > div');
					if ($inner.length == 1 && $inner[0].innerHTML === '') {
						$inner[0].innerHTML = this.opts.invisibleSpace;
					}
					
					$quote.removeAttr('id');
					this.wutil.setCaretAfter($quote[0]);
				}
				
				this.wbbcode._observeQuotes();
				
				this.$toolbar.find('a.re-__wcf_quote').removeClass('redactor-button-disabled');
			}
			else {
				this.wutil.insertAtCaret($openTag + plainText + $closingTag);
			}
			
			return $quote;
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
			this.wbbcode._openQuoteEditOverlay(null, true);
		},
		
		/**
		 * Ensures that there is a paragraph in front of each quotes because you cannot click in between two of them.
		 */
		_fixQuotes: function() {
			this.$editor.find('blockquote').each((function(index, blockquote) {
				if (blockquote.previousElementSibling === null || blockquote.previousElementSibling.tagName !== 'P') {
					$(this.opts.emptyHtml).insertBefore(blockquote);
				}
				else if (blockquote.previousElementSibling.tagName === 'P' && !blockquote.previousElementSibling.innerHTML.length) {
					$(blockquote.previousElementSibling).html(this.opts.invisibleSpace);
				}
			}).bind(this));
		}
	};
};
