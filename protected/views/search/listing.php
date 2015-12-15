<?php
    /* @var $this SearchController */
    /* @var $items EbayItem[] */
    /* @var $request SearchRequests */

    $this->actions_menu = array(
        array( 'label' => 'Create request', 'url' => array('/search/index')),
        array( 'label' => 'Update request', 'url' => array('/search/index', 'id' => $request->id)),
    );

    $listings = ListingNames::model()->getUserLists(WebUser::Id());
?>

<div class="row-fluid">
    <div class="span3 pull-right">
        <?php
            $data = array(
                '',
                'id'         => 'Sort by id',
                'id_desc'    => 'Sort by id DESC',
                'price'      => 'Sort by price',
                'price_desc' => 'Sort by price DESC',
                'bin'        => 'Sort by BuyItNow',
                'bin_desc'   => 'Sort by BuyItNow DESC',
                'date_start'      => 'Sort by start date',
                'date_start_desc' => 'Sort by start date DESC',
                'date_end'        => 'Sort by end date',
                'date_end_desc'   => 'Sort by end date DESC',
            );

            echo CHtml::label('Sort', 'sort-order');
            echo CHtml::dropDownList('sortOrder', 0, $data, array('id' => 'sort-order-list'));

        ?>
    </div>
</div>

<?php $this->widget('bootstrap.widgets.TbGridView',array(
    'id'=>'search-results-grid',
    'dataProvider'=>new CArrayDataProvider($items, array(
        'pagination' => false,
    )),
    'template'=>'{summary} {pager} {items} {pager}',
    'afterAjaxUpdate' => 'setHandlers',
    'pager' => array(
        'class' => 'booster.widgets.TbPager',
        'displayFirstAndLast' => true,
    ),
    'htmlOptions' => array( 'class' => 'table-hover'),
    'rowHtmlOptionsExpression' => 'array("data-item-id" => $data->itemId, "data-date-end" => $data->dateOfEnded, "data-date-start" => $data->dateOfAdded, "data-price" => $data->price, "data-bin" => $data->buyItNow)',
    'columns'=>array(
        array(
            'header' => '<input type="checkbox" class="checkbox-all">',
            'type'   => 'raw',
            'value'  => function($data) {
                /* @var $data EbayItem */

                $details = array(
                    'ebay_id'       => $data->itemId,
                    'title'         => $data->title,
                    'url_picture'   => $data->pictureUrl,
                    'url_item'      => $data->itemUrl,
                    'buy_it_now'    => intval($data->buyItNow != -1),
                    'date_start'    => $data->dateOfAdded,
                    'date_end'      => $data->dateOfEnded,
                    'currency'      => $data->currency,
                );

                return CHtml::checkBox("EbayItems[id][]", false, array("data-id" => $data->itemId, 'class' => 'checkbox', 'value' => $data->itemId, 'data-details' => json_encode($details)));
            },
            'footer' => '<input type="checkbox" class="checkbox-all">',
        ),
        array( 'type' => 'raw', 'value' => function($data) {
            /* @var $data EbayItem */

            $img = CHtml::image($data->pictureUrl, $data->title, array( 'class' => 'item-image'));

            return CHtml::link($img, $data->itemUrl, array('target' => '_blank'));
        }, 'htmlOptions' => array( 'class' => 'item-image')),
        array( 'type' => 'raw', 'value' => function($data) {
            /* @var $data EbayItem */
            $out  = 'Item title: <span class="text-error">' . $data->title . '</span><br/>';
            $out .= 'Item ID: <span class="text-error">' . $data->itemId . '</span><br/>';

            return $out;
        }),
        array( 'type' => 'raw', 'value' => function($data) {
            /* @var $data EbayItem */

            $time_left_container = array(
                '<span class="time-left-days"></span>D',
                '<span class="time-left-hours"></span>:<span class="time-left-minutes"></span>:<span class="time-left-seconds"></span>',
            );

            $time_left = $data->dateOfEnded - time();
            ($time_left < 0) && $time_left = 0;

            $out = array(
                'Time Left: ' . CHtml::tag('div',
                    array(
                        'class' => 'text-error time-left-container no-wrap',
                        'data-ebay-id'    => $data->itemId,
                        'data-time-left'  => ($time_left)
                    ),
                    implode('&nbsp', $time_left_container)),
                'End Time: '  . CHtml::tag('div', array('class' => 'text-error no-wrap'), date('Y-m-d H:i:s', $data->dateOfEnded)),
                'Bid Count: ' . CHtml::tag('span', array('class' => 'text-error'), $data->bidCount)
            );

            return implode('<br/>', $out);
        }),
        array( 'type' => 'raw', 'value' => function($data) {
            /* @var $data EbayItem */
            $out = 'Lots price: <span class="text-error">$' . number_format((double)$data->price, 2) . '</span><br/>';

            if($data->buyItNow != -1) {
                $out .= 'BuyItNow price: <span class="text-error">' . number_format($data->buyItNow, 2) . '</span><br/>';
            }

            $out .= 'Auction Type: <span class="text-error">' . $data->auctionType . '</span>';

            return $out;
        }),
        array( 'type' => 'raw', 'value' => function($data) {
            /* @var $data EbayItem */
            $out = 'Lots shipping: <span class="text-error">' . $data->shipping . '</span>';

            return $out;
        }),
        //array(
        //    'class'=>'bootstrap.widgets.TbButtonColumn',
        //),
    ),
)); ?>


<script type="text/javascript">
    var intervalIDs = {};
    var stamps = {};

    var sort_attribute;

    $(function() {
        setHandlers();

        $('#sort-order-list').on('click', sortTable);
    });

    function setHandlers() {
        $('.checkbox').on('click', checkboxClick);
        $('.checkbox-all').on('click', checkAllCheckboxes);

        setTimerLeft();
    }

    function checkboxClick() {
        var chbx_count = $('.checkbox').length,
            chbx_count_checked = $('.checkbox').filter(':checked').length;

        if(chbx_count != chbx_count_checked) {
            $('.checkbox-all').attr('checked', false);
        } else {
            $('.checkbox-all').attr('checked', true);
        }
    }

    function checkAllCheckboxes() {
        $('.checkbox').attr('checked', $(this).is(':checked'));
        $('.checkbox-all').attr('checked', $(this).is(':checked'));
    }


    function setTimerLeft() {
        $('.time-left-container').each(function(indx) {
            var $obj   = $(this);
            var stamp  = $obj.data('timeLeft');
            var ebayId = $obj.data('ebayId');
            var stamp_start = $obj.data('timeStart');
            var stamp_end   = $obj.data('timeEnd');

            stamps[ebayId] = stamp;

            if(stamp <= 0) {
                $obj.text('Ended');
                return;
            }

            intervalIDs[ebayId] = setInterval(function() {
                var _stamp = stamps[ebayId]--;
                parseTime(_stamp, $obj);
                if(_stamp <= 0) {
                    $obj.text('Ended');
                    clearInterval(intervalIDs[ebayId]);
                }
            }, 1000);

        });
    }

    function parseTime(timestamp, $obj){
        if (timestamp < 0) timestamp = 0;

        var day = Math.floor( (timestamp/60/60) / 24);
        var hour = Math.floor(timestamp/60/60);
        var mins = Math.floor((timestamp - hour*60*60)/60);
        var secs = Math.floor(timestamp - hour*60*60 - mins*60);
        var left_hour = Math.floor( (timestamp - day*24*60*60) / 60 / 60 );

        $obj.find('.time-left-days').text(day);

        if(String(left_hour).length > 1)
            $obj.find('.time-left-hours').text(left_hour);
        else
            $obj.find('.time-left-hours').text("0" + left_hour);
        if(String(mins).length > 1)
            $obj.find('.time-left-minutes').text(mins);
        else
            $obj.find('.time-left-minutes').text("0" + mins);
        if(String(secs).length > 1)
            $obj.find('.time-left-seconds').text(secs);
        else
            $obj.find('.time-left-seconds').text("0" + secs);

    }

    function sortTable() {

        var sort_id = $(this).val();

        var $trs = $('#search-results-grid tbody tr');

        switch(sort_id) {
            case 'id':
            case 'id_desc': sort_attribute = 'itemId'; break;

            case 'price':
            case 'price_desc': sort_attribute = 'price'; break;

            case 'bin':
            case 'bin_desc': sort_attribute = 'bin'; break;

            case 'date_start':
            case 'date_start_desc': sort_attribute = 'dateStart'; break;

            case 'date_end':
            case 'date_end_desc': sort_attribute = 'dateEnd'; break;

            default: break;
        }


        if(/_desc/i.test(sort_id)) {
            $trs.sort(_sortItemsDESC);
        } else {
            $trs.sort(_sortItems);
        }

        $('#search-results-grid tbody').html($trs);
    }

    function _sortItems(a, b) {
        if(sort_attribute == '' || sort_attribute === undefined) {
            return false;
        }

        var val_a = $(a).data(sort_attribute);
        var val_b = $(b).data(sort_attribute);

        return ((val_a < val_b) ? -1 : ((val_a > val_b) ? 1 : 0));
    }
    function _sortItemsDESC(a, b) {
        if(sort_attribute == '' || sort_attribute === undefined) {
            return false;
        }

        var val_a = $(a).data(sort_attribute);
        var val_b = $(b).data(sort_attribute);

        return ((val_a > val_b) ? -1 : ((val_a < val_b) ? 1 : 0));
    }


</script>

