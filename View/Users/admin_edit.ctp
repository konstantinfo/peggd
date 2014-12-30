
<br />
<br />
<br />
    <div class="grid_4" >
        <div class="panel">
            <h2 class="cap"><?php echo __('Edit User'); ?></h2>
            <div class="content">
                <div class="actions" style="float:right">
                    <?php echo $this->Html->link(__('All User', true), array('action' => 'index'), array('class' => 'button medium')); ?>
                </div>
                <!-- Any form you want to use this custom styling with must have the class "styled" -->
					<?php echo $this->Form->create('User', array('class' => "styled", 'style' => "width:500px", 'name' => 'userform', 'enctype' => 'multipart/form-data')); ?>
                <fieldset>
                    <legend> </legend>
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
				    <label for="textArea">
                        <span>Image:</span>
						<?php 
							echo $this->Form->file('image', array('class' => 'file', 'div' => false, 'label' => false, 'id' => "image", 'style' => "width:100px;float:right"));
                         if (isset($this->data['User']['image']) && $this->data['User']['image'] != '') 
						 {
                         ?>
                        <br/>
                        <a href="<?php echo Configure::read('Site.url'); ?>uploads/users/<?php echo $this->data['User']['image']; ?>" class="fancybox" rel="gallery_group" title = "User image"><img alt="User image" src="<?php echo $this->webroot; ?>uploads/users/<?php echo $this->data['User']['image']; ?>" width="200px"></a>
			       <?php } ?>
                   </label>
					<label for="phoneField">
                        <span>Phone Number:</span>
                        <?php echo $this->Form->input('phone_no', array('class' => 'textbox', 'div' => false, 'label' => false, 'id' => "phone_no", 'style' => "")); ?>
                    </label>
					<label for="phoneField"> <!--'class' => 'chosen',-->
                    <span>State :</span>
<?php echo $this->Form->input('state_id',array('selected'=>$this->data['User']['state_id'],'onchange'=>'if(this.value==""){alert("Please select any state")}else{changeCity(this.value,"cityList","users")}', 'div' => false, 'label' => false, 'id' => "state_id", 'style' => "")); ?>
                </label>
					<label for="phoneField">
                    <span>City :</span>
                    <div id="cityList">
<?php echo $this->Form->input('city_id', array('div' => false, 'label' => false, 'id' => "city_id", 'style' => "")); ?>
</div>               
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
	
	
	
	<!--<div class="grid_1">
		<div class="panel">

			<h2 class="cap">Help</h2>
			<div class="content">
				<p><strong>This is the page for editing the information regarding a specific user in the User Module of this application. It has two sections, namely Left Part & Right Part. Left Part shows the information of that specific user that the admin can edit. Right Part contains the help text(i.e. about the page.).</strong></p>
				<p>The Left Part shows the information of that specific user that the admin can edit. On this part there is a tab named "All User" that will navigate the admin to the index page.</p>
			</div>
		</div>
	</div>-->
    
