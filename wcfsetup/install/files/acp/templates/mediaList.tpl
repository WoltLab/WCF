{include file='header' pageTitle='wcf.media.media.pageTitle'}

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
	{unsafe:$listView->render()}
</div>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Acp/Component/Media/List'], ({ setup }) => {
		setup(
			document.querySelector('#mediaUploadButton woltlab-core-file-upload'),
			document.getElementById('{unsafe:$listView->getID()|encodeJS}_items'),
		);
	});
</script>

{include file='footer'}
