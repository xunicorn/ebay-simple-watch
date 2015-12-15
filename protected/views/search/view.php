<?php
$this->breadcrumbs=array(
	'Search Requests'=>array('index'),
	$model->id,
);

$this->menu=array(
array('label'=>'List SearchRequests','url'=>array('index')),
array('label'=>'Create SearchRequests','url'=>array('create')),
array('label'=>'Update SearchRequests','url'=>array('update','id'=>$model->id)),
array('label'=>'Delete SearchRequests','url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
array('label'=>'Manage SearchRequests','url'=>array('admin')),
);
?>

<h1>View SearchRequests #<?php echo $model->id; ?></h1>

<?php $this->widget('bootstrap.widgets.TbDetailView',array(
'data'=>$model,
'attributes'=>array(
		'id',
		'user_id',
		'date_update',
		'request_name',
		'end_time_from',
		'price_min',
		'price_max',
		'listing_type',
		'condition',
		'ebay_category_id',
		'keyword',
		'lots_count',
		'ignore_list',
),
)); ?>
