<?php
require_once(dirname(__FILE__) . '/../../../wp-load.php');
$courses = get_posts(array(
    'post_type' => 'sfwd-courses',
    'numberposts' => -1
));
foreach ($courses as $c) {
    $steps = learndash_course_get_steps_by_type($c->ID, 'sfwd-lessons');
    echo "Course: " . $c->post_title . " (ID: " . $c->ID . ") - Lessons: " . count($steps) . "\n";
}
?>