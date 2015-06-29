<?php // no direct access
defined('_JEXEC') or die('Restricted access');

$doc = JFactory::getDocument();
$doc->addScript('/modules/mod_photos/assets/js/slimbox2.js');
$doc->addStyleSheet('/modules/mod_photos/assets/css/slimbox.css');
$doc->addStyleSheet('/modules/mod_photos/assets/css/mod_photos.css');

$sort = $params->get('sort', 0);

switch ($sort){
    case 1 : $photos = array_reverse($photos);
}

$main_photo_index = $params->get('main_photo', 1) - 1;
if ($params->get('gallery_type', 0) == 1 && isset($photos[$main_photo_index]))
{
    $main_photo = basename($photos[$main_photo_index]);
    $main_photo_img = str_replace('-mid.jpg', '-min.jpg', $main_photo);
    unset($photos[$main_photo_index]);
}
?>

<div class="photos">


    <?php if (!$params->get('gallery_type', 0)) : ?>
        <ul class="list-previews unstyled">
            <?php foreach ($photos as $key => $photo) : ?>
                <?php
                $image_name = basename($photo);
                $min_img = str_replace('-mid.jpg', '-min.jpg', $image_name);
                $base = $doc->getBase();
                ?>
                <li><a rel="lightbox-gallery" title="<?php echo $params->get('title_'.($key+1)) ?>" href="<?php echo $base; ?>images/<?=$params->get('path')?>/<?php echo $image_name?>" class="thumb"><img src="<?php echo $base; ?>images/<?=$params->get('path')?>/min/<?=$min_img?>" /></a></li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <div class="main-photo">
            <a rel="lightbox-gallery" title="<?php echo $params->get('title_'.($main_photo_index+1)) ?>" href="<?php echo $base; ?>images/<?=$params->get('path')?>/<?=$main_photo?>" class="thumb"><img src="<?php echo $base; ?>images/<?=$params->get('path')?>/min/<?=$main_photo_img?>" /></a>
            <div class="photos-invisible">
                <? foreach ($photos as $key => $photo) : $image_name = basename($photo); ?>
                    <a rel="lightbox-gallery" title="<?php echo $params->get('title_'.($key+1)) ?>" href="<?php echo $base; ?>images/<?=$params->get('path')?>/<?=$image_name?>"></a>
                <? endforeach; ?>
            </div>
            <?php if ($header = $params->get('header', '')) : ?>
                <span class="main-photo-title"><?php echo $header ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>