<?php
require_once(dirname(__FILE__) . '/../../../wp-load.php');
$course_id = 18;
$course_steps_object = learndash_get_course_steps_object( $course_id );
if ($course_steps_object) {
    echo "STEPS FOR COURSE $course_id:\n";
    print_r($course_steps_object->get_steps());
} else {
    echo "No steps object found.\n";
}
?>