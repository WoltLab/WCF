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
		this._createSmileyDropdown();
		
		this.buttonAdd('wsmiley', 'Smiley', $.proxy(function(btnName, $button, btnObject, e) {
			this.dropdownShow(e, btnName);
		}, this));
		this.buttonAwesome('wsmiley', 'fa-smile-o');
		
		this.opts.initCallback = $.proxy(function() {
			if (this.$source.val().length) {
				this.toggle();
				this.toggle();
			}
		}, this);
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
		}
		else {
			this._convertToHtml();
			this.toggleVisual();
		}
	},
	
	/**
	 * Removes the unicode zero width space (0x200B).
	 * 
	 * @param	string		string
	 * @return	string
	 */
	_removeCrap: function(string) {
		var $string = '';
		
		for (var $i = 0, $length = string.length; $i < $length; $i++) {
			var $byte = string.charCodeAt($i).toString(16);
			if ($byte != '200b') {
				$string += string[$i];
			}
		}
		
		return $string;
	},
	
	/**
	 * Converts source contents from HTML into BBCode.
	 */
	_convertFromHtml: function() {
		var html = this.$source.val();
		
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
		html = html.replace(/<ul style="list-style-type: (none|circle|square|disc|decimal|lower-roman|upper-roman|decimal-leading-zero|lower-greek|lower-latin|upper-latin|armenian|georgian)">/gi, '[list=$1]');
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
		
		// Restore (and )
		html = html.replace(/%28/g, '(');
		html = html.replace(/%29/g, ')');
		
		// Restore %20
		html = html.replace(/%20/g, ' ');
		
		this.$source.val(html);
	},
	
	/**
	 * Converts source contents from BBCode to HTML.
	 */
	_convertToHtml: function() {
		var data = this.$source.val();
		
		// remove 0x200B (unicode zero width space)
		data = this._removeCrap(data);
		
		//if (!$pasted) {
			// Convert & to its HTML entity.
			data = data.replace(/&/g, '&amp;');
			
			// Convert < and > to their HTML entities.
			data = data.replace(/</g, '&lt;');
			data = data.replace(/>/g, '&gt;');
		//}
		
		// Convert line breaks to <br>.
		data = data.replace(/(?:\r\n|\n|\r)/g, '<br>');
		
		/*if ($pasted) {
			$pasted = false;
			// skip
			return data;
		}*/
		
		// cache source code tags
		var $cachedCodes = { };
		for (var $i = 0, $length = __REDACTOR_SOURCE_BBCODES.length; $i < $length; $i++) {
			var $bbcode = __REDACTOR_SOURCE_BBCODES[$i];
			
			var $regExp = new RegExp('\\[' + $bbcode + '(.+?)\\[\\/' + $bbcode + '\\]', 'gi');
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
		for (var smileyCode in __REDACTOR_SMILIES) {
			$smileyCode = smileyCode.replace(/</g, '&lt;').replace(/>/g, '&gt;');
			var regExp = new RegExp('(\\s|>|^)' + WCF.String.escapeRegExp($smileyCode) + '(?=\\s|<|$)', 'gi');
			data = data.replace(regExp, '$1<img src="' + __REDACTOR_SMILIES[smileyCode] + '" class="smiley" alt="' + $smileyCode + '" />');
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
		
		this.$source.val(data);
	}
};
