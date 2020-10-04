{include file='header' pageTitle='wcf.acp.systemCheck'}

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.systemCheck{/lang}</h1>
</header>

{event name='userNotice'}

{capture assign='statusOk'}<span class="icon icon16 fa-check green"></span>{/capture}
{capture assign='statusSufficient'}<span class="icon icon16 fa-exclamation-triangle orange"></span>{/capture}
{capture assign='statusInsufficient'}<span class="icon icon16 fa-exclamation-triangle red"></span>{/capture}

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.systemCheck.result{/lang}</h2>
	
	<dl{if !$results[status][php]} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.php{/lang}</dt>
		<dd>
			{if $results[status][php]}
				{if $results[php][version][result] === 'sufficient'}
					{@$statusSufficient} {lang}wcf.acp.systemCheck.sufficient{/lang}
				{else}
					{@$statusOk} {lang}wcf.acp.systemCheck.pass{/lang}
				{/if}
			{else}
				{@$statusInsufficient} {lang}wcf.acp.systemCheck.insufficient{/lang}
			{/if}
		</dd>
	</dl>
	
	<dl{if !$results[status][mysql]} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.mysql{/lang}</dt>
		<dd>
			{if $results[status][mysql]}
				{@$statusOk} {lang}wcf.acp.systemCheck.pass{/lang}
			{else}
				{@$statusInsufficient} {lang}wcf.acp.systemCheck.insufficient{/lang}
			{/if}
		</dd>
	</dl>
	
	<dl{if !$results[status][directories]} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.directories{/lang}</dt>
		<dd>
			{if $results[status][directories]}
				{@$statusOk} {lang}wcf.acp.systemCheck.pass{/lang}
			{else}
				{@$statusInsufficient} {lang}wcf.acp.systemCheck.insufficient{/lang}
			{/if}
		</dd>
	</dl>
</section>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.systemCheck.php{/lang}</h2>
	
	<dl{if $results[php][version][result] === 'unsupported'} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.php.version{/lang}</dt>
		<dd>
			{if $results[php][version][result] === 'recommended'}
				{@$statusOk}
			{elseif $results[php][version][result] === 'sufficient'}
				{@$statusSufficient}
			{else}
				{@$statusInsufficient}
			{/if}
			{$results[php][version][value]}
			<small>{lang}wcf.acp.systemCheck.php.version.description{/lang}</small>
		</dd>
	</dl>
	
	<dl{if !$results[php][extension]|empty} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.php.extension{/lang}</dt>
		<dd>
			{if $results[php][extension]|empty}
				{@$statusOk} {lang}wcf.acp.systemCheck.pass{/lang}
			{else}
				<ul class="nativeList">
					{foreach from=$results[php][extension] item=extension}
						<li>{@$statusInsufficient} <kbd>{$extension}</kbd></li>
					{/foreach}
				</ul>
			{/if}
			<small>{lang}wcf.acp.systemCheck.php.extension.description{/lang}</small>
		</dd>
	</dl>
	
	<dl{if !$results[php][memoryLimit][result]} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.php.memoryLimit{/lang}</dt>
		<dd>
			{if $results[php][memoryLimit][result]}{@$statusOk}{else}{@$statusInsufficient}{/if} {$results[php][memoryLimit][value]}
			<small>{lang}wcf.acp.systemCheck.php.memoryLimit.description{/lang}</small>
		</dd>
	</dl>
	
	<dl{if !$results[php][sha256]} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.php.sha256{/lang}</dt>
		<dd>
			{if $results[php][sha256]}
				{@$statusOk} {lang}wcf.acp.systemCheck.pass{/lang}
			{else}
				{@$statusInsufficient} {lang}wcf.acp.systemCheck.notSupported{/lang}
			{/if}
			<small>{lang}wcf.acp.systemCheck.php.sha256.description{/lang}</small>
		</dd>
	</dl>
</section>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.systemCheck.mysql{/lang}</h2>
	
	<dl{if !$results[mysql][result]} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.mysql.version{/lang}</dt>
		<dd>
			{if $results[mysql][result]}{@$statusOk}{else}{@$statusInsufficient}{/if}
			{if $results[mysql][mariadb]}MariaDB{else}MySQL{/if} {$results[mysql][version]}
			<small>{lang}wcf.acp.systemCheck.mysql.version.description{/lang}</small>
		</dd>
	</dl>
	
	<dl{if !$results[mysql][innodb]} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.mysql.innodb{/lang}</dt>
		<dd>
			{if $results[mysql][innodb]}
				{@$statusOk} {lang}wcf.acp.systemCheck.pass{/lang}
			{else}
				{@$statusInsufficient} {lang}wcf.acp.systemCheck.notSupported{/lang}
			{/if}
			<small>{lang}wcf.acp.systemCheck.mysql.innodb.description{/lang}</small>
		</dd>
	</dl>
	
	<dl{if !$results[mysql][foreignKeys]} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.mysql.foreignKeys{/lang}</dt>
		<dd>
			{if $results[mysql][foreignKeys]}
				{@$statusOk} {lang}wcf.acp.systemCheck.pass{/lang}
			{else}
				{@$statusInsufficient} {lang}wcf.acp.systemCheck.notFound{/lang}
			{/if}
			<small>{lang}wcf.acp.systemCheck.mysql.foreignKeys.description{/lang}</small>
		</dd>
	</dl>
	
	<dl{if !$results[mysql][searchEngine][result]} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.mysql.searchEngine{/lang}</dt>
		<dd>
			{if $results[mysql][searchEngine][result]}
				{@$statusOk} {lang}wcf.acp.systemCheck.pass{/lang}
			{else}
				{@$statusInsufficient} {lang}wcf.acp.systemCheck.mysql.searchEngine.incorrect{/lang}
			{/if}
			<small>{lang}wcf.acp.systemCheck.mysql.searchEngine.description{/lang}</small>
		</dd>
	</dl>
</section>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.systemCheck.directories{/lang}</h2>
	
	<dl{if !$results[directories]|empty} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.directories.writable{/lang}</dt>
		<dd>
			{if $results[directories]|empty}
				{@$statusOk} {lang}wcf.acp.systemCheck.pass{/lang}
			{else}
				<ul class="nativeList">
					{foreach from=$results[directories] item=directory}
						<li>{@$statusInsufficient} <kbd>{$directory}</kbd></li>
					{/foreach}
				</ul>
			{/if}
			<small>{lang}wcf.acp.systemCheck.directories.writable.description{/lang}</small>
		</dd>
	</dl>
</section>

{include file='footer'}
