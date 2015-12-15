<?php
$this->breadcrumbs=array(
	'Search Requests'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Update',
);

	$this->menu=array(
	array('label'=>'List SearchRequests','url'=>array('index')),
	array('label'=>'Create SearchRequests','url'=>array('create')),
	array('label'=>'View SearchRequests','url'=>array('view','id'=>$model->id)),
	array('label'=>'Manage SearchRequests','url'=>array('admin')),
	);
	?>

	<h1>Update SearchRequests <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>