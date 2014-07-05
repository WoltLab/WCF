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
		/*var $dropdown = this._createSmileyDropdown();
		
		this.buttonReplace('smiley', 'wsmiley', 'Smiley', $.proxy(function(btnName, $button, btnObject, e) {
			this.dropdownShow(e, btnName);
		}, this));
		this.buttonGet('wsmiley').data('dropdown', $dropdown);
		this.buttonAwesome('wsmiley', 'fa-smile-o');*/
		
		this.opts.initCallback = $.proxy(function() {
			if (this.$source.val().length) {
				this.toggle();
				this.toggle();
			}
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
	},
	
	/**
	 * Creates the smiley dropdown.
	 */
	_createSmileyDropdown: function() {
		var $dropdown = $('<div class="redactor_dropdown redactor_dropdown_box_wsmiley" style="display: none; width: 195px;" />');
		var $list = $('<ul class="smileyList" />').appendTo($dropdown);
		
		for (var $smileyCode in __REDACTOR_SMILIES) {
			var $insertLink = $('<li><img src="' + __REDACTOR_SMILIES[$smileyCode] + '" class="smiley" /></li>').data('smileyCode', $smileyCode);
			$insertLink.appendTo($list).click($.proxy(this._onSmileyPick, this));
		}
		
		$(this.$toolbar).append($dropdown);
		
		return $dropdown;
	},
	
	/**
	 * Inserts smiley on click.
	 * 
	 * @param	object		event
	 */
	_onSmileyPick: function(event) {
		var $smileyCode = $(event.currentTarget).data('smileyCode');
		this.insertSmiley($smileyCode, __REDACTOR_SMILIES[$smileyCode], false);
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
			this.toggleCode(direct);
			this._convertFromHtml();
			
			this.buttonGet('html').children('i').removeClass('fa-square-o').addClass('fa-square');
		}
		else {
			this._convertToHtml();
			this.toggleVisual();
			
			this.buttonGet('html').children('i').removeClass('fa-square').addClass('fa-square-o');
		}
	},
	
	/**
	 * Converts source contents from HTML into BBCode.
	 */
	_convertFromHtml: function() {
		var html = this.$source.val();
		
		// drop all new lines
		html = html.replace(/\r?\n/g, '');
		
		// convert paragraphs into single lines
		var $parts = html.split(/(<\/?p>)/);
		var $tmp = '';
		var $buffer = '';
		for (var $i = 0; $i < $parts.length; $i++) {
			var $part = $parts[$i];
			if ($part == '<p>') {
				continue;
			}
			else if ($part == '</p>') {
				$buffer = $.trim($buffer);
				if ($buffer != '@@@wcf_empty_line@@@') {
					$buffer += "\n";
				}
				
				$tmp += $buffer;
				$buffer = '';
			}
			else {
				if ($i == 0) {
					$tmp += $part;
				}
				else {
					$buffer += $part;
				}
			}
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
		html = html.replace(/<img [^>]*?alt="([^"]+?)" class="smiley".*?>/gi, '$1'); // firefox
		html = html.replace(/<img [^>]*?class="smiley" alt="([^"]+?)".*?>/gi, '$1'); // chrome, ie
		
		// [img]
		html = html.replace(/<img [^>]*?src=(["'])([^"']+?)\1 style="float: (left|right)[^"]*".*?>/gi, "[img='$2',$3][/img]");
		html = html.replace(/<img [^>]*?src=(["'])([^"']+?)\1.*?>/gi, '[img]$2[/img]');
		
		// [quote]
		// html = html.replace(/<blockquote>/gi, '[quote]');
		// html = html.replace(/\n*<\/blockquote>/gi, '[/quote]');
		
		// handle [color], [size] and [font]
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
		html = html.replace(/<div style="text-align: ?(left|center|right|justify);? ?">([\s\S]*?)<\/div>/gi, "[align=$1]$2[/align]");
		
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
		html = html.replace(/(\t)*<td>/gi, '[td]');
		html = html.replace(/(\t)*<\/td>/gi, '[/td]\n');
		
		// cache redactor's selection markers
		var $cachedMarkers = { };
		html.replace(/<span id="selection-marker-\d+" class="redactor-selection-marker"><\/span>/, function(match) {
			var $key = match.hashCode();
			$cachedMarkers[$key] = match.replace(/\$/g, '$$$$');
			return '@@' + $key + '@@';
		});
		
		// Remove remaining tags.
		html = html.replace(/<[^>]+>/g, '');
		
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
		
		// trim leading tabs
		var $tmp = html.split("\n");
		for (var $i = 0, $length = $tmp.length; $i < $length; $i++) {
			$tmp[$i] = $tmp[$i].replace(/^\s*/, '');
		}
		html = $tmp.join("\n");
		
		// insert codes
		if ($.getLength($cachedCodes)) {
			for (var $key in $cachedCodes) {
				var $regex = new RegExp('@@' + $key + '@@', 'g');
				html = html.replace($regex, $cachedCodes[$key]);
			}
		}
		
		this.$source.val(html);
	},
	
	/**
	 * Converts source contents from BBCode to HTML.
	 */
	_convertToHtml: function() {
		var data = this.$source.val();
		
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
			return '<td>' + $.trim(p1) + '</td>';
		});
		
		// smileys
		for (var smileyCode in __REDACTOR_SMILIES) {
			$smileyCode = smileyCode.replace(/</g, '&lt;').replace(/>/g, '&gt;');
			var regExp = new RegExp('(\\s|>|^)' + WCF.String.escapeRegExp($smileyCode) + '(?=\\s|<|$)', 'gi');
			data = data.replace(regExp, '$1<img src="' + __REDACTOR_SMILIES[smileyCode] + '" class="smiley" alt="' + $smileyCode + '" />');
		}
		
		// remove "javascript:"
		data = data.replace(/(javascript):/gi, '$1<span></span>:');
		
		// unify line breaks
		data = data.replace(/(\r|\r\n)/, "\n");
		
		// convert line breaks into <p></p> or empty lines to <p><br></p>
		var $tmp = data.split("\n");
		data = '';
		for (var $i = 0, $length = $tmp.length; $i < $length; $i++) {
			var $line = $.trim($tmp[$i]);
			
			if ($line.indexOf('<') === 0) {
				data += $line;
			}
			else {
				if (!$line) {
					$line = '<br>';
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
		}
		
		// preserve leading whitespaces in [code] tags
		data = data.replace(/\[code\][\S\s]*?\[\/code\]/, '<pre>$&</pre>');
		
		this.$source.val(data);
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
		
		return html;
	}
};
