<?php
require_once(dirname(__FILE__) . '/../../../wp-load.php');
$user_id = 1; 
$course_id = 18; 
echo 'Testing course_id: ' . $course_id . "\n";
if (function_exists('learndash_user_progress_get_first_incomplete_step')) {
    $step_id = learndash_user_progress_get_first_incomplete_step($user_id, $course_id);
    echo 'First Incomplete Step (learndash_user_progress_get_first_incomplete_step): ' . $step_id . "\n";
}
if (function_exists('learndash_get_user_course_last_step')) {
    $last_step = learndash_get_user_course_last_step($user_id, $course_id);
    echo 'Last Step (learndash_get_user_course_last_step): ' . $last_step . "\n";
}
if (function_exists('learndash_user_get_last_active_step')) {
    $last_active_step = learndash_user_get_last_active_step($user_id, $course_id);
    echo 'Last Active (learndash_user_get_last_active_step): ' . $last_active_step . "\n";
}

global $wpdb;
$query_result = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT user_activity_meta.activity_meta_value FROM {$wpdb->prefix}learndash_user_activity as user_activity INNER JOIN {$wpdb->prefix}learndash_user_activity_meta as user_activity_meta ON user_activity.activity_id = user_activity_meta.activity_id WHERE user_activity.user_id=%d AND user_activity.post_id=%d AND user_activity.activity_type='course' AND user_activity_meta.activity_meta_key= 'steps_last_id' ORDER BY user_activity.activity_updated DESC LIMIT 1",
        $user_id,
        $course_id
    )
);
echo "Raw DB Query for last step: " . absint($query_result) . "\n";

?>