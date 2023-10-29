<?php
require_once('../../config.php');
require_login();

global $DB;
global $USER;

$res = ['process' => false];

if (isset($_POST['action']) && $_POST['action'] === 'vote' && isset($_POST['question'])) {
    $question_id = $_POST['question'];
    $vote = $_POST['vote'];
    $user_id = $USER->id;

    $exist = $DB->get_record('block_generalvote_votes', array('userid' => $user_id, 'questionid' => $question_id));
    
    if(!$exist)
    {
        $reg = new stdClass();
        $reg->userid = $user_id;
        $reg->questionid = $question_id;
        $reg->vote = $vote;

        $last_id = $DB->insert_record('block_generalvote_votes', $reg);

        $instance = $DB->get_record('block_instances', array('id' => $question_id));
        $block = block_instance('generalvote', $instance);

        if ($last_id) {
            $res = ['process' => true, 'html_results' => $block->render_results()];
        } else {
            $res = ['process' => false];
        }
    }
}

echo json_encode($res);
die();