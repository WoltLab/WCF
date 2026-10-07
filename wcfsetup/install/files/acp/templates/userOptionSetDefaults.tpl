{include file='header' pageTitle='wcf.acp.user.option.setDefaults'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.option.setDefaults{/lang}</h1>
	</div>

	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{if $hasOptions}
	{unsafe:$form->getHtml()}
{else}
	<woltlab-core-notice type="info">{lang}wcf.global.noItems{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}
