{if !$__imageViewerLoaded|isset}
	<script data-relocate="true">
		var $imageViewer = null;
		$(function() {
			WCF.Language.addObject({
				'wcf.imageViewer.button.enlarge': '{jslang}wcf.imageViewer.button.enlarge{/jslang}',
				'wcf.imageViewer.button.full': '{jslang}wcf.imageViewer.button.full{/jslang}',
				'wcf.imageViewer.seriesIndex': '{jslang __literal=true}wcf.imageViewer.seriesIndex{/jslang}',
				'wcf.imageViewer.counter': '{jslang __literal=true}wcf.imageViewer.counter{/jslang}',
				'wcf.imageViewer.close': '{jslang}wcf.imageViewer.close{/jslang}',
				'wcf.imageViewer.enlarge': '{jslang}wcf.imageViewer.enlarge{/jslang}',
				'wcf.imageViewer.next': '{jslang}wcf.imageViewer.next{/jslang}',
				'wcf.imageViewer.previous': '{jslang}wcf.imageViewer.previous{/jslang}'
			});
			
			$imageViewer = new WCF.ImageViewer();
		});
	</script>
	
	{assign var=__imageViewerLoaded value=true}
{/if}
