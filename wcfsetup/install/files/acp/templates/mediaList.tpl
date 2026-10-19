{include file='header' pageTitle='wcf.media.media.pageTitle'}

{include file='shared_imageViewer'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.media.media.pageTitle{/lang}</h1>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			<li id="mediaUploadButton">{unsafe:$uploadButton}</li>

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

<script data-relocate="true">
	WoltLabLanguage.registerPhrase('wcf.media.upload.success.multiple', '{jslang __literal=true}wcf.media.upload.success.multiple{/jslang}');

	require(['WoltLabSuite/Core/Acp/Component/Media/List'], ({ setup }) => {
		setup(
			document.querySelector('#mediaUploadButton woltlab-core-file-upload'),
			document.getElementById('{unsafe:$gridView->getID()|encodeJS}_table'),
			'{unsafe:$editEndpoint|encodeJS}',
		);
	});
</script>

{include file='footer'}
