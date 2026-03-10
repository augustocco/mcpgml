<?php
require_once(dirname(__FILE__) . '/../../../wp-load.php');
echo 'Active Theme: ' . LearnDash_Theme_Register::get_active_theme_key() . "\n";
echo 'Focus Mode Enabled globally: ' . (LearnDash_Settings_Section::get_section_setting('LearnDash_Settings_Theme_LD30', 'focus_mode_enabled') == 'yes' ? 'YES' : 'NO') . "\n";

$course_id = 18;
echo "Focus Mode for Course $course_id: " . (learndash_get_setting($course_id, 'course_disable_focus_mode') === 'on' ? 'DISABLED' : 'ENABLED') . "\n";
?>