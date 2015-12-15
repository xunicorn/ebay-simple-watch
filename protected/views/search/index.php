<?php
    /* @var $this SearchController */
    /* @var $categories [] */
    /* @var $model SearchRequests */

    echo CHtml::hiddenField('request_id', intval($model->id), array('id' => 'request-id'));
?>

<!-- Start of the request form part	-->
<div class="row-fluid">
    <div class="span12">
        <?php echo CHtml::errorSummary($model, null, null, array( 'class' => 'alert alert-block alert-error')); ?>
        <form method="post"  class="form-horizontal">
            <table  class="table table-striped table-bordered table-condensed" >
                <tr>
                    <td>
                        <div class="span4">
                            <?php //echo CHtml::textField('request[request]', '', array('placeholder' => Yii::t('sniper_ebay', 'Request'))) ?>
                            <?php echo CHtml::activeTelField($model, 'request_name', array('placeholder' => Yii::t('sniper_ebay', 'Request'))) ?>
                            <span class="required">*</span>
                        </div>
                        <?php if(!$model->isNewRecord): ?>
                        <div class="span6 offset2">
                            <div class="span3">
                                Request:
                            </div>
                            <?php

                                $request_types = array(
                                    'new' => 'Create New',
                                    'update' => 'Update Current',
                                );

                                echo CHtml::radioButtonList('request_type', 'update', $request_types,
                                    array( 'template' => '<span class="span4">{beginLabel}{input}{labelTitle}{endLabel}</span>', 'separator' => ' '));
                            ?>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="span4">
                        <?php //echo CHtml::textField('request[keyWord]', '', array('placeholder' => Yii::t('sniper_ebay', 'Keyword'))); ?>
                        <?php echo CHtml::activeTelField($model, 'keyword', array('placeholder' => Yii::t('sniper_ebay', 'Keyword'))) . '&nbsp;<span class="required">*</span>'; ?>

                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="span3">
                            <?php echo CHtml::activeLabelEx($model, 'ebay_global_id'); ?>
                            <?php
                            $global_ids = SearchRequests::model()->getGlobalEbayIds();
                            //print_r($global_ids);
                            echo CHtml::activeDropDownList($model, 'ebay_global_id', $global_ids, array( 'class' => 'ebay-global-id' ));
                            ?>
                        </div>
                        <div class="span3">
                            <?php
                            echo CHtml::activeLabelEx($model, 'ebay_category_id');
                            //$categories = array_merge(array(Yii::t('sniper_ebay', 'Category')), $categories);
                            //echo CHtml::dropDownList('request[category]', 0, $categories);
                            echo CHtml::activeDropDownList($model, 'ebay_category_id', $categories);
                            ?>
                        </div>
                        <div class="span3">
                        <?php echo CHtml::activeLabelEx($model, 'auction_type_id')?>
                        <?php
                            $listing_types = array(
                                'All',
                                'Auction',
                                'BuyItNow',
                                'Classified',
                                'FixedPrice',
                                'StoreInventory',
                            );

                            //$listing_types = array_combine(array_keys($model->getAuctionTypes()), $listing_types);
                            $listing_types = $model->getAuctionTypes();

                            echo CHtml::activeDropDownList($model, 'auction_type_id', $listing_types);
                        ?>
                        </div>
                        <div class="span3">
                            <?php echo CHtml::activeLabelEx($model, 'condition')?>
                            <?php
                                $conditions = SearchRequests::model()->getItemsConditions();
                                echo CHtml::activeDropDownList($model, 'condition', $conditions);
                            ?>
                        </div>

                    </td>

                </tr>
                <tr>
                    <td>
                        <table>
                            <tbody>
                                <tr>
                                    <td>
                                        <?php echo Yii::t('sniper_ebay', 'Min Price'); ?>
                                        <span class="required">*</span>
                                    </td>
                                    <td>
                                        <?php //echo CHtml::textField('request[minPrice]', ''/*, array('class' => 'col-sm-2')*/)?>
                                        <?php echo CHtml::activeTelField($model, 'price_min'/*, array('class' => 'col-sm-2')*/)?>
                                    </td>
                                    <td>
                                        <?php echo Yii::t('sniper_ebay', 'Max Price'); ?>
                                        <span class="required">*</span>
                                    </td>
                                    <td>
                                        <?php //echo CHtml::textField('request[maxPrice]', ''/*, array('class' => 'col-sm-2')*/)?>
                                        <?php echo CHtml::activeTelField($model, 'price_max'/*, array('class' => 'col-sm-2')*/)?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo Yii::t('sniper_ebay', 'Minimum time before auction finishing (min)'); ?>
                        <span class="required">*</span>
                        <?php //echo CHtml::textField('request[endTimeFrom]', '', array('class' => 'col-sm-2')); ?>
                        <?php echo CHtml::activeTextField($model, 'end_time_from', array('class' => 'col-sm-2')); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo Yii::t('sniper_ebay', 'Count of lots'); ?>
                        <span class="required">*</span>
                        <?php //echo CHtml::textField('request[countOfLots]', '', array('class' => 'col-sm-2')); ?>
                        <?php echo CHtml::activeTextField($model, 'lots_count', array('class' => 'col-sm-2')); ?>
                    </td>
                </tr>
                <tr class="<?php echo WebUser::isGuest() ? 'hidden' : ''; ?>">
                    <td>
                        <?php
                            $model->only_new = WebUser::isGuest() ? 0 : $model->only_new;
                            $chb_only_new = CHtml::activeCheckBox($model, 'only_new', array('id' => 'checkbox-only-new'));
                            $text = Yii::t('sniper_ebay', 'Only new results');
                            echo CHtml::label($text. ' ' . $chb_only_new, 'checkbox-only-new');
                        ?>
                    </td>
                </tr>
                <tr class="<?php echo WebUser::isGuest() ? 'hidden' : ''; ?>">
                    <td>
                        <?php
                            $chb_ignored_list = CHtml::activeCheckBox($model, 'ignore_list', array('id' => 'checkbox-ignored-list'));
                            $text = Yii::t('sniper_ebay', 'Use ignore list');

                            $ignore_list = ListingNames::model()->getUserIgnoreList(WebUser::Id());

                            echo CHtml::label($text. ' ' . $chb_ignored_list, 'checkbox-ignored-list');
                            echo CHtml::link('Browse', array('/listing/index', 'id' => $ignore_list->id), array('target' => '_blank'));
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php
                            echo CHtml::submitButton(Yii::t('sniper_ebay', 'Start Search'), array( 'class' => 'btn btn-primary'));
                        ?>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>
<script type="text/javascript">
    $(function() {
        $('.ebay-global-id').on('change', changeCategories);
    });

    function changeCategories() {
        var global_id = $(this).val();
        var request_id = $('#request-id').val();

        var data = { 'NewUrl': { 'action': '/search/index', 'params': { 'id': request_id, 'ebay_global_id': global_id } } };

        $.post(
            '<?php echo $this->createUrl('/site/createUrlAjax'); ?>',
            data,
            function(resp) {
                if(resp.result) {
                    window.location.href = resp.url;
                } else {
                    $.notify('Occurred some problems in processing params', 'error');
                }
            },
            'json'
        ).fail(function() {
                $.notify('Occurred some problems in request', 'error');
            });
    }
</script>
<!-- End of the request form part-->