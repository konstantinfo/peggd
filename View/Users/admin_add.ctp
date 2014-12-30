<div class="grid_4" >
    <div class="panel">
        <h2 class="cap"><?php echo __('Add New User'); ?></h2>
        <div class="content">
            <div class="actions" style="float:right">
                <?php echo $this->Html->link(__('All User', true), array('action' => 'index'), array('class' => 'button medium')); ?>
            </div>
            <!-- Any form you want to use this custom styling with must have the class "styled" -->
            <?php echo $this->Form->create('User', array('class' => "styled", 'style' => "width:500px", 'name' => 'userform', 'enctype' => 'multipart/form-data')); ?>
            <fieldset>
                <legend></legend>
                <label for="textField">
                    <span> Merchant Name:</span>
                    <?php
                    echo $this->Form->input('id');
                    echo $this->Form->input('fullname', array('class' => 'textbox', 'div' => false, 'label' => false, 'id' => "fullname", 'style' => "",));
                    ?>
                </label>                    
                <label for="textField">
                    <span> User Name:</span>
                    <?php echo $this->Form->input('username', array('class' => 'textbox', 'div' => false, 'label' => false, 'id' => "username", 'style' => "")); ?>

                </label>
                <label for="textField">
                    <span> Password:</span>
                    <?php echo $this->Form->input('password', array('class' => 'textbox', 'div' => false, 'label' => false, 'id' => "password", 'style' => "")); ?>

                </label>
                <?php echo $this->Form->input('email', array('class' => 'chosen chzn-done', 'div' => false, 'label' => false, 'id' => "email", 'style' => "")); ?>
                                   
                <label for="textArea">
                    <span>Image:</span>
                    <?php
                    echo $this->Form->file('image', array('class' => 'file', 'div' => false, 'label' => false, 'id' => "image", 'style' => "width:100px;float:right"));
                    ?>	
                </label>
                <label for="phoneField">
                    <span>Phone Number:</span>
                    <?php echo $this->Form->input('phone_no', array('class' => 'textbox', 'div' => false, 'label' => false, 'id' => "phone_no", 'style' => "")); ?>
                </label>
                <label for="phoneField"> <!--'class' => 'chosen',-->
                    <span>State :</span> 
                    <?php echo $this->Form->input('state_id', array('selected' => 'AB', 'onchange' => 'if(this.value==""){alert("Please select any state")}else{changeCity(this.value,"cityList","users")}', 'div' => false, 'label' => false, 'id' => "state_id", 'style' => "")); ?>
                </label>

                <label for="phoneField">
                    <span>City :</span>
                    <div id="cityList">
                        <?php echo $this->Form->input('city_id', array('div' => false, 'label' => false, 'id' => "city_id", 'style' => "")); ?>											</div>               
                </label>

                <label for="textArea">
                    <span>Status:</span>

                    <?php echo $this->Form->input('status', array('class' => 'checkbox chzn-done', 'div' => false, 'label' => false, 'id' => "status", 'style' => "")); ?>
                </label>		

                <div class="non-label-section">
                    <input class="button red small" type="submit" value="Submit">
                    <!--<span><a class="button small" href="<?php echo Configure::read('Site.Url'); ?>admin/users">Cancel</a></span>--><span><?php echo $this->Html->link(__('Cancel', true), array('action' => 'index'), array('class' => 'button small')); ?></span>
                </div>

            </fieldset>

            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>


