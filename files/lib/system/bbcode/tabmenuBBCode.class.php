<?php
namespace wcf\system\bbcode;
use wcf\system\WCF;

/**
 * Parses the tabmenuBBCode bbcode tag.
 *
 * @package	com.geramy.wcf.progressBarBBCode.bbcode
 * @copyright	geramy
 * @author	geramy (mit Hilfe von anderen BBcode Plugins aufgebaut)
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @category	WCF
 * @parameter
 * @usage: [tabmenu][tab='name','icon']contentab1[tab='name2','icon2'][subtab='name','icon']text21[/tabmenu]
 */
class tabmenuBBCode extends AbstractBBCode {
    /**
     * @see	wcf\system\bbcode\IBBCode::getParsedTag()
     */
    public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {

        $trimmedContent = trim($content);
        $tabs=array();
        // parse possible tabs
        preg_match_all('~\[tab=[^\[\]\\n]+\]~i', $content, $possibleTabs);

        $possibleTabs = $possibleTabs[0];
        if (!empty($possibleTabs)) {
            foreach ($possibleTabs as $key => $possibleTab) {
                // get tab attributes (see BBCodeParser::buildTagAttributes)
                $string = mb_substr($possibleTab, 5, -1);
                preg_match_all("~(?:^|,)('[^'\\\\]*(?:\\\\.[^'\\\\]*)*'|[^,]*)~", $string, $matches);
                for ($i = 0, $j = count($matches[1]); $i < $j; $i++) {
                    // trim to allow backwards compatibility with version 1.0.x
                    $matches[1][$i] = trim($matches[1][$i]);
                    // remove quotes
                    if (mb_substr($matches[1][$i], 0, 1) == "'" && mb_substr($matches[1][$i], -1) == "'") {
                        $matches[1][$i] = str_replace("\'", "'", $matches[1][$i]);
                        $matches[1][$i] = str_replace("\\\\", "\\", $matches[1][$i]);
                        $matches[1][$i] = mb_substr($matches[1][$i], 1, -1);
                    }
                }
                // if tab has no title and no icon, we can't use it
                if (strlen($matches[1][0]) > 0 || (isset($matches[1][1]) && strlen($matches[1][1]) > 0)) {
                    $tabKey = count($tabs);
                    $tabs[$tabKey] = array(
                        'title' => $matches[1][0],
                        'icon' => (isset($matches[1][1]) ? $matches[1][1] : ''),
                        'content' => null
                    );
                }
                else {
                    unset($possibleTabs[$key]);
                }
            }
        }

        // // get tab contents
        $usedTabTitles = array();
        reset($possibleTabs);


        foreach ($tabs as $tabKey => $tabData) {
            $currentTab = current($possibleTabs);
            $nextTab = next($possibleTabs);
            preg_match("~".preg_quote($currentTab,'~')."(.*)".($nextTab ? preg_quote($nextTab,'~') : "$")."~is", $trimmedContent, $match);
            // avoid an odd "undefined index" error that occurs with specific server configurations and tab menus that contain huge amounts of html code
            if (isset($match[1])) {
                $tabContent = preg_replace('~(<br />|\s)*$~i', '', preg_replace('~^(<br />|\s)*~i', '', $match[1]));
            }
            else {
                // clear $tabs and leave the parser to return the unparsed bbcode
                $tabs = array();
                break;
            }

            // check if title was already used, if content contains any html errors and if the icon url is valid
            if (in_array($tabData['title'], $usedTabTitles) || (!empty($tabData['icon']) && !preg_match('~^[^?\s]+$~i', $tabData['icon']))) {
                // clear $tabs and leave the parser to return the unparsed bbcode
                $tabs = array();
                break;
            }
            $usedTabTitles[] = $tabData['title'];

            // parse possible sub tabs
            $subTabs = array();
            preg_match_all('~\[subtab=[^\[\]\\n]+\]~i', $tabContent, $possibleSubtabs);
            $possibleSubtabs = $possibleSubtabs[0];
            //if sub tabs exist, $tabs[][content] holds another array of sub tabs
            if (!empty($possibleSubtabs)) {
                foreach ($possibleSubtabs as $key => $possibleSubtab) {
                    // get sub tab attributes
                    $string = mb_substr($possibleSubtab, 8, -1);
                    preg_match_all("~(?:^|,)('[^'\\\\]*(?:\\\\.[^'\\\\]*)*'|[^,]*)~", $string, $matches);
                    for ($i = 0, $j = count($matches[1]); $i < $j; $i++) {
                        // trim to allow backwards compatibility with version 1.0.x
                        $matches[1][$i] = trim($matches[1][$i]);
                        // remove quotes
                        if (mb_substr($matches[1][$i], 0, 1) == "'" && mb_substr($matches[1][$i], -1) == "'") {
                            $matches[1][$i] = str_replace("\'", "'", $matches[1][$i]);
                            $matches[1][$i] = str_replace("\\\\", "\\", $matches[1][$i]);
                            $matches[1][$i] = mb_substr($matches[1][$i], 1, -1);
                        }
                    }
                    // if sub tab has no title and no icon, we can't use it
                    if (strlen($matches[1][0]) > 0 || (isset($matches[1][1]) && strlen($matches[1][1]) > 0)) {
                        $subTabKey = count($subTabs);
                        $subTabs[$subTabKey] = array(
                            'title' => $matches[1][0],
                            'icon' => (isset($matches[1][1]) ? $matches[1][1] : ''),
                            'content' => null
                        );
                    }
                    else {
                        unset($possibleSubtabs[$key]);
                    }
                }

                // get sub tab contents
                $usedSubTabTitles = array();
                reset($possibleSubtabs);
                foreach ($subTabs as $subTabKey => $subTabData) {
                    $currentSubtab = current($possibleSubtabs);
                    $nextSubtab = next($possibleSubtabs);
                    preg_match("~".preg_quote($currentSubtab,'~')."(.*)".($nextSubtab ? preg_quote($nextSubtab,'~') : "$")."~is", $tabContent, $match);
                    $subTabContent = preg_replace('~(<br />|\s)*$~i', '', preg_replace('~^(<br />|\s)*~i', '', $match[1]));
                    $subTabs[$subTabKey]['content'] = $subTabContent;

                    // check if title was already used, if content contains any html errors and if the icon url is valid
                    if (in_array($subTabData['title'], $usedSubTabTitles) || (!empty($subTabData['icon']) && !preg_match('~^[^?\s]+$~i', $subTabData['icon']))) {
                        // clear $tabs and leave the parser to return the unparsed bbcode
                        $tabs = array();
                        break 2;
                    }
                    $usedSubTabTitles[] = $subTabData['title'];
                }
            }

            // otherwise $tabs[][content] contains the content string
            $tabs[$tabKey]['content'] = (empty($subTabs) ? $tabContent : $subTabs);
        }


        // // check if parsing was successful
        if (empty($tabs)) {
            return $openingTag['source'] . $content . $closingTag['source'];
        }
        $id=uniqid();
        foreach($tabs as $key => $tab){
            if(is_array($tab["content"])){
                foreach($tab["content"] as $keysub => $subtab){
                    $tabs[$key]["content"][$keysub]["id"]=$id."_".$key."_".$keysub;
                }
            }
            $tabs[$key]["id"]=$id."_".$key;
        }

//        //@todo: delete!!!
//        $debug=print_r($tabs,true);
//        WCF::getTPL()->assign(array('debug' => $debug));

        if ($parser->getOutputType() == 'text/html') {
            WCF::getTPL()->assign(array('minimal' => false));
            WCF::getTPL()->assign(array('tabs' => $tabs));
            return WCF::getTPL()->fetch('tabmenuBBCodeTag');
        }
        else{

            if(is_array($tabs[0]["content"])){
                $tabs[0]["content"]=$tabs[0]["content"][0]["content"];
            }

            WCF::getTPL()->assign(array('minimal' => "[Tabmenu: ".$tabs[0]["title"]."] ".$tabs[0]["content"]));
            return WCF::getTPL()->fetch('tabmenuBBCodeTag');

        }
    }

}
