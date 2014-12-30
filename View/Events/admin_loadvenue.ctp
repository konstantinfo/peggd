<?php echo $this->Html->script(array('custom')); ?>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&libraries=places&language=en-AU"></script>
<?php
$defaultCSS = array();
if (isset($includeCSS) && $includeCSS != '' && is_array($includeCSS)) {
    $defaultCSS = array_merge($defaultCSS, $includeCSS);
}
echo $this->Html->css($defaultCSS);

$defaultJS = array();
if (isset($includeJS) && $includeJS != '' && is_array($includeJS)) {
    $defaultJS = array_merge($defaultJS, $includeJS);
}
echo $this->Html->script($defaultJS);
?>



<label for="textField">
    <span> Venue Name: <font color="red">*</font></span>
    <?php echo $this->Form->input('Venue.name', array('class' => 'textbox', 'div' => false, 'label' => false)); ?>
</label>

<label for="textField">
    <span> Address: <font color="red">*</font></span>
    <?php echo $this->Form->input('Venue.address', array('class' => 'textbox', 'div' => false, 'label' => false)); ?>
</label>

<label for="textField">
    <span> Extended Address: </span>
    <?php echo $this->Form->input('Venue.extended_address', array('class' => 'textbox', 'div' => false, 'label' => false)); ?>
</label>

<label for="textField">
    <span> Country: <font color="red">*</font></span>
    <?php echo $this->Form->input('Venue.country_id', array('style' => 'width:290px;', 'class' => 'chosen', 'type' => 'select', 'empty' => 'Select Country', 'options' => $list_country, 'label' => false, 'div' => false, 'style' => 'width:350px !important;')) ?>
</label>

<label for="textField" id="loadstatedata">
    <span> State: <font color="red">*</font></span>
    <?php echo $this->Form->input('Venue.state', array('style' => 'width:290px;', 'class' => 'chosen', 'type' => 'select', 'empty' => 'Select State', 'options' => $state_list, 'label' => false, 'div' => false, 'style' => 'width:350px !important;', 'onchange' => 'getdatafromgoogle()')) ?>
    <span id="StateIdLoading"></span>
</label>

<label for="textField">
    <span> City: <font color="red">*</font></span>
    <?php echo $this->Form->input('Venue.city', array('class' => 'textbox', 'div' => false, 'label' => false, 'onblur' => 'getdatafromgoogle()')); ?>
</label>

<label for="textField">
    <span> Location: <font color="red">*</font></span>
    <?php echo $this->Form->input('Venue.location', array('class' => 'textbox', 'div' => false, 'label' => false, 'id' => 'address', 'onblur' => 'getdatafromgoogle()')); ?>
    Ex. :- 10 William Street, Double Bay, New South Wales, Australia
</label>
<label for="textArea" >
    <span> </span>
    <div id="map" ></div>
</label>

<label for="textField">
    <span> Longitude: <font color="red">*</font></span>
    <?php echo $this->Form->input('Venue.longitude', array('class' => 'textbox', 'div' => false, 'label' => false, 'id' => 'long')); ?>
</label>

<label for="textField">
    <span> Latitude: <font color="red">*</font></span>
    <?php echo $this->Form->input('Venue.latitude', array('class' => 'textbox', 'div' => false, 'label' => false, 'id' => 'lat')); ?>
</label>

<label for="textField">
    <span> Post Code: <font color="red">*</font></span>
    <?php echo $this->Form->input('Venue.post_code', array('class' => 'textbox', 'div' => false, 'label' => false, 'onblur' => 'getdatafromgoogle()')); ?>
</label>