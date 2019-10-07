<?php
$SITENAME = get_bloginfo('url');
// TODO: check if array

get_header();
msp_get_departments_silder();
?>
<div class="row">
    <div class="col-12 col-lg-6">
        <a href="<?php $SITENAME ?>/product-category/fall-protection/">
            <img class="img-thumbnail" src="<?php echo $SITENAME ?>/wp-content/uploads/2019/09/fall-protection.jpg"/>
        </a>
    </div>
    <div class="col-12 col-lg-6">
        <a href="<?php $SITENAME ?>/product/kcj-1-safety-cutter-klever-kutter/">
            <img class="img-thumbnail" src="<?php echo $SITENAME ?>/wp-content/uploads/2019/09/kcj-1-w-cardboard.jpg"/>
        </a>
    </div>
</div>
<?php
msp_get_random_slider();
?>
<div class="row">
    <div class="col-12 col-lg-6">
        <a href="<?php $SITENAME ?>/product/ps-doors-ladder-safety-gate/">
            <img class="img-thumbnail" src="<?php echo $SITENAME ?>/wp-content/uploads/2019/09/lsg.jpg"/>
        </a>
    </div>
    <div class="col-12 col-lg-6">
        <a href="<?php $SITENAME ?>/product-category/machine-shields/">
            <img class="img-thumbnail" src="<?php echo $SITENAME ?>/wp-content/uploads/2019/09/machine-shields.jpg"/>
        </a>
    </div>
</div>
<?php
msp_get_random_slider();
msp_get_featured_products_silder();
msp_get_customer_service_info();
get_footer(); 
?>