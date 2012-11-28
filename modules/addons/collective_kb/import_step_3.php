<?php
$installed = $newData = array();
foreach ($_POST['title'] as $key => $value) {
    $newData[$key]['title'] = $value;
    $newData[$key]['article'] = $_POST['article'][$key];
    if (!empty($_POST['customcat'][$key])) {
        $newData[$key]['catname'] = $_POST['customcat'][$key];
        $newData[$key]['catdesc'] = $_POST['customcatdesc'][$key];
    } else {
        $newData[$key]['catid'] = $_POST['cattitle'][$key];
    }
    $newData[$key]['relid'] = $_POST['kbid'][$key];
}

foreach ($newData as $key => $value) {
    if (!empty($value['catname'])) {
        $categoryID = collective_getLocalCatID($value['catname'], $value['catdesc']);
    } else {
        $categoryID = $value['catid'];
    }
    if (!collective_localArticleExists($value['title'])) {
        $installed[] = $value['relid'];
        $newid = insert_query('tblknowledgebase', array('title' => $value['title'],'article' => html_entity_decode($value['article']),'private' => 'on','relid' => $value['relid']));
        insert_query('tblknowledgebaselinks', array('categoryid' => $categoryID,'articleid' => $newid));
    } else {
        $_SESSION['kb_messages'][] = array('title' => 'Import Failed','type' => 'error', 'message' => "Failed to import \"{$value['title']}\" because it already exists!  Change the title and try again!");
    }
}

if (!empty($installed)) {
    collective_addInstalled($vars['option1'],$installed,true);

    $_SESSION['kb_messages'][] = array('title' => 'Import Successful!','type' => 'success', 'message' => 'Please edit your articles and then mark them as not private!');
}

header('Location: ' . $modulelink);
exit;
?>