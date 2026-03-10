<?php
require_once(dirname(__FILE__) . '/../../../wp-load.php');
$conditions = get_option('elementor_pro_theme_builder_conditions', []); 
print_r($conditions);
?>