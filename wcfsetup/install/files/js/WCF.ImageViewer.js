/**
 * ImageViewer for WCF.
 * Based upon "Slimbox 2" by Christophe Beyls 2007-20120, http://www.digitalia.be/software/slimbox2, MIT-style license.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.ImageViewer = Class.extend({
	/**
	 * Initializes the ImageViewer for every a-tag with the attribute rel = imageviewer.
	 */
	init: function() {
		$('a[rel^=imageviewer]').slimbox({
			counterText: WCF.Language.get('wcf.imageViewer.counter'),
			loop: true
		});
		
		WCF.DOMNodeInsertedHandler.enable();
		
		// navigation buttons
		$('<span><img src="' + WCF.Icon.get('wcf.icon.arrowLeftColored') + '" alt="" class="icon24 jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.previous') + '" /></span>').appendTo($('#lbPrevLink'));
		$('<span><img src="' + WCF.Icon.get('wcf.icon.arrowRightColored') + '" alt="" class="icon24 jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.next') + '" /></span>').appendTo($('#lbNextLink'));
		
		// close and enlarge icons
		$('<img src="' + WCF.Icon.get('wcf.icon.deleteColored') + '" alt="" class="icon24 jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.close') + '" />').appendTo($('#lbCloseLink'));
		var $buttonEnlarge = $('<img src="' + WCF.Icon.get('wcf.icon.enlargeColored') + '" alt="" class="icon24 jsTooltip" title="' + WCF.Language.get('wcf.imageViewer.enlarge') + '" id="lbEnlarge" />').insertAfter($('#lbCloseLink'));
		
		WCF.DOMNodeInsertedHandler.disable();
		
		// handle enlarge button
		$buttonEnlarge.click($.proxy(this._enlarge, this));
	},
	
	/**
	 * Redirects to image for full view.
	 */
	_enlarge: function() {
		var $url = $('#lbImage').css('backgroundImage');
		if ($url) {
			$url = $url.substring(4, $url.length - 1);
			window.location = $url;
		}
	}
});