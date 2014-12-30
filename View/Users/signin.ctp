<?php echo $this->Form->create('User', array('url' => array('controller' => 'users', 'action' => 'signin'), 'class' => 'styled')); ?>
<!-- Username Field -->
<label for="username">
    <span>Email:</span>		

    <?php echo $this->Form->input('username', array('class' => 'textbox', 'div' => false, 'label' => false)); ?>
</label>
<label for="password">
    <span>Password:</span>
    <?php
    echo $this->Form->input('password', array('class' => 'textbox', 'div' => false, 'label' => false));
    ?></label>

<!-- Login button with custom CSS classes -->
<?php
/*echo $this->Html->link(__('Forgot password?', true), array(
    'admin' => false,
    'controller' => 'users',
    'action' => 'forgot',
        ), array(
    'style' => 'float:right',
));*/
$options = array('value' => 'login', 'type' => 'submit', 'div' => false, 'class' => 'button blue small');
echo $this->Form->end($options);

// echo $this->Form->end(__('Log In', true,array('class'=>'button red small')));
?>

