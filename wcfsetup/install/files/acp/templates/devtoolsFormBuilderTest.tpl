{* TODO: delete file again after finishing the form builder API *}

{include file='header'}

<header class="contentHeader">
	<h1 class="contentTitle">Testing the new Form Builder API</h1>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">Successfully validated the form data. The parameters array that would be passed to the database object action constructor is shown below.</p>
{/if}

{if $data|isset}
	<div class="section">
		<h2 class="sectionTitle">Form Data</h2>
		
		<pre>{$data|var_dump}</pre>
	</div>
{/if}

{@$form->getHtml()}

{include file='footer'}
