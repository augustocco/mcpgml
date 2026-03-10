<?php
require_once(dirname(__FILE__) . '/../../../wp-load.php');
$course_id = 18;
echo "--- DEBUG COURSE $course_id ---\n";

$lessons_list = learndash_get_course_lessons_list($course_id);
echo "learndash_get_course_lessons_list returned " . count($lessons_list) . " lessons:\n";
foreach ($lessons_list as $key => $l) {
    echo "  Key: $key | Title: " . $l['post']->post_title . " | ID: " . $l['post']->ID . "\n";
}

$steps = learndash_course_get_steps_by_type($course_id, 'sfwd-lessons');
echo "learndash_course_get_steps_by_type returned " . count($steps) . " steps:\n";
foreach ($steps as $s_id) {
    echo "  ID: $s_id | Title: " . get_the_title($s_id) . "\n";
}

$course_steps_object = learndash_get_course_steps_object($course_id);
if ($course_steps_object) {
    echo "Steps Object JSON: " . json_encode($course_steps_object->get_steps()) . "\n";
}
?>