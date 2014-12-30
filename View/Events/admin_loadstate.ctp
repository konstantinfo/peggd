<?php echo $this->Html->script(array('custom')); ?>
<span> State:</span>
<?php echo $this->Form->input('Venue.state', array('style' => 'width:290px;', 'class' => 'chosen', 'type' => 'select', 'empty' => 'Select State', 'options' => $list_state, 'label' => false, 'div' => false, 'style' => 'width:350px !important;', 'onchange' => 'getdatafromgoogle()')) ?>
<span id="StateIdLoading"></span>