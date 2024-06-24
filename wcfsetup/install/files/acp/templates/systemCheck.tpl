{include file='header' pageTitle='wcf.acp.systemCheck'}

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.systemCheck{/lang}</h1>
</header>

{event name='userNotice'}

{capture assign='statusOk'}<span class="green">{icon name='check'}</span>{/capture}
{capture assign='statusSufficient'}<span class="orange">{icon name='circle-exclamation'}</span>{/capture}
{capture assign='statusInsufficient'}<span class="red">{icon name='triangle-exclamation'}</span>{/capture}

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.systemCheck.result{/lang}</h2>
	
	<dl{if !$results[status][web]} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.web{/lang}</dt>
		<dd>
			{if $results[status][web]}
				{@$statusOk} {lang}wcf.acp.systemCheck.pass{/lang}
			{else}
				{@$statusInsufficient} {lang}wcf.acp.systemCheck.insufficient{/lang}
			{/if}
		</dd>
	</dl>
	
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
	<h2 class="sectionTitle">{lang}wcf.acp.systemCheck.web{/lang}</h2>
	
	<dl{if !$results[web][https]} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.web.https{/lang}</dt>
		<dd>
			{if $results[web][https]}
				{@$statusOk} {lang}wcf.acp.systemCheck.pass{/lang}
			{else}
				{@$statusInsufficient} {lang}wcf.acp.systemCheck.notSupported{/lang}
			{/if}
			<small>{lang}wcf.acp.systemCheck.web.https.description{/lang}</small>
		</dd>
	</dl>
</section>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.systemCheck.php{/lang}</h2>
	
	<dl{if $results[php][version][result] === 'unsupported'} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.php.version{/lang}</dt>
		<dd>
			{if $results[php][version][result] === 'recommended'}
				{@$statusOk} {$results[php][version][value]}
			{elseif $results[php][version][result] === 'sufficient'}
				{@$statusSufficient} {$results[php][version][value]}
			{elseif $results[php][version][result] === 'deprecated'}
				{@$statusSufficient} {$results[php][version][value]}

				<woltlab-core-notice type="warning">{lang}wcf.acp.systemCheck.php.version.deprecated{/lang}</woltlab-core-notice>
			{else}
				{@$statusInsufficient} {$results[php][version][value]}
			{/if}
			
			<small>{lang}wcf.acp.systemCheck.php.version.description{/lang}</small>
		</dd>
	</dl>

	<dl{if !$results[php][x64]} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.php.x64{/lang}</dt>
		<dd>
			{if $results[php][x64]}
				{@$statusOk} {lang}wcf.acp.systemCheck.pass{/lang}
			{else}
				{@$statusInsufficient} {lang}wcf.acp.systemCheck.notSupported{/lang}
			{/if}
			<small>{lang}wcf.acp.systemCheck.php.x64.description{/lang}</small>
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
	
	<dl{if $results[php][opcache] === false} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.php.opcache{/lang}</dt>
		<dd>
			{if $results[php][opcache] === true}
				{@$statusOk} {lang}wcf.acp.systemCheck.pass{/lang}
			{elseif $results[php][opcache] === null}
				{@$statusSufficient} {lang}wcf.acp.systemCheck.notSupported{/lang}
			{else}
				{@$statusInsufficient} {lang}wcf.acp.systemCheck.php.opcache.broken{/lang}
			{/if}
			<small>{lang}wcf.acp.systemCheck.php.opcache.description{/lang}</small>
		</dd>
	</dl>
	
	<dl{if !$results[php][gd][result]} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.php.gd{/lang}</dt>
		<dd>
			{if $results[php][gd][result]}
				{@$statusOk} {lang}wcf.acp.systemCheck.pass{/lang}
			{else}
				<ul class="nativeList">
					{if !$results[php][gd][jpeg]}
						<li>{@$statusInsufficient} <kbd>jpeg</kbd></li>
					{/if}
					{if !$results[php][gd][png]}
						<li>{@$statusInsufficient} <kbd>png</kbd></li>
					{/if}
					{if !$results[php][gd][webp]}
						<li>{@$statusInsufficient} <kbd>webp</kbd></li>
					{/if}
				</ul>
			{/if}
			<small>{lang}wcf.acp.systemCheck.php.gd.description{/lang}</small>
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

	<dl{if !$results[mysql][mysqlnd]} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.mysql.mysqlnd{/lang}</dt>
		<dd>
			{if $results[mysql][mysqlnd]}
				{@$statusOk} {lang}wcf.acp.systemCheck.pass{/lang}
			{else}
				{@$statusInsufficient} {lang}wcf.acp.systemCheck.notSupported{/lang}
			{/if}
			<small>{lang}wcf.acp.systemCheck.mysql.mysqlnd.description{/lang}</small>
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

	<dl{if !$results[mysql][bufferPool][result]} class="formError"{/if}>
		<dt>{lang}wcf.acp.systemCheck.mysql.bufferPool{/lang}</dt>
		<dd>
			{if $results[mysql][bufferPool][result] === 'recommended'}
				{@$statusOk}
			{elseif $results[mysql][bufferPool][result] === 'sufficient'}
				{@$statusSufficient}
			{else}
				{@$statusInsufficient}
			{/if} {$results[mysql][bufferPool][value]|filesizeBinary}
			<small>{lang}wcf.acp.systemCheck.mysql.bufferPool.description{/lang}</small>
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
