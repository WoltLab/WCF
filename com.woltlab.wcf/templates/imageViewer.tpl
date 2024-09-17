{if !$__imageViewerLoaded|isset}
{*<script data-relocate="true">
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
	</script>*}
	<script data-eager="true">
	  {
		let stylesheet = document.getElementById("fancybox-stylesheet");
		if (stylesheet === null) {
		  stylesheet = document.createElement("link");
		  stylesheet.rel = "stylesheet";
		  stylesheet.type = "text/css";
		  stylesheet.href = "{$__wcf->getPath()}js/3rdParty/fancybox/fancybox.css";
		  stylesheet.id = "fancybox-stylesheet";

		  document.querySelector("link[rel=\"stylesheet\"]").before(stylesheet);
		}
	  }
	</script>
	
	{assign var=__imageViewerLoaded value=true}
{/if}
