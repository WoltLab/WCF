{include file='header' pageTitle='wcf.acp.devtools.project.pip.entry.'|concat:$action:'.pageTitle'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.devtools.project.pip.entry.{$action}{/lang}</h1>
		<p class="contentHeaderDescription">{$project->name}</p>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{unsafe:$pipObject->getPip()->getAdditionalTemplateCode()}

{unsafe:$form->getHtml()}

{include file='footer'}
