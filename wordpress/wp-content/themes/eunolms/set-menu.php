<?php
require_once('wp-load.php');
$locations = get_theme_mod('nav_menu_locations');
if (!is_array($locations)) {
    $locations = array();
}
$locations['primary'] = 4;
set_theme_mod('nav_menu_locations', $locations);
echo "Menu assigned successfully.\n";
