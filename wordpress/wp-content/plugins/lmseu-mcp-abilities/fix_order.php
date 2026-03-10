<?php
require_once(dirname(__FILE__) . '/../../../wp-load.php');

$courses = [17, 18, 22]; // Include 22 since it was also created backward
foreach ($courses as $course_id) {
    $course_steps_object = learndash_get_course_steps_object( $course_id );
    if ($course_steps_object) {
        $steps = $course_steps_object->get_steps(); // array
        // In LearnDash, $steps looks like ['h' => ..., 't' => ['sfwd-lessons' => ...]]
        
        // Easiest way to sort sfwd-lessons in course steps:
        $raw_steps = learndash_course_get_steps_by_type($course_id, 'sfwd-lessons');
        if (!empty($raw_steps)) {
            // Sort them by their post title (Lección 1, Lección 2, etc)
            $posts = get_posts(['post_type' => 'sfwd-lessons', 'post__in' => $raw_steps, 'orderby' => 'title', 'order' => 'ASC', 'posts_per_page' => -1]);
            $sorted_ids = wp_list_pluck($posts, 'ID');
            
            // Build the new steps array
            $new_steps = [];
            foreach ($sorted_ids as $id) {
                // Keep existing children if any
                $children = isset($steps['t']['sfwd-lessons'][$id]) ? $steps['t']['sfwd-lessons'][$id] : [];
                $new_steps['sfwd-lessons'][$id] = $children;
            }
            
            // Other types like sfwd-quiz might exist
            if (isset($steps['t'])) {
                foreach ($steps['t'] as $type => $items) {
                    if ($type !== 'sfwd-lessons') {
                        $new_steps[$type] = $items;
                    }
                }
            }
            
            $course_steps_object->set_steps( $new_steps );
            echo "Course $course_id lessons sorted by title ASC.\n";
        }
    }
}
?>