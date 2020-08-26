{if !$__imageViewerLoaded|isset}
	<script data-relocate="true">
		var $imageViewer = null;
		$(function() {
			WCF.Language.addObject({
				'wcf.imageViewer.button.enlarge': '{jslang}wcf.imageViewer.button.enlarge{/jslang}',
				'wcf.imageViewer.button.full': '{jslang}wcf.imageViewer.button.full{/jslang}',
				'wcf.imageViewer.seriesIndex': '{jslang __literal=true}wcf.imageViewer.seriesIndex{/jslang}',
				'wcf.imageViewer.counter': '{jslang}wcf.imageViewer.counter{/jslang}',
				'wcf.imageViewer.close': '{jslang}wcf.imageViewer.close{/jslang}',
				'wcf.imageViewer.enlarge': '{jslang}wcf.imageViewer.enlarge{/jslang}',
				'wcf.imageViewer.next': '{jslang}wcf.imageViewer.next{/jslang}',
				'wcf.imageViewer.previous': '{jslang}wcf.imageViewer.previous{/jslang}'
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
	</script>
	
	{assign var=__imageViewerLoaded value=true}
{/if}
