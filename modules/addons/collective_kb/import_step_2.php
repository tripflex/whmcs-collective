<?php
//echo "Categories:<pre>".print_r($queue_results['categories']['category'],1)."</pre>";
foreach ($queue_results['categories']['category'] as $v) {
    foreach ($v['articles'] as $a) {
        $articles[$a['id']] = $a;
        $articles[$a['id']]['catname'] = $v['name'];
        $articles[$a['id']]['catdesc'] = $v['description'];
    }
}
//echo "Articles:<pre>".print_r($articles,1)."</pre>";
//echo "POST Results:<pre>".print_r($_POST,1)."</pre>";
echo "<p>Please make any changes to these KB articles before submitting the form.!<br />The changes you make below will be applied before importing the articles into your knowledgebase!</p>\n";
echo "<p><strong>NOTE!!!</strong><br />Please replace Generic values like <strong>\"[COMPANY NAME]\"</strong> and <strong>\"[SUPPORT EMAIL]\"</strong> or <strong>\"[WEBSITE URL]\"</strong>!</p>";
echo "<form method=\"post\" action=\"\">";
$a = 1;
$b = count($_POST['KBSelection']);
foreach($_POST['KBSelection'] as $article) {
    $articleDetails = $articles[$article];
    echo "<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
    <tr><td class=\"fieldlabel\" width=\"200\">Title</td><td class=\"fieldarea\"><input type=\"text\" name=\"title[]\" value=\"{$articleDetails['title']}\" size=70></td></tr>
    <tr><td class=\"fieldlabel\">Local Category:</td><td class=\"fieldarea\"><select name=\"cattitle[]\">\n";
    echo "<option value=\"\">Choose One</option>\n";
    foreach (collective_getLocalCategories() as $key => $value) {
        echo "<option value=\"{$value['id']}\">{$value['name']}</option>\n";
    }
    echo "</select> or Remote Category: <input type=\"text\" name=\"customcat[]\" size=\"60\" value=\"{$articleDetails['catname']}\" /></td></tr>
    <tr><td class=\"fieldarea\">Remote Category Description</td><td><input type=\"text\" name=\"customcatdesc[]\" size=\"60\" value=\"{$articleDetails['catdesc']}\" /></td></tr>
    <tr><td class=\"fieldarea\" colspan=\"2\"><textarea name=\"article[]\" rows=\"18\" style=\"width:100%\">{$articleDetails['article']}</textarea></td></tr></table>";
    echo "<input type=\"hidden\" name=\"kbid[]\" value=\"{$articleDetails['id']}\" />\n<input type=\"hidden\" name=\"action\" value=\"import-3\" />\n";
    if ($a != $b) {
        echo "<br />";
    }
    $a++;
}
echo "<p><input type=\"submit\" name=\"retrieve\" value=\"Add articles to Local Knowledgebase!\" /></p>
</form>";
include(ROOTDIR . '/' . $customadminpath . '/editor/editor.php');
?>