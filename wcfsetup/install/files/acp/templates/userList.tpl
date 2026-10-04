{include file='header' pageTitle='wcf.acp.user.list'}

{event name='javascriptInclude'}
<script data-relocate="true">
	{jsphrase name='wcf.acp.content.removeContent'}
	{jsphrase name='wcf.acp.worker.abort.confirmMessage'}
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.list{/lang}</h1>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			<li class="dropdown">
				<a class="button dropdownToggle">{icon name='magnifying-glass'} <span>{lang}wcf.acp.user.quickSearch{/lang}</span></a>
				<ul class="dropdownMenu">
					<li><a href="{link controller='UserList'}filters[banned]=1{/link}">{lang}wcf.acp.user.quickSearch.banned{/lang}</a></li>
					<li><a href="{link controller='UserList'}sortField=registrationDate&sortOrder=DESC{/link}">{lang}wcf.acp.user.quickSearch.newest{/lang}</a></li>
					<li><a href="{link controller='UserList'}filters[isDisabled]=1&sortField=registrationDate&sortOrder=DESC{/link}">{lang}wcf.acp.user.quickSearch.disabled{/lang}</a></li>
					<li><a href="{link controller='UserList'}filters[pendingActivation]=1&sortField=registrationDate&sortOrder=DESC{/link}">{lang}wcf.acp.user.quickSearch.pendingActivation{/lang}</a></li>
					<li><a href="{link controller='UserList'}filters[disableAvatar]=1{/link}">{lang}wcf.acp.user.quickSearch.disabledAvatars{/lang}</a></li>
					<li><a href="{link controller='UserList'}filters[disableSignature]=1{/link}">{lang}wcf.acp.user.quickSearch.disabledSignatures{/lang}</a></li>
				</ul>
			</li>

			{if $__wcf->session->getPermission('admin.user.canAddUser')}
				<li><a href="{link controller='UserAdd'}{/link}" class="button buttonPrimary">{icon name='plus'} <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
			{/if}

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='footer'}
