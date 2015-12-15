<?php
    /* @var $this SiteController */
    ?>

<h3>Forgot Password</h3>

<div class="form">
    <form method="post">
        <div class="row">
            <?php
                echo CHtml::label('Enter your username / email', 'user-mail');
                echo CHtml::textField('credentials', null, array('id' => 'user-mail'));
            ?>
        </div>
        <div class="row">
            <?php echo CHtml::submitButton('Send email', array('class' => 'btn'))?>
        </div>
    </form>
</div>