<?php
namespace wcf\system\bbcode;
use wcf\system\WCF;

/**
 * Parses the tabmenuBBCode bbcode tag.
 *
 * @package	com.geramy.wcf.tabmenu.bbcode
 * @copyright	geramy, nerdus
 * @author	nerdus, geramy
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @category	WCF
 * @parameter
 * @usage: [tabmenu][tab='name','icon']contentab1[tab='name2','icon2'][subtab='name','icon']text21[/tabmenu]
 */
class TabmenuBBCode extends AbstractBBCode {
    /**
     * @see	wcf\system\bbcode\IBBCode::getParsedTag()
     */
    public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {

        $trimmedContent = trim($content);
        $tabs=array();
        // parse possible tabs
        preg_match_all('~\[tab=[^\[\]\\n]+\]~i', $content, $possibleTabs,PREG_OFFSET_CAPTURE);

        $possibleTabs = $possibleTabs[0];
        if (!empty($possibleTabs)) {
            foreach ($possibleTabs as $key => $possibleTab) {
                $offset=$possibleTab[1];
                $possibleTab=$possibleTab[0];
                // get acc attributes (see BBCodeParser::buildTagAttributes)
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
                // if acc has no title and no icon, we can't use it
                if (strlen($matches[1][0]) > 0 || (isset($matches[1][1]) && strlen($matches[1][1]) > 0)) {
                    $accKey = count($tabs);
                    $tabs[$accKey] = array(
                        'title' => $matches[1][0],
                        'icon' => (isset($matches[1][1]) ? $matches[1][1] : ''),
                        'content' => null,
                        'offset' => $offset,
                        'tag' =>$possibleTab
                    );
                }
                else {
                    unset($possibleTabs[$key]);
                }
            }
        }

        reset($possibleTabs);
        foreach ($tabs as $accKey => $accData) {
            $length=strlen($tabs[$accKey]["tag"]);
            $currOffset=$tabs[$accKey]["offset"];
            if(isset($tabs[$accKey+1])){
                $nextOffset=$tabs[$accKey+1]["offset"];
                $contentBeforeNext=substr($content,0,$nextOffset);
                $accContent=substr($contentBeforeNext,$currOffset+$length);
            }
            else
            {
                $accContent=substr($content,$currOffset+$length);
            }

            if(strpos($accContent,"subtab")===false){
                $tabs[$accKey]['content'] =  $accContent;
            }
            else
            {



                $subtabs=array();
                $possibleSubTabs=array();
                preg_match_all('~\[subtab=[^\[\]\\n]+\]~i', $accContent, $possibleSubTabs,PREG_OFFSET_CAPTURE);


                $possibleSubTabs = $possibleSubTabs[0];
                if (!empty($possibleSubTabs)) {
                    foreach ($possibleSubTabs as $newsubkey => $possibleTab) {
                        $offset=$possibleTab[1];
                        $possibleTab=$possibleTab[0];
                        // get acc attributes (see BBCodeParser::buildTagAttributes)
                        $string = mb_substr($possibleTab, 8, -1);
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
                        // if acc has no title and no icon, we can't use it
                        if (strlen($matches[1][0]) > 0 || (isset($matches[1][1]) && strlen($matches[1][1]) > 0)) {
                            $accSubSubKey = count($subtabs);
                            $subtabs[$accSubSubKey] = array(
                                'title' => $matches[1][0],
                                'icon' => (isset($matches[1][1]) ? $matches[1][1] : ''),
                                'content' => null,
                                'offset' => $offset,
                                'tag' =>$possibleTab
                            );
                        }
                        else {

                            unset($possibleSubTabs[$newsubkey]);
                        }
                    }
                }



                foreach ($subtabs as $subKey => $subData) {
                    $length=strlen($subtabs[$subKey]["tag"]);
                    $currOffset=$subtabs[$subKey]["offset"];
                    if(isset($subtabs[$subKey+1])){
                        $nextOffset=$subtabs[$subKey+1]["offset"];
                        $contentBeforeNext=substr($accContent,0,$nextOffset);
                        $subContent=substr($contentBeforeNext,$currOffset+$length);
                    }
                    else
                    {
                        $subContent=substr($accContent,$currOffset+$length);
                    }
                    $subtabs[$subKey]['content'] =  $subContent;
                }

                //
                $tabs[$accKey]['content'] = $subtabs;

            }
        }

        //iconparse
        foreach($tabs as $key => $tab){
            if($tab["icon"]!=""){
                $iconContent=$tab["icon"];
                $icon=array();
                if(strpos($iconContent,'icon-')===false){
                    $icon["type"]="url";
                    $icon["string"]=$iconContent;
                }
                else
                {
//                    $iconContent=str_replace("icon-","",$iconContent);
//                    $iconparts=explode("/",$iconContent);
                    $string="icon icon16 ".$iconContent;
//                    if(isset($iconparts[1])){
//                        $string.=" icon-".$iconparts[1];
//                    }
                    $icon["type"]="icon";
                    $icon["string"]=$string;
                }
                $tabs[$key]["icon"]=$icon;
            }
            else
            {
                $tabs[$key]["icon"]=array("type"=>"none", "string"=>"");
            }

            if(is_array($tab["content"])){
                foreach($tab["content"] as $keysub => $subtab){
                    if($subtab["icon"]!=""){
                        $iconContent=$subtab["icon"];
                        $icon=array();
                        if(strpos($iconContent,'icon:')===false){
                            $icon["type"]="url";
                            $icon["string"]=$iconContent;
                        }
                        else
                        {
                            $iconContent=str_replace("icon:","",$iconContent);
                            $iconparts=explode("/",$iconContent);
                            $string="icon icon16 icon-".$iconparts[0];
                            if(isset($iconparts[1])){
                                $string.=" icon-".$iconparts[1];
                            }
                            $icon["type"]="icon";
                            $icon["string"]=$string;
                        }
                        $tabs[$key]["content"][$keysub]["icon"]=$icon;
                    }
                    else
                    {
                        $tabs[$key]["content"][$keysub]["icon"]=array("type"=>"none", "string"=>"");
                    }
                }
            }
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
//        $debug=print_r($tabs ,true);
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
