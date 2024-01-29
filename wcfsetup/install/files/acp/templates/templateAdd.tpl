{include file='header' pageTitle='wcf.acp.template.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.template.{$action}{/lang}</h1>
		{if $action == 'edit'}<p class="contentHeaderDescription">{$formObject->getPath()}</p>{/if}
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $action == 'edit'}<li><a href="{link controller='TemplateDiff' id=$formObject->templateID}{/link}" class="button">{icon name='right-left'} <span>{lang}wcf.acp.template.diff{/lang}</span></a></li>{/if}
			<li><a href="{link controller='TemplateList'}{if $action == 'edit'}templateGroupID={@$formObject->templateGroupID}{/if}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.template.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{@$form->getHtml()}

{include file='footer'}
