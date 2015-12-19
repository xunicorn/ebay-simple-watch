<?php
/* @var $this ListingController */
/* @var $list ListingNames */
/* @var $items EbayItem[] */

$this->breadcrumbs=array(
    'List [' . $list->name . ']',
);

?>

<h3>List: [<?php echo $list->name; ?>]</h3>

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

<?php
$this->widget('bootstrap.widgets.TbGridView',array(
    'id'=>'listing-items-grid',
    'dataProvider'=>new CArrayDataProvider($items, array(
        'pagination' => false,
    )),
    'template'=>'{summary} {items}',
    'afterAjaxUpdate' => 'setHandlers',
    'pager' => array(
        'class' => 'booster.widgets.TbPager',
        'displayFirstAndLast' => true,
    ),
    'rowHtmlOptionsExpression' => 'array("data-item-id" => $data->itemId, "data-date-end" => $data->dateOfEnded, "data-date-start" => $data->dateOfAdded, "data-price" => $data->price, "data-bin" => $data->buyItNow)',
    'htmlOptions' => array( 'class' => 'table-hover span12'),
    'columns'=>array(
        array(
            'header' => '<input type="checkbox" class="checkbox-all">',
            'type'   => 'raw',
            'value'  => function($data) {
                /* @var $data EbayItem */

                $details = array(
                    'ebay_id'   => $data->itemId,
                    'image_url' => $data->pictureUrl,
                );

                return CHtml::checkBox("EbayItems[id][]", false, array("data-id" => $data->itemId, 'class' => 'checkbox', 'value' => $data->itemId, 'data-details' => json_encode($details)));
            },
            'footer' => '<input type="checkbox" class="checkbox-all">',
        ),
        array( 'type' => 'raw', 'value' => function($data) {
            /* @var $data EbayItem */
            $img = CHtml::image($data->pictureUrl, $data->title, array('class' => 'item-image')); //

            return CHtml::link($img, $data->itemUrl, array( 'target' => '_blank'));//array('/listing/item', 'id' => $data->itemId)
        }, 'htmlOptions' => array( 'class' => 'item-image')),
        array( 'type' => 'raw', 'value' => function($data) {
            /* @var $data EbayItem */
            $out  = 'Item Title: <span class="text-error">' . $data->title . '</span><br/>';
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
            $out = 'Lots Price: <span class="text-error">$' . number_format((double)$data->price, 2) . '</span><br/>';

            if($data->buyItNow != -1) {
                $out .= 'BuyItNow Price: <span class="text-error">' . number_format($data->buyItNow, 2) . '</span><br/>';
            }

            $out .= 'Payments Methods: <span class="text-error">' . $data->paymentMethods . '</span><br/>';
            $out .= 'Condition: <span class="text-error">' . (empty($data->condition) ? 'Unknown' : $data->condition) . '</span>';

            return $out;
        }),
        array( 'type' => 'raw', 'value' => function($data) {
            /* @var $data EbayItem */
            $out  = 'Lots Shipping: <span class="text-error">' . $data->shipping . '</span><br/>';
            $out .= 'Excluded Ship Locations: <span class="text-error">' . (empty($data->excludeShipLocations) ? '-' : $data->excludeShipLocations) . '</span><br/>';
            $out .= 'Global Shipping: <span class="text-error">' . (empty($data->globalShipping) ? '-' : '+') . '</span>';


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

        $(".delete-from-list-btn").on("click", deleteFromList);
        $(".delete-from-list-ended-btn").on("click", deleteFromListEnded);
        $('.change-list-image-btn').on('click', changeListImage);

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

    function deleteFromList() {

        var items_ids = [];

        var $chkbxs = $(".checkbox").filter(":checked");

        $chkbxs.each(function(indx) {
            items_ids.push($(this).val());
        });

        if(items_ids.length == 0) {
            $.notify("Please select items", "error");
            return;
        }

        var data = { "ListItems": { "items": items_ids.join(" ") } };

        $.post(
            "<?php echo $this->createUrl('/listing/deleteFromList', array('id' => $list->id)); ?>",
            data,
            function(resp) {
                if(resp.success) {
                    $.notify(items_ids.length + " items successfully deleted from list", "success");
                    $("tr").has(".checkbox:checked").fadeOut();
                } else {
                    $.notify("You have not delete items from list", "error");
                }
            },
            "json"
        ).fail(function() {
                $.notify("Occurred some errors in request", "error");
            });

        return false;
    }

    function deleteFromListEnded() {
        $('.checkbox').attr('checked', false);

        var trs = $('tr').has('.time-left-container[data-time-left=0]');

        trs.find('.checkbox').attr('checked', true);

        deleteFromList();

        return false;
    }

    function changeListImage() {
        var $chkbxs = $(".checkbox:checked");

        if($chkbxs.length == 0) {
            $.notify('Choose image for list', 'error');
            return;
        }

        var list_id = '<?php echo $list->id; ?>';
        var details = $chkbxs.filter(':first').data('details');

        var data = { 'List': { 'image_url': details.image_url, 'id': list_id }};

        $.post(
            '<?php echo $this->createUrl('changeImageUrl'); ?>',
            data,
            function(resp) {
                if(resp.success) {
                    $.notify('List image successfully changed', 'success');
                } else {
                    $.notify('Could not change list image', 'error');
                }
            },
            'json'
        ).fail(function() {
                $.notify('Error in request', 'error');
            });

        $chkbxs.attr('checked', false);

        return false;
    }

    function sortTable() {
        var sort_id = $(this).val();

        var $trs = $('#listing-items-grid tbody tr');

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

        $('#listing-items-grid tbody').html($trs);
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
