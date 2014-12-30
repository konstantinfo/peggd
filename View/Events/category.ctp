<div class="MainWraper">
    <?php echo $this->element("web-header"); ?>

    <?php echo $this->element("event-categories"); ?>

    <div id="" class="midcontent" style="min-height: 500px!important;">
        <ul id="eventcontentajax" class="eventlist">

            <input type="hidden" name="total_groups" id="total_groups" value="<?php echo isset($total_groups) ? $total_groups : '0'; ?>" />
        </ul>
    </div>
    <div class="loadmore">

        <input type="button" style="display:none;" id="noMoreLoadbt" class="loadmore_button" value="No More Record Found" name="submit">
    </div>
    <div class="animation_image" style="display:none;text-align:center;clear:both;" ><?php echo $this->Html->image('../frontend/images/load.gif'); ?></div>

    <div class="clear"></div>
</div>
<div class="load-container load8" ><div class="loader">Loading...</div></div>

<?php echo $this->element('web-footer'); ?>
</div>
<?php echo $this->html->scriptBlock("jQuery(function(){ myClass.updatelocation('" . json_encode($this->Session->read('locationData')) . "','" . $categoryId . "'); })", array('inline' => true)); ?>