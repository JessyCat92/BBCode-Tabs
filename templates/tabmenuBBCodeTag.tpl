{if $minimal}
    {$minimal}
{else}
<div id="tabMenuFix">
    <div class="tabMenuContainer">
        <nav class="tabMenu tabMenuFix">
            <ul>
                {foreach from=$tabs key=tabKey item=tab}
                    <li><a href="{@$__wcf->getAnchor($tab[id])}" title="{$tab.id}"><img class="iconTabMenuFix" src="{@$tab.icon}" /> {$tab.title}</a></li>
                {/foreach}
            </ul>
        </nav>
        {foreach from=$tabs key=tabKey item=tab}
            {if $tab.content|is_array}
                <div id="{$tab.id}" class="container containerPadding tabMenuContainer tabMenuConten">
                    <nav class="menu">
                        <ul>
                            {foreach from=$tab.content key=subTabKey item=subTab}
                                <li><a href="{@$__wcf->getAnchor($subTab[id])}"><img class="subiconTabMenuFix" src="{@$tab.icon}" /> {$subTab.title}</a></li>
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
                <div id="{$tab.id}" class="container containerPadding tabMenuConten tabMenuContainer">
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
    if (typeof(TabMenuLoaded) != "undefined" && TabMenuLoaded !== null) {
        WCF.TabMenu.init();
    }
    //]]>
</script>

{/if}