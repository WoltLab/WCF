{if !$__imageViewerLoaded|isset}
	<script data-eager="true">
	  {
		let stylesheet = document.getElementById("fancybox-stylesheet");
		if (stylesheet === null) {
		  stylesheet = document.createElement("link");
		  stylesheet.rel = "stylesheet";
		  stylesheet.type = "text/css";
		  stylesheet.href = "{$__wcf->getPath()}style/fancybox.css";
		  stylesheet.id = "fancybox-stylesheet";

		  document.querySelector("link[rel=\"stylesheet\"]").before(stylesheet);
		}
	  }
	</script>
	
	{assign var=__imageViewerLoaded value=true}
{/if}
