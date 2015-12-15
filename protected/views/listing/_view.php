<?php
/* @var $this ListingController */
/* @var $data ListingNames */
?>

<div class="row-fluid list-view-item well">
    <div class="span3 list-view-image">
        <?php
            $image_src = $this->assets['img']['no-image'];

            if(count($data->items) > 0) {
                $_item = $data->items[0];

                $image_src = $_item->url_picture;
            }

            $img = CHtml::image($image_src, $data->name, array('class' => 'item-image'));

            echo CHtml::link($img, array('/listing/list', 'id' => $data->id));
        ?>
    </div>
    <div class="span9 list-view-description">
        <?php if(!$data->ignored): ?>
            <div class="pull-right">
                <?php echo CHtml::image($this->assets['img']['delete'], 'Delete list', array('class' => 'list-delete-icon', 'title' => 'Delete List', 'data-list-id' => $data->id)); ?>
            </div>
        <?php endif; ?>

        <?php if(WebUser::isAdmin()): ?>
            <div class="user-info">
                User: <?php echo $data->user->username; ?>
            </div>
        <?php endif; ?>

        <h4>
            <?php
                echo CHtml::link(
                    $data->name,
                    array('/listing/list', 'id' => $data->id),
                    array(
                        'class'     => ($data->ignored ? 'text-error' : 'list-view-item-link'),
                        'data-type' => 'text',
                        'data-pk'   => $data->id,
                        'data-url'  => $this->createUrl('changeName'),
                        'data-name' => 'name',
                    ));
            ?>
            <?php if(!$data->ignored): ?>
                <small class="rename-link">
                    <?php echo CHtml::link('Rename', '#'); ?>
                </small>
            <?php endif; ?>
        </h4>

        <div>Items count: <?php echo $data->items_count; ?></div>
        <div>Create date: <?php echo date('Y-m-d H:i:s', $data->date_create); ?></div>
        <div>Update date: <?php echo date('Y-m-d H:i:s', $data->date_update); ?></div>
        <?php /*if($data->ignored): ?>
            <h5 class="text-error">Ignored</h5>
        <?php endif;*/ ?>
    </div>
</div>