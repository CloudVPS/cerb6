<div id="headerSubMenu">
	<div style="padding:5px;">
	<a href="{devblocks_url}c=contacts&a=orgs{/devblocks_url}">organizations</a>
	 {* | <a href="{devblocks_url}c=contacts&a=people{/devblocks_url}">people</a> *}
	 | <a href="{devblocks_url}c=contacts&a=addresses{/devblocks_url}">addresses</a>
	 {if $active_worker->hasPriv('core.addybook.import')} | <a href="{devblocks_url}c=contacts&a=import{/devblocks_url}">import</a>{/if}
	</div>
</div> 
