<?php
/* @var $this ListingController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Lists',
);

?>

<?php $this->widget('zii.widgets.CListView', array(
    'dataProvider'=>$dataProvider,
    'itemView'=>'_view',
)); ?>

<script type="text/javascript">
    $(function() {
        $('.list-delete-icon').on('click', deleteList);
        $('.list-view-item .rename-link a').on('click', renameList);
    });

    function deleteList() {
        if(!confirm('Are you sure want to delete list?')) {
            //$.notify("You are not sure", "info");
            return false;
        }

        var $obj = $(this);

        var url_part = '<?php echo $this->createUrl('delete'); ?>';

        var list_id  = $obj.data('listId');

        var url = url_part + '&id=' + list_id;

        $.post(
            url,
            {},
            function(resp) {
                $obj.parents('.list-view-item').fadeOut();
                $.notify('You successfully deleted the list', 'success');
            }
        );
    }

    function renameList() {

        var $link = $(this).parents('h4').find('.list-view-item-link');

        $link.editable({
            success: function(response, newValue) {
                $.notify('You successfully changed list\'s name', 'info');

                $link.text(newValue);
                $link.editable('destroy');
            }
        });

        $link.trigger('click');

        return false;
    }
</script>