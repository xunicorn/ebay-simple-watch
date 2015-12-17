<?php
    /* @var $this UsersController */
    /* @var $model Users */

$this->breadcrumbs=array(
	'Users'
);

$this->actions_menu = array(
    array( 'label' => 'Create user', 'url' => array('/users/create') ),
);

$provider = $model->search();

?>

<h3>Manage Users</h3>



<?php $this->widget('bootstrap.widgets.TbGridView',array(
    'id'=>'users-grid',
    'dataProvider'=>$provider,
    'filter'=>$model,
    'columns'=>array(
        'username',
        'email',
        array(
            'name'  => 'date_last_visit',
            'type'  => 'raw',
            'value' => function($data) {
                /* @var $data Users */
                if($data->date_last_visit == 0) {
                    return '-';
                }

                return date('Y/m/d H:i:s', $data->date_last_visit);
            }
        ),
        array(
            'name'  => 'email_verified',
            'type'  => 'raw',
            'value' => function($data) {
                /* @var $data Users */

                if($data->email_verified) {
                    return '<span class="text-success">Yes</span>';
                } else {
                    return '<span class="text-error">No</span>';
                }
            },
            'filter' => array(0 => 'no', 1 => 'yes'),
        ),
        array(
            'class'=>'bootstrap.widgets.TbButtonColumn',
            'template' => '{verify}{update}{delete}',
            'buttons'  => array(
                'verify' => array(
                    'label' => 'verify user',
                    'icon'  => 'ok-sign',
                    'url'   => 'array("verifyUser", "id" => $data->id)',
                    'visible' => '!$data->email_verified',
                ),
                'delete' => array(
                    'visible' => '$data->id!=WebUser::ADMIN_ID',
                ),
            ),
        ),
    ),
)); ?>
