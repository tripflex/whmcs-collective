<?php
//echo "<pre>".print_r($_POST,1)."</pre>";
echo "<form method=\"post\" action=\"\">";
echo "<p>Please make any changes to your KB articles before submitting!<br />Any changes made below will not affect your current knowledgebase, the changes will only be transmitted to the collective!</p>\n";
echo "<p><strong>NOTE!!!</strong><br />Please remove all references to your site and/or company and replace with Generic values like <strong>\"[COMPANY NAME]\"</strong> and <strong>\"[SUPPORT EMAIL]\"</strong> or <strong>\"[WEBSITE URL]\"</strong><br />This will help other users replace the text with their values, this also keeps you anonymous!</p>";
$remoteCats = collective_getCategories($vars['option1']);
$a = 1;
$b = count($_POST['KBSelection']);
foreach($_POST['KBSelection'] as $key => $value) {
    if (empty($value)) {
        continue;
    }
    $category = collective_getLocalCategoryDetails($value);
    $article = collective_getLocalArticleDetails($value);
    echo "<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
    <tr><td class=\"fieldlabel\">Title</td><td class=\"fieldarea\"><input type=\"text\" name=\"title[]\" value=\"{$article['title']}\" size=70></td></tr>
    <tr><td class=\"fieldlabel\">Remote Category:</td><td class=\"fieldarea\"><select name=\"cattitle[]\">\n";
    echo "<option value=\"\">Choose One</option>\n";
    foreach ($remoteCats['categories'] as $id => $name) {
        $selected = ($name == $category['name']) ? ' selected="selected"' :'';
        echo "<option value=\"{$id}\"{$selected}>{$name}</option>\n";
    }
    echo "</select> or Local Category: <input type=\"text\" name=\"customcat[]\" size=\"60\" value=\"{$category['name']}\" /></td></tr>
    <tr><td class=\"fieldarea\">Local Category Description</td><td><input type=\"text\" name=\"customcatdesc[]\" size=\"60\" value=\"{$category['description']}\" /></td></tr>
    <tr><td class=\"fieldarea\" colspan=\"2\"><textarea name=\"article[]\" rows=\"18\" style=\"width:100%\">{$article['article']}</textarea></td></tr></table>";
    echo "<input type=\"hidden\" name=\"kbid[]\" value=\"{$article['id']}\" />\n<input type=\"hidden\" name=\"action\" value=\"submit-3\" />\n";
    if ($a != $b) {
        echo "<br />";
    }
    $a++;
}
echo "<p><input type=\"submit\" name=\"submit\" value=\"Submit Articles to the Collective!\" /></p>
</form>";
include(ROOTDIR . '/' . $customadminpath . '/editor/editor.php');
?>