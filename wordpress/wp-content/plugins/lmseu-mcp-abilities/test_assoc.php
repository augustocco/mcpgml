<?php
require_once(dirname(__FILE__) . '/../../../wp-load.php');
$all_lessons = get_posts(['post_type' => 'sfwd-lessons', 'numberposts' => -1]);
foreach($all_lessons as $l) {
    echo $l->ID . ' | ' . $l->post_title . ' - Course Setting: ' . learndash_get_setting($l->ID, 'course') . ' - course_id Meta: ' . get_post_meta($l->ID, 'course_id', true) . "\n";
}
echo "\nChecking Course Steps for Course 18:\n";
$steps = learndash_course_get_steps_by_type(18, 'sfwd-lessons');
print_r($steps);
?>