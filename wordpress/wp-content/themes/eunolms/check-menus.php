<?php
require_once('wp-load.php');

$menus = wp_get_nav_menus();
foreach ($menus as $menu) {
    echo "ID: {$menu->term_id} | Name: {$menu->name}\n";
    $items = wp_get_nav_menu_items($menu->term_id);
    foreach ($items as $item) {
        echo "  - {$item->title}\n";
    }
}
