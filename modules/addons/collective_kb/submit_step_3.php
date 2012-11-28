<?php
$articles = $newData = array();
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
        $articles[$key]['catname'] = $value['catname'];
        $articles[$key]['catdesc'] = $value['catdesc'];
    } else {
        $articles[$key]['catid'] = $value['catid'];
    }
    $articles[$key]['article'] = htmlspecialchars($value['article']);
    $articles[$key]['title'] = $value['title'];
    $articles[$key]['relid'] = $value['relid'];
}

$postfields['articles'] = json_encode($articles);
$postfields['apikey'] = $vars['option1'];
$postfields['action'] = 'putarticles';
$postfields['responsetype'] = 'json';

$results = collective_kb_call($postfields);
foreach ($results['result'] as $v) {
    $articledetails = collective_getLocalArticleDetails($v['id']);
    if ($v['result'] == 'success') {
        update_query('tblknowledgebase',array('relid' => $v['newid']),array('id' => $v['id']));
        $_SESSION['kb_messages'][] = array('title' => 'Submission Successful!','type' =>'success','message' => 'Article: "' . $articledetails['title'] . '"' . $v['message']);
    } else {
        $_SESSION['kb_messages'][] = array('title' => 'Submission Failed!','type' => 'error','message' => 'Article: "' . $articledetails['title'] . '"' . $v['message']);
    }
}
header('Location: ' . $modulelink);
exit;
?>