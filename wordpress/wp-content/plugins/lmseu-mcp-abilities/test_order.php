<?php
require_once(dirname(__FILE__) . '/../../../wp-load.php');
$steps = learndash_course_get_steps_by_type(22, 'sfwd-lessons');
foreach($steps as $step_id) {
    echo get_the_title($step_id) . "\n";
}
?>