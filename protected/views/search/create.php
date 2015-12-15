<?php
$this->breadcrumbs=array(
	'Search Requests'=>array('index'),
	'Create',
);

$this->menu=array(
array('label'=>'List SearchRequests','url'=>array('index')),
array('label'=>'Manage SearchRequests','url'=>array('admin')),
);
?>

<h1>Create SearchRequests</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>