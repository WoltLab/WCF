{include file='header'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{unsafe:$objectType->getProcessor()->getLanguageVariable('list')}</h1>
	</div>

	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $objectType->getProcessor()->canAddCategory()}
						<li><a href="{$addFormLink}" class="button buttonPrimary">{icon name='plus'} <span>{unsafe:$objectType->getProcessor()->getLanguageVariable('add')}</span></a></li>
					{/if}

					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

<div class="section">
	{unsafe:$nodeTreeView->render()}
</div>

{include file='footer'}
