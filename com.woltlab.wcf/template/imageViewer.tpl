{if !$__imageViewerLoaded|isset}
	<script type="text/javascript" src="{@$__wcf->getPath()}js/3rdParty/slimbox2.min{if !DEBUG_MODE}.min{/if}.js"></script>
	<script type="text/javascript" src="{@$__wcf->getPath()}js/WCF.ImageViewer{if !DEBUG_MODE}.min{/if}.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var $imageViewer = null;
		$(function() {
			WCF.Language.addObject({
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