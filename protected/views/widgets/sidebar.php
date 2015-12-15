<?php
    /* @var $this Controller */

?>
<div class="widget-sidebar">
    <div class="widget-sidebar-inner">


            <?php if(!empty($this->actions_menu)): ?>
                <div class="well">
                    <h4>Actions:</h4>
                    <?php
                        $items = array(
                            array( 'label' => 'Actions', 'itemOptions' => array( 'class' => 'nav-header' )),
                        );

                        $items = array_merge($items, $this->actions_menu);

                        $this->widget(
                            'booster.widgets.TbMenu',
                            array(
                                'type' => 'list',
                                'items' => $this->actions_menu
                            )
                        );
                    ?>
                </div>
                <hr/>
            <?php endif; ?>

        <?php if(!WebUser::isGuest()): ?>
            <?php if($this->add_to_list_btn): ?>
                <?php //echo CHtml::button('Add To List', array('class' => 'btn')); ?>
                <div class="well">
                    <h4>Add To List:</h4>
                    <div class="user-listings-list-container">
                        <div class="user-listings-list-items">
                            <?php
                                $listing_names = array();
                                //$lists = ListingNames::model()->getUserLists(WebUser::Id());
                                foreach($this->listings as $_lst) {
                                    if($_lst->ignored) {
                                        $_name = "<span class='text-error'>" . $_lst->name . "</span>";
                                    } else {
                                        $_name = $_lst->name;
                                    }

                                    $listing_names[$_lst->id] = $_name;
                                }

                                echo CHtml::checkBoxList('user_lists', false, $listing_names,
                                    array( 'template' => '{beginLabel}{input}&nbsp;{labelTitle}{endLabel}', 'separator' => ' ', 'class' => 'user-list-checkbox'));
                            ?>
                        </div>

                        <div class="user-listings-list-add-new">
                           <div class="user-listings-list-add-btn">
                               <?php echo CHtml::button('Create new', array('class' => 'btn btn-link '))?>
                           </div>
                            <div class="user-listings-list-add-form">
                                <div class="input-append">
                                    <?php echo CHtml::textField('new_user_list', '', array( 'placeholder' => 'New List', 'class' => 'span7')); ?>
                                    <?php echo CHtml::button('Create', array('class' => 'btn btn-primary btn-mini ')); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr/>
            <?php endif; ?>
        <?php endif; ?>

            <?php if(!empty($this->requests)): ?>
                <div class="well">
                    <h4>Requests:</h4>
                    <?php
                        $items = array();

                        foreach($this->requests as $_req) {
                            $items[] = array(
                                'label' => $_req->request_name . ' (' . $_req->keyword . ')',
                                'url'   => array('/search/listing', 'id' => $_req->id),
                            );
                        }

                        $this->widget(
                            'booster.widgets.TbMenu',
                            array(
                                'type' => 'list',
                                'items' => $items
                            )
                        );
                    ?>
                </div>
            <?php endif; ?>

        <?php if(!WebUser::isGuest()): ?>
            <?php if(!empty($this->listings)): ?>
                <div class="well">
                    <h4>Lists:</h4>
                    <?php
                    $items = array();

                    foreach($this->listings as $_lst) {
                        $items[] = array(
                            'label' => $_lst->name . ' (' . $_lst->items_count . ')',
                            'url'   => array('/listing/list', 'id' => $_lst->id),
                            'linkOptions' => array('class' => ($_lst->ignored ? 'text-error' : ''))
                        );
                    }

                    $this->widget(
                        'booster.widgets.TbMenu',
                        array(
                            'type' => 'list',
                            'items' => $items,
                            'htmlOptions' => array('id' => 'sidebar-user-lists'),
                        )
                    );
                    ?>
                </div>
            <?php endif;?>
        <?php endif; ?>
    </div>
    <div class="scroll-to-top-btn well" title="scroll to top">
        <?php echo CHtml::image($this->assets['img']['arrow-top'], 'Scroll to top'); ?>
    </div>
</div>

<script type="text/javascript">
    $(function() {
        $(window).on('scroll', toggleScrollToTopBtnVisibility);

        $('.scroll-to-top-btn').on('click', scrollToTop);

        $(".user-listings-list-add-btn [type=button]").on("click", showCrateListForm);
        $(".user-listings-list-add-form [type=button]").on("click", createUserList);
        $(".user-list-checkbox").on("click", updateUserList);
    });

    function toggleScrollToTopBtnVisibility() {
        var $elm = $('.widget-sidebar-inner');

        var $scrol_to_top_btn = $('.scroll-to-top-btn');

        if(isScrolledIntoView($elm)) {
            //$.notify('element is on page', 'info');

            if($scrol_to_top_btn.is(':visible')) {
                $scrol_to_top_btn.fadeOut();
            }
        } else {
            //$.notify('element is not on page', 'warning');

            if(!$scrol_to_top_btn.is(':visible')) {
                $scrol_to_top_btn.fadeIn();
            }
        }
    }

    function scrollToTop() {
        $("html, body").animate({ scrollTop: 0 }, "slow");
    }

    function isScrolledIntoView($elem) {
        var $window = $(window);

        var docViewTop = $window.scrollTop();

        var elemBottom = $elem.offset().top + $elem.height();

        return ((elemBottom >= docViewTop)); // && (elemTop >= docViewTop)
    }

    function showCrateListForm() {
        $(".user-listings-list-add-btn").slideUp();
        $(".user-listings-list-add-form").slideDown();
    }

    function updateUserList() {
        var $obj = $(this);
        var list_id = $(this).val();

        var items_attrs = [];

        var $chkbxs = $(".checkbox").filter(":checked");

        $chkbxs.each(function(indx) {
            var _details = $(this).data("details");
            items_attrs.push(_details);
        });

        if(items_attrs.length == 0) {
            $.notify("Please select items", "error");
            $obj.attr("checked", false);
            return;
        }

        var data = { "ListingNames": { "list_id": list_id, "items": JSON.stringify(items_attrs) } };

        $.post(
            "<?php echo $this->createUrl('/listing/updateFromUserList'); ?>",
            data,
            function(resp) {
                if(resp.success) {
                    $.notify($chkbxs.length + " items successfully added to list", "success");
                } else {
                    $.notify("You have not updated the list", "error");
                    $obj.attr("checked", false);
                }
            },
            "json"
        ).fail(function() {
                $.notify("Occurred some errors in request", "error");
                $obj.attr("checked", false);
            });
    }

    function createUserList() {
        var list_name = $(".user-listings-list-add-form [name=new_user_list]").val();

        if(list_name == "") {
            $.notify("List name could not be empty", "error");
            return;
        }

        var items_attrs = [];

        var $chkbxs = $(".checkbox").filter(":checked");

        $chkbxs.each(function(indx) {
            var _details = $(this).data("details");
            items_attrs.push(_details);
        });

        var data = { "ListingNames": { "name": list_name, "items": JSON.stringify(items_attrs) } };

        $.post(
            "<?php echo $this->createUrl('/listing/createFromUserList'); ?>",
            data,
            function(resp) {
                if(resp.success) {

                    $.notify($chkbxs.length + " items successfully added to new list", "success");

                    var $label = $("<label/>");
                    var $chbx  = $("<input type=\"checkbox\" />");
                    $chbx.attr("name", "user_lists[]");
                    $chbx.val(resp.list_id);
                    $chbx.on("click", updateUserList);

                    $label.append($chbx).append("&nbsp;" + list_name);

                    $("#user_lists").append($label);

                    var $link = $('<a/>').attr('href', resp.url).html(list_name + " (" + items_attrs.length + ")");
                    var $li   = $('<li/>').append($link);

                    $('#sidebar-user-lists').append($li);

                    $(".user-listings-list-add-form").slideUp();
                    $(".user-listings-list-add-btn").slideDown();
                } else {
                    $.notify("You have not created the list", "error");
                }
            },
            "json"
        ).fail(function() {
                $.notify("Occurred some errors in request", "error");
            });
    }

</script>