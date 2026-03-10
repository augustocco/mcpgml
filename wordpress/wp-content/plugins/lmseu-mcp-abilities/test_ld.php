<?php
require_once(dirname(__FILE__) . '/../../../wp-load.php');
$course_id = 22;
$user_id = 1;

$lessons = learndash_get_course_lessons_list($course_id, $user_id);
if (!empty($lessons)) {
    echo '<pre>'; 
    $keys = array_keys($lessons);
    echo 'KEYS: ' . implode(', ', $keys) . "\n";
    $first_key = $keys[0];
    echo 'TYPE OF ITEM ' . $first_key . ': ' . gettype($lessons[$first_key]) . "\n";
    if (is_object($lessons[$first_key])) {
        echo 'CLASS OF ITEM ' . $first_key . ': ' . get_class($lessons[$first_key]) . "\n";
        echo 'ID OF ITEM ' . $first_key . ': ' . $lessons[$first_key]->ID . "\n";
    } elseif (is_array($lessons[$first_key])) {
        echo 'HAS POST KEY: ' . (isset($lessons[$first_key]['post']) ? 'YES' : 'NO') . "\n";
        if (isset($lessons[$first_key]['post'])) {
            echo 'CLASS: ' . get_class($lessons[$first_key]['post']) . "\n";
            echo 'ID: ' . $lessons[$first_key]['post']->ID . "\n";
        }
    }
    echo '</pre>';
} else {
    echo "NO LESSONS<br>\n";
}
?>