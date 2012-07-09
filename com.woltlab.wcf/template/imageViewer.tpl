{if !$__imageViewerLoaded|isset}
	<script type="text/javascript" src="{@$__wcf->getPath()}js/3rdParty/slimbox2.min.js"></script>
	<script type="text/javascript" src="{@$__wcf->getPath()}js/WCF.ImageViewer.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		$(function() {
			WCF.Icon.addObject({
				'wcf.icon.arrowLeftColored': '{icon size='M'}arrowLeftColored{/icon}',
				'wcf.icon.arrowRightColored': '{icon size='M'}arrowRightColored{/icon}',
				'wcf.icon.closeColored': '{icon size='M'}closeColored{/icon}',
				'wcf.icon.enlargeColored': '{icon size='M'}enlargeColored{/icon}'
			});
			WCF.Language.addObject({
				'wcf.imageViewer.counter': '{lang}wcf.imageViewer.counter{/lang}',
				'wcf.imageViewer.close': '{lang}wcf.imageViewer.close{/lang}',
				'wcf.imageViewer.enlarge': '{lang}wcf.imageViewer.enlarge{/lang}',
				'wcf.imageViewer.next': '{lang}wcf.imageViewer.next{/lang}',
				'wcf.imageViewer.previous': '{lang}wcf.imageViewer.previous{/lang}'
			});
			
			new WCF.ImageViewer();
		});
		//]]>
	</script>
	
	{assign var=__imageViewerLoaded value=true}
{/if}