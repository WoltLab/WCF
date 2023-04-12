{include file='header' pageTitle=$title templateName='error' templateNameApplication='wcf'}

{if ENABLE_DEBUG_MODE}
{if $exception !== null}
<!--
{* A comment may not contain double dashes. *}
{@'--'|str_replace:'- -':$exception}
-->
{/if}
{/if}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{$title}</h1>
	</div>
</header>

<div class="section">
	<div class="box64 userException">
		{icon size=64 name='circle-exclamation'}
		<p>
			{@$message}
		</p>
	</div>
</div>

{include file='footer'}
