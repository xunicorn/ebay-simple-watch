<?php
/* @var $this SiteController */
/* @var $error array */

$this->pageTitle=Yii::app()->name . ' - Error';
$this->breadcrumbs=array(
	'Error',
);
?>

<h2>Error <?php echo $code; ?></h2>

<div class="error">
    <p>
        <?php echo CHtml::encode($message); ?>

        <?php if(YII_DEBUG): ?>

        | line: <?php echo $line; ?> | file: <?php echo $file;?>
    </p>
    <?php if(!empty($source)): ?>
        <p>
        <h5>Source:</h5>
        <pre>
<?php echo $source; ?>
                </pre>
        </p>
    <?php endif; ?>
    <p>
    <h5>Trace:</h5>
                <pre>
<?php echo $trace; ?>
                </pre>

    <?php endif; ?>
    </p>

</div>