{if !$__imageViewerLoaded|isset}
	<script data-relocate="true">
		//<![CDATA[
		var $imageViewer = null;
		$(function() {
			WCF.Language.addObject({
				'wcf.imageViewer.button.enlarge': '{lang}wcf.imageViewer.button.enlarge{/lang}',
				'wcf.imageViewer.button.full': '{lang}wcf.imageViewer.button.full{/lang}',
				'wcf.imageViewer.seriesIndex': '{lang}wcf.imageViewer.seriesIndex{/lang}',
				'wcf.imageViewer.counter': '{lang}wcf.imageViewer.counter{/lang}',
				'wcf.imageViewer.close': '{lang}wcf.imageViewer.close{/lang}',
				'wcf.imageViewer.enlarge': '{lang}wcf.imageViewer.enlarge{/lang}',
				'wcf.imageViewer.next': '{lang}wcf.imageViewer.next{/lang}',
				'wcf.imageViewer.previous': '{lang}wcf.imageViewer.previous{/lang}'
			});
			
			$imageViewer = new WCF.ImageViewer();
		});
		
		// WCF 2.0 compatibility, dynamically fetch slimbox and initialize it with the request parameters
		$.widget('ui.slimbox', {
			_create: function() {
				var self = this;
				head.load('{@$__wcf->getPath()}js/3rdParty/slimbox2.js', function() {
					self.element.slimbox(self.options);
				});
			}
		});
		//]]>
	</script>
	
	{assign var=__imageViewerLoaded value=true}
{/if}