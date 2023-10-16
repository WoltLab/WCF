{include file='header'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{@$objectType->getProcessor()->getLanguageVariable($action)}</h1>
	</div>

	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $action == 'edit' && $categoryNodeList !== null && $categoryNodeList->hasChildren()}
						<li class="dropdown">
							<a class="button dropdownToggle">
								{icon name='sort'} <span>{@$objectType->getProcessor()->getLanguageVariable('button.choose')}</span>
							</a>
							<div class="dropdownMenu">
								<ul class="scrollableDropdownMenu">
									{foreach from=$categoryNodeList item='categoryNode'}
										<li{if $categoryNode->getObjectID() == $formObject->getObjectID()} class="active"{/if}>
											<a href="{link controller=$editController application=$objectType->getProcessor()->getApplication() object=$categoryNode}{/link}">{section name=i loop=$categoryNodeList->getDepth()}&nbsp;&nbsp;&nbsp;&nbsp;{/section}{$categoryNode->getTitle()}</a>
										</li>
									{/foreach}
								</ul>
							</div>
						</li>
					{/if}

					{if $objectType->getProcessor()->canDeleteCategory() || $objectType->getProcessor()->canEditCategory()}
						<li>
							<a href="{link controller=$listController application=$objectType->getProcessor()->getApplication()}{/link}" class="button">{icon name='list'} <span>{@$objectType->getProcessor()->getLanguageVariable('button.list')}</span></a>
						</li>
					{/if}

					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{@$form->getHtml()}

{include file='footer'}
