{if !$__imageViewerLoaded|isset}
	<script type="text/javascript" src="{@$__wcf->getPath()}js/3rdParty/slimbox2.min.js"></script>
	<script type="text/javascript" src="{@$__wcf->getPath()}js/WCF.ImageViewer.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var $imageViewer = null;
		$(function() {
			WCF.Icon.addObject({
				'wcf.icon.arrowLeftColored': '{icon}arrowLeftColored{/icon}',
				'wcf.icon.arrowRightColored': '{icon}arrowRightColored{/icon}',
				'wcf.icon.deleteColored': '{icon}deleteColored{/icon}',
				'wcf.icon.enlargeColored': '{icon}enlargeColored{/icon}'
			});
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