{if !$__imageViewerLoaded|isset}
	<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/slimbox2{if !ENABLE_DEBUG_MODE}.min{/if}.js"></script>
	<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.ImageViewer{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@$__wcfVersion}"></script>
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
		//]]>
	</script>
	
	{assign var=__imageViewerLoaded value=true}
{/if}