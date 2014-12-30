<div class="MainWraper">
    <?php echo $this->element("web-header"); ?>
    <?php echo $this->element("event-categories"); ?>

    <div  class="midcontent" style="min-height:700px!important;">
        <ul id="eventcontentajax" class="eventlist">
            <input type="hidden" name="total_groups" id="total_groups" data-srch="<?php echo $search; ?>" value="<?php echo isset($total_groups) ? $total_groups : '1'; ?>" /> 
        </ul>
    </div>
    <div class="loadmore">
        <input type="button" style="display:none;" id="loadbt" class="loadmore_button" value="Load More" name="submit">
        <input type="button" style="display:none;" id="noMoreLoadbt" class="loadmore_button" value="No More Record Found" name="submit">
    </div>
    <div class="animation_image" style="display:none;text-align:center; clear:both;" ><?php echo $this->Html->image('../frontend/images/load.gif'); ?></div>
    <div class="clear"></div>
</div>



<?php echo $this->element('web-footer'); ?>
</div>
<?php $categoryId = '0'; ?>