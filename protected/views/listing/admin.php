<?php
 /* @var $this ListingController */
 /* @var $model ListingNames */

$this->breadcrumbs=array(
	'Manage Lists',
);

$columns = array(
    'checkbox'    => array( 'header' => '<input type="checkbox" class="checkbox-all">',
        'type'   => 'raw',
        'value'  => function($data) {
            /* @var $data ListingNames */

            return CHtml::checkBox("ListingNames[id][]", false, array("value" => $data->id, 'class' => 'checkbox'));
        },
        'footer' => '<input type="checkbox" class="checkbox-all">',
    ),
    'name'        => array( 'name'  => 'name', 'type'  => 'raw',
        'value' => function($data) {
            /* @var $data ListingNames */

            $name = $data->name;

            if($data->ignored) {
                $name = '<span class="text-error">' . $data->name . '</span>';
            }

            /*
            return $this->widget(
                'booster.widgets.TbEditableField',
                array(
                    'type' => 'text',
                    'model' => $data,
                    'attribute' => 'name', // $model->name will be editable
                    'url' => array('/listing/changeName'), //url for submit data
                    'placement' => 'right',
                    //'htmlOptions' => array( 'class' => (($data->ignored) ? 'text-error' : '')),
                ), true
            );
            */

            return CHtml::link($name, array('/listing/list', 'id' => $data->id));
        }
    ),
    'items_count' => array( 'name'  => 'filter_items_count', 'type'  => 'raw', 'value' => function($data) {
        /* @var $data ListingNames */

        return CHtml::link($data->items_count, array('/listing/list', 'id' => $data->id));
    } ),
    'date_create' => array( 'name'  => 'date_create', 'type'  => 'raw', 'value' => function($data) {
        /* @var $data ListingNames */

        return date('Y-m-d H:i:s', $data->date_create);
    } ),
    'date_update' => array( 'name'  => 'date_update', 'type'  => 'raw', 'value' => function($data) {
        /* @var $data ListingNames */

        return date('Y-m-d H:i:s', $data->date_update);
    } ),
    'ignored'     => array( 'name'  => 'ignored', 'type'  => 'raw', 'value' => function($data) {
        /* @var $data ListingNames */
        return $data->ignored;
    } ),
    'buttons'     => array(
        'class'=>'bootstrap.widgets.TbButtonColumn',
        'template' => '{delete}',
    ),
);

if(WebUser::isAdmin()) {
    $columns['user'] = array( 'name'  => 'filter_user', 'type'  => 'raw',
        'value' => function($data) {
            /* @var $data ListingNames */
            return $data->user->username;
        } );

    $columns = array(
        $columns['checkbox'],
        $columns['user'],
        $columns['name'],
        $columns['items_count'],
        $columns['date_create'],
        $columns['date_update'],
        $columns['ignored'],
        $columns['buttons'],
    );
}

?>

<h3>Manage Lists</h3>




<?php $this->widget('bootstrap.widgets.TbGridView',array(
    'id'=>'listing-names-grid',
    'dataProvider'=>$model->search(),
    //'filter'=>$model,
    'template'=>'{summary} {pager} {items} {pager}',
    'afterAjaxUpdate' => 'setHandlers',
    'columns'=>$columns,
)); ?>

<div id="grid-actions-block">
    <form method="post" action="<?php echo $this->createUrl('doAction'); ?>">
        <div class="span2">
            <?php
                $actions = array(
                    'delete' => 'Delete',
                    'truncate' => 'Erase Items',
                );

                echo CHtml::label('Choose action:', 'actions-list');
                echo CHtml::dropDownList('action', 'delete', $actions, array('class' => 'actions-list'));

                echo '<br/>';

                $this->widget(
                    'booster.widgets.TbButton',
                    array(
                        'label' => 'Action Button',
                        'type'  => 'primary',
                        'buttonType'  => 'submit',
                        'htmlOptions' => array(
                            //'class' => 'span1',
                            'class' => 'do-action'
                        ),
                    )
                );
            ?>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(function() {
        setHandlers();

        $('.do-action').on('click', doActionClick);
    });

    function setHandlers() {
        $('.checkbox').on('click', checkboxClick);
        $('.checkbox-all').on('click', checkAllCheckboxes);
    }

    function checkboxClick() {
        var chbx_count = $('.checkbox').length,
            chbx_count_checked = $('.checkbox').filter(':checked').length;

        if(chbx_count != chbx_count_checked) {
            $('.checkbox-all').attr('checked', false);
        } else {
            $('.checkbox-all').attr('checked', true);
        }

        if(chbx_count_checked > 0) {
            $('#grid-actions-block').slideDown();
        } else {
            $('#grid-actions-block').slideUp();
        }
    }

    function checkAllCheckboxes() {

        if($(this).is(':checked')) {
            $('#grid-actions-block').slideDown();
        } else {
            $('#grid-actions-block').slideUp();
        }

        $('.checkbox').attr('checked', $(this).is(':checked'));
        $('.checkbox-all').attr('checked', $(this).is(':checked'));
    }

    function doActionClick() {
        var $chkbxs  = $('.checkbox').filter(':checked');

        for(var i = 0; i < $chkbxs.length; i++) {
            var value = $chkbxs[i].value;
            var name  = $chkbxs[i].name;
            var field = $('<input type="hidden" name="' + name + '" value="' + value + '">');
            $(this).parents('form').append(field);
        }
    }
</script>
