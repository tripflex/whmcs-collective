<?php
if (!defined("WHMCS")) {
	die("This file cannot be accessed directly");
}

function collective_kb_config() {
    $configarray = array(
    "name" => "Collective KB",
    "description" => "This module gives all WHMCS members a way to share their knowledgebase articles with other members and install articles shared by other members.",
    "version" => "2.1",
    "author" => "Lithium Hosting",
    "fields" => array(
        "option1" => array ("FriendlyName" => "API KEY", "Type" => "text", "Size" => "50", "Description" => "Login to whmcscollective.com to get your API Key")
    ));
    return $configarray;
}

function collective_kb_activate() {
    full_query("ALTER TABLE `tblknowledgebase` ADD `relid` INT NOT NULL");
    return array('status' => 'success','description' => 'The WHMCS KB Collective has been activated!');
}

function collective_kb_deactivate() {
    return array('status' => 'success','description' => 'The WHMCS KB Collective has been deactivated!');
}

function collective_kb_upgrade($vars) {
    
}

function collective_kb_output($vars) {
    global $customadminpath;
    $modulelink = $vars['modulelink'];
    $queue_results = collective_getQueue($vars['option1']);
    $disabled = ($queue_results['result'] == 'success' || 'info') ? '' : ' disabled="disabled"';
    $query = collective_getInstalled($vars['option1']);
    $installed = ($query['result'] == 'success') ? $query['installed'] : array();
    $query = collective_getUploaded($vars['option1']);
    $uploaded = ($query['result'] == 'success') ? $query['uploaded'] : array();
    
    echo collective_infobox();
    
    if ($_POST['action'] == 'import-2') {
        require('import_step_2.php');
    } else if ($_POST['action'] == 'import-3') {
        require('import_step_3.php');
    } else if ($_POST['action'] == 'submit-2') {
        require('submit_step_2.php');
    } else if ($_POST['action'] == 'submit-3') {
        require('submit_step_3.php');
    } else if (empty($_POST['action'])) {
        $excluded = array_merge($installed,$uploaded);
        $excludedQuery = (!empty($excluded)) ? "tblknowledgebase.relid NOT IN (" . implode(',',$excluded) . ")" : '';
        $articles = $categories = array();
        $result = select_query("tblknowledgebase", "tblknowledgebase . *,tblknowledgebaselinks.categoryid AS category,tblknowledgebasecats.parentid",$excludedQuery, "title", "ASC", "", "tblknowledgebaselinks ON tblknowledgebase.id = tblknowledgebaselinks.articleid INNER JOIN tblknowledgebasecats ON tblknowledgebasecats.id = tblknowledgebaselinks.categoryid");
        
        while ($data = mysql_fetch_assoc($result)) {
            $articles[$data['category']][] = array('id' => $data['id'],'title' => $data['title'],'article' => $data['article']);
            if ($data['parentid']) {
                $categories[] = $data['parentid'];
            }
            $categories[] = $data['category'];
        }
        echo "<h3>Share / Submit KB Articles to the Collective</h3>\r\n";
        echo "<p>The articles below are pulled from your knowledgebase. Only the articles that have not been published to the Collective, or imported from the Collective are displayed!</p>\n";
        echo "<form name=\"kbsubstep1\" action=\"\" method=\"post\">\r\n";
        echo "<table><tr><td>";
        echo "<select name=\"KBSelection[]\" multiple=\"multiple\" size=\"25\">\r\n";
        if (!empty($categories)) {
            $categories = array_unique($categories);
            $allowedcats = "AND tblknowledgebasecats.id IN (" . implode(',',$categories) . ")";
            $result = select_query('tblknowledgebasecats LEFT JOIN tblknowledgebaselinks ON tblknowledgebasecats.id = tblknowledgebaselinks.categoryid', 'DISTINCT tblknowledgebasecats.id,tblknowledgebasecats.*', "tblknowledgebasecats.parentid=0 {$allowedcats}", 'tblknowledgebasecats`.`name', 'ASC', '');
            while ($data = mysql_fetch_assoc($result)) {
                echo "<optgroup label=\"{$data['name']}\">\n";
                if (!empty($articles[$data['id']])) {
                    foreach($articles[$data['id']] AS $article) {
                        echo "<option value=\"{$article['id']}\">{$article['title']}</option>\n";
                    }
                } else {
                    echo "<option value=\"0\">No articles found in the \"{$data['name']}\" category!</option>\n";
                }
                collective_getsubcats($data['id'],$categories,$articles);
                echo "</optgroup>\n";
            }
        } else {
            echo "<option value=\"0\">No articles available for publishing!</option>\n";
        }
        echo "</select><br />* Indicates sub-category";
        echo"</td></tr><tr><td><input type=\"hidden\" name=\"action\" value=\"submit-2\" /><input type=\"submit\" name=\"submit-2\" value=\"Proceed to Step Two\"{$disabled} /></td></tr></table></form>\r\n";
        echo "<script type=\"text/javascript\">\n\$(function() {\n\t\$('.articlePreview').hide();\n\t\$('#retrieveList').change(function() {\n\t\tvar kbid = \$('#retrieveList').val();\n\t\tif (kbid == null) {\n\t\t\t\$('.articlePreview').hide();\n\t\t\t\$('#articlePlaceholder').show();\n\t\t} else {\n\t\t\t\$('.articlePreview').hide();\n\t\t\t\$('#articlePlaceholder').hide();\n\t\t\t\$('#kbid-' + kbid).show();\n\t\t}\n\t}).change();\n});\n</script>\n";
        
        echo "<h3>Retrieve articles from your Queue</h3>\r\n";
        echo "<p>Only articles in your queue at the Collective are displayed below.  To modify your queue, please login to the Collective!</p>\n";
        if ($queue_results['result'] == 'success') {
            echo "<form name=\"kbretstep1\" action=\"\" method=\"post\" id=\"retrieveForm\">\r\n<table width=\"100%\">\r\n\t<tr><td style=\"vertical-align:top;\">";
            echo "<select name=\"KBSelection[]\" multiple=\"multiple\" size=\"25\" id=\"retrieveList\">\r\n";
            foreach ($queue_results['categories']['category'] AS $catid => $catdata) {
                echo "<optgroup label=\"{$catdata['name']}\" title=\"{$catdata['description']}\">\n";
                foreach ($catdata['articles'] AS $article) {
                    echo "<option value=\"{$article['id']}\">{$article['title']}</option>\n";
                    $divs[] = "<div id=\"kbid-{$article['id']}\" class=\"articlePreview\"><p><strong>{$article['title']}</strong></p>" . htmlspecialchars_decode($article['article']) . "</div>\n";
                }
                echo "</optgroup>\n";
            }
            echo "</select><br /><br /><input type=\"hidden\" name=\"action\" value=\"import-2\" /><input type=\"submit\" name=\"retrieve\" value=\"Proceed to Step Two\" /></td>\r\n<td align=\"left\" style=\"overflow: auto;padding-left: 20px;vertical-align: top;width: 100%;\">";
            echo "<fieldset><legend>Article Preview</legend><div id=\"articlePlaceholder\">This is where the articles will be displayed!</div>" . implode("\n",$divs) . "</fieldset></td></tr>\r\n";
            echo "</table></form>\r\n";
        } else {
            echo "<p>{$queue_results['message']}</p>";
        }
    }
}

function collective_kb_sidebar($vars) {
    $modulelink = $vars['modulelink'];
    $version = $vars['version'];
    $option1 = $vars['option1'];
    $option2 = $vars['option2'];
    $option3 = $vars['option3'];
    $option4 = $vars['option4'];
    $option5 = $vars['option5'];
    $LANG = $vars['_lang'];

    $sidebar = '<span class="header"><img src="images/icons/addonmodules.png" class="absmiddle" width="16" height="16" /> Collective Knowledgebase</span>
<ul class="menu">
        <li><a href="#">Version: '.$version.'</a></li>
    </ul>';
    return $sidebar;
}

function collective_infobox() {
    $infobox = '';
    if (!empty($_SESSION['kb_messages'])) {
        foreach ($_SESSION['kb_messages'] AS $k => $message) {
            if ($message['type'] == "error") {
                $type = "error";
            } else if ($message['type'] == "success") {
                $type = "success";
            } else {
                $type = "info";
            }
            $infobox .= "<div class=\"{$type}box\"><strong>{$message['title']}</strong><br />{$message['message']}</div>\n";
            unset($_SESSION['kb_messages'][$k]);
        }
    }
    return $infobox;
}

function collective_getsubcats($id,$allowed,$articles) {
    $allowedcats = "AND tblknowledgebasecats.id IN (" . implode(',',$allowed) . ")";
    $result = select_query('tblknowledgebasecats', 'DISTINCT tblknowledgebasecats.id,tblknowledgebasecats.*', "tblknowledgebasecats.parentid={$id} {$allowedcats}", 'tblknowledgebasecats`.`name', 'ASC', '', 'tblknowledgebaselinks ON tblknowledgebasecats.id = tblknowledgebaselinks.categoryid');
    while ($data = mysql_fetch_assoc($result)) {
        echo "<optgroup label=\"{$data['name']} *\">\n";
        if (!empty($articles[$data['id']])) {
            foreach($articles[$data['id']] AS $article) {
                echo "<option value=\"{$article['id']}\">{$article['title']}</option>\n";
            }
        } else {
            echo "<option value=\"0\">No articles found in the \"{$data['name']}\" sub-category!</option>\n";
        }
        collective_getsubcats($data['id'],$allowed,$articles);
        echo "</optgroup>\n";
    }
}

function collective_addInstalled($apikey,$installed,$debug=false) {
    $postfields['apikey'] = $apikey;
    $postfields['action'] = 'setinstalled';
    $postfields['installed'] = base64_encode(serialize($installed));
    $postfields['responsetype'] = 'json';
    
    return collective_kb_call($postfields,$debug);
}

function collective_getQueue($apikey,$debug=false) {
    $postfields['apikey'] = $apikey;
    $postfields['action'] = 'getqueue';
    $postfields['responsetype'] = 'json';
    
    return collective_kb_call($postfields,$debug);
}

function collective_getInstalled($apikey,$debug=false) {
    $postfields['apikey'] = $apikey;
    $postfields['action'] = 'getinstalled';
    $postfields['responsetype'] = 'json';
    
    return collective_kb_call($postfields,$debug);
}

function collective_getUploaded($apikey,$debug=false) {
    $postfields['apikey'] = $apikey;
    $postfields['action'] = 'getuploaded';
    $postfields['responsetype'] = 'json';
    
    return collective_kb_call($postfields,$debug);
}

function collective_getArticles($catid,$apikey,$debug=false) {
    $postfields['apikey'] = $apikey;
    $postfields['action'] = 'getarticles';
    $postfields['category'] = $catid;
    $postfields['responsetype'] = 'json';
    
    return collective_kb_call($postfields,$debug);
}

function collective_getCategories($apikey,$debug=false) {
    $postfields['apikey'] = $apikey;
    $postfields['action'] = 'getcategories';
    $postfields['responsetype'] = 'json';
    
    return collective_kb_call($postfields,$debug);
}

function collective_kb_call($postfields,$debug=false) {
    $url = 'http://www.whmcscollective.com/api.php';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 100);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    
    $jsondata = curl_exec($ch);
    if (curl_error($ch)) {
        die("Connection Error: ".curl_errno($ch).' - '.curl_error($ch));
    }
    curl_close($ch);
    
    $arr = json_decode($jsondata,1);

    logModuleCall('collective kb', $postfields['action'], $postfields, $jsondata, $arr, array($postfields['apikey']));
    return $arr;
}

function collective_getLocalArticleDetails($articleID) {
    $result = select_query('tblknowledgebase','',array('id' => $articleID));
    $data = mysql_fetch_assoc($result);
    return $data;
}

function collective_getLocalCategoryDetails($articleID) {
    $result = select_query('tblknowledgebasecats','name,description',array('tblknowledgebaselinks.articleid' => $articleID),'','','','tblknowledgebaselinks ON tblknowledgebaselinks.categoryid = tblknowledgebasecats.id');
    $data = mysql_fetch_assoc($result);
    return $data;
}

function collective_getLocalCategories() {
    $result = select_query('tblknowledgebasecats','tblknowledgebasecats.*',array('tblknowledgebasecats.parentid' => '0'), 'tblknowledgebasecats`.`name', 'ASC', '', 'tblknowledgebaselinks ON tblknowledgebasecats.id = tblknowledgebaselinks.categoryid');
    while ($data = mysql_fetch_assoc($result)) {
        $return[] = $data;
    }
    return $return;
}

function collective_getLocalCatID($catName, $catDesc) {
    $result = select_query('tblknowledgebasecats','id',array('name' => $catName));
    if (mysql_num_rows($result)) {
        $row = mysql_fetch_row($result);
        $id = $row[0];
    } else {
        $id = insert_query('tblknowledgebasecats',array('name' => $catName,'description' => $catDesc));
    }
    return $id;
}

function collective_localArticleExists($articletitle) {
  $result = select_query('tblknowledgebase','id',array('title' => $articletitle));
  return (!mysql_num_rows($result)) ? FALSE : TRUE;
}
?>