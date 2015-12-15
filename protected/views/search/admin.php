<?php
$this->breadcrumbs=array(
	'Search Requests'=>array('index'),
	'Manage',
);

$this->menu=array(
array('label'=>'List SearchRequests','url'=>array('index')),
array('label'=>'Create SearchRequests','url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
$('.search-form').toggle();
return false;
});
$('.search-form form').submit(function(){
$.fn.yiiGridView.update('search-requests-grid', {
data: $(this).serialize()
});
return false;
});
");
?>

<h1>Manage Search Requests</h1>

<p>
	You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>
		&lt;&gt;</b>
	or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>

<?php echo CHtml::link('Advanced Search','#',array('class'=>'search-button btn')); ?>
<div class="search-form" style="display:none">
	<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('bootstrap.widgets.TbGridView',array(
'id'=>'search-requests-grid',
'dataProvider'=>$model->search(),
'filter'=>$model,
'columns'=>array(
		'id',
		'user_id',
		'date_update',
		'request_name',
		'end_time_from',
		'price_min',
		/*
		'price_max',
		'listing_type',
		'condition',
		'ebay_category_id',
		'keyword',
		'lots_count',
		'ignore_list',
		*/
array(
'class'=>'bootstrap.widgets.TbButtonColumn',
),
),
)); ?>
