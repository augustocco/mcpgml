<?php
require_once(dirname(__FILE__) . '/../../../wp-load.php');

$lessons = get_posts(['post_type' => 'sfwd-lessons', 'numberposts' => -1]);
$count = 0;
foreach ($lessons as $lesson) {
    $course_id = (int) learndash_get_setting($lesson->ID, 'course');
    if ($course_id > 0) {
        if (function_exists('learndash_course_add_child_to_parent')) {
            $result = learndash_course_add_child_to_parent($course_id, $lesson->ID, $course_id);
            echo "Added Lesson {$lesson->ID} to Course $course_id: " . ($result ? "Success" : "Failed") . "\n";
            $count++;
        } else {
            // Fallback for older LearnDash versions
            $course_steps_object = learndash_get_course_steps_object( $course_id );
            if ( $course_steps_object ) {
                $course_steps_object->set_step_to_course_legacy( $lesson->ID );
                echo "Added Lesson {$lesson->ID} to Course $course_id (Legacy)\n";
                $count++;
            }
        }
    }
}
echo "Total updated: $count\n";
?>