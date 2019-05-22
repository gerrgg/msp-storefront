<?php

defined( 'ABSPATH' ) || exit;

function loop_columns() {
    return 5; // 5 products per row
}
add_filter('loop_shop_columns', 'loop_columns', 999);