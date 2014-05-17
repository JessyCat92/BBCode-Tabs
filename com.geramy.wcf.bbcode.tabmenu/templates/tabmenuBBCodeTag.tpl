{if $minimal}
    {$minimal}
{else}
    <div id="tabMenuFix" class="noTabProxy tabBBCodeMenu">
        <div class="tabMenuContainer">
            <nav class="tabMenu tabMenuFix">
                <ul>
                    {foreach from=$tabs key=tabKey item=tab}
                        {if $tab.icon.type=='url'}
                            <li><a href="{@$__wcf->getAnchor($tab[id])}" title="{$tab.title}" rel="nofollow"><img class="iconTabMenuFix" src="{@$tab.icon.string}" /> {$tab.title}</a></li>
                        {elseif $tab.icon.type=='icon'}
                            <li><a href="{@$__wcf->getAnchor($tab[id])}" title="{$tab.title}" rel="nofollow"><span class="{@$tab.icon.string}"> </span> {$tab.title}</a></li>
                        {else}
                            <li><a href="{@$__wcf->getAnchor($tab[id])}" title="{$tab.title}" rel="nofollow">{$tab.title}</a></li>
                        {/if}
                    {/foreach}
                </ul>
            </nav>
            {foreach from=$tabs key=tabKey item=tab}
                {if $tab.content|is_array}
                    <div id="{$tab.id}" class="container containerPadding tabMenuContainer tabMenuContent">
                        <nav class="menu">
                            <ul> 
                                {foreach from=$tab.content key=subTabKey item=subTab}
                                    {if $subTab.icon.type=='url'}
                                        <li><a href="{@$__wcf->getAnchor($subTab[id])}" title="{$subTab.title}" rel="nofollow"><img class="iconTabMenuFix" src="{@$subTab.icon.string}" /> {$subTab.title}</a></li>
                                    {elseif $subTab.icon.type=='icon'}
                                        <li><a href="{@$__wcf->getAnchor($subTab[id])}" title="{$subTab.title}" rel="nofollow"><span class="{@$subTab.icon.string}"> </span> {$subTab.title}</a></li>
                                    {else}
                                        <li><a href="{@$__wcf->getAnchor($subTab[id])}" title="{$subTab.title}" rel="nofollow">{$subTab.title}</a></li>
                                    {/if}
                                {/foreach}
                            </ul>
                        </nav>
                        {foreach from=$tab.content key=subTabKey item=subTab}
                            <div id="{$subTab.id}" class="hidden">
                                {@$subTab.content}
                                <p style="clear: both;"></p>
                            </div>
                        {/foreach}
                    </div>
                {else}
                    <div id="{$tab.id}" class="container containerPadding tabMenuContent tabMenuContainer">
                        <nav class="menu"><ul><li></li></ul></nav>
                        {@$tab.content}
                        <p style="clear: both;"></p>
                    </div>
                {/if}
            {/foreach}
        </div>
    </div>
	
	<script>
	//<![CDATA[
            WCF.TabMenu.init(); 
	//]]>
    </script>

	<script data-relocate="true">
	//<![CDATA[
			$(".noTabProxy div").each(function(){
				var id=$(this).attr("id");
				WCF.User.Profile.TabMenu.prototype._hasContent[id]=true;
			});

        //]]>
    </script>

{/if}