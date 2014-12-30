<br />
<br />
<br />
    <div class="grid_3" >

        <div class="panel">

            <h2 class="cap"><?php echo  __('Reset password'); ?>: <?php echo $this->data['User']['username']; ?></h2>
            <div class="content">
                
                <!-- Any form you want to use this custom styling with must have the class "styled" -->
                <?php echo $this->Form->create('User', array('url' => array('action' => 'reset_password'), 'class' => "styled",'style' => "width:500px")); ?>

                <fieldset>

                    <legend></legend>

                    <!-- Text Field -->
                    <label for="textField">
                        <span>New Password:</span>
                        <?php
                        echo $this->Form->input('id');
                        echo $this->Form->input('username', array('type' => 'hidden'));
                        echo $this->Form->input('current_password', array('class' => 'textbox', 'div' => false, 'label' => false, 'id' => "", 'style' => "", 'value' => '', 'type' => 'password'));
                        ?>

						 <?php echo $this->Form->input('password', array('type' => 'hidden','class' => 'textbox', 'div' => false, 'label' => false, 'id' => "", 'style' => "", 'value' => '')); ?>
                    </label>

                    <!-- Text Field with Extras-->
                    <label for="phoneField">
                        <span>Confirm Password:</span>
                        <?php echo $this->Form->input('conf_password', array('class' => 'textbox', 'div' => false, 'label' => false, 'id' => "", 'style' => "", 'value' => '', 'type' => 'password')); ?>

                    </label>

                    <!-- Radio Buttons -->

                    <!-- Custom Upload Field -->


                    <!-- Progress Bars -->


                    <!-- "Chosen" Select Box -->

                    <!-- Buttons -->
                    <div class="non-label-section">
                        <input class="button blue small" type="submit" value="Submit" style="margin-right:10px; float:left;">
                       <!-- <input class="button yellow small" type="button" value="Submit">-->
                       <?php echo $this->Html->link(__('Cancel', true), array('controller'=>'users','action' => 'dashboard'), array('class' => 'button yellow small','style'=>"margin-left:13px; float:left;height:19px;padding-top:6px !important;")); ?>
                    </div>

                </fieldset>
                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </div>
	
	<div class="grid_1">
		<div class="panel">

			<h2 class="cap">Help</h2>
			<div class="content">
			
				<!-- NEWS ITEM -->
				
				<p><strong>This is the page for reset password of user in the User Module of this application. It has two sections, namely Left Part & Right Part. Left Part shows the password fields that the admin can reset the password. Right Part contains the help text(i.e. about the page.).</strong></p>
				<p>The Left Part shows the password fields that the admin can reset the password. On this part there are two tabs, first is "All User" that will navigate the admin to the index page and second is "Edit User" that will navigate the admin to the edit page. </p>
				

				<!-- END NEWS ITEM -->
				
			</div>
		</div>
	</div>
    
