<div class="MainWraper">
    <?php echo $this->element("web-header"); ?>
    <div class="midcontent">

        <?php echo $this->element("profile-banner"); ?>

        <div class="profileblock">
            <div class="profileleft">
                <ul>
                    <li class="active"><a href="#" class="myprofile">My Profile</a></li>
                    <li><a href="#" class="event">Events</a></li>
                    <li><a href="#" class="venue">Venues</a></li>
                    <li><?php echo $this->html->link('Pegg`d Events',array('controller'=>'events'),array('class'=>'peggevent')); ?></li>
                    <li><a href="#" class="rsvpevent">RSVP Events</a></li>
                </ul>
            </div>
            <div class="profileright">
                <?php echo $this->Form->create("User", array('controller' => 'users', 'action' => 'edit_profile_info', 'div' => false, 'label' => false)); ?>
                <div class="clear">
                    <h2 class="title">Edit personal Info</h2>
                    <div class="profileinfo">
                        
                        
                        <p>
                            <span>Gender</span> 
                            <strong>
                                <?php
                                $options = array('m' => 'Male', 'f' => 'Female');
                                $attributes = array('legend' => false, 'label' => false);
                                echo $this->Form->radio('User.gender', $options, $attributes);
                                ?> 
                            </strong>
                        </p>
                        <p>
                            <span>Interested In</span> 
                            <strong>
                                <?php
                                $categories = $this->General->get_categories();
                                if (count($categories) > 0) {
                                    $options = array();
                                    $i = 1;
                                    $selected = array();

                                    if (isset($this->request->data['User']['intrested_in']) && $this->request->data['User']['intrested_in'] != '') {
                                        $selected = explode(',', $this->request->data['User']['intrested_in']);
                                    }

                                    foreach ($categories as $cat) {

                                        $options[$cat['SiteCategorie']['id']] = $cat['SiteCategorie']['title'];
                                        $cheked = '';
                                        if (in_array($cat['SiteCategorie']['id'], $selected)) {
                                            $cheked = "checked";
                                        }
                                        echo $this->Form->checkbox('User.intrested_in.', array('type' => 'checkbox', 'div' => false, 'label' => false, 'multiple' => 'checkbox', 'checked' => $cheked, 'hidden' => false, 'value' => $cat['SiteCategorie']['id']));

                                        echo " " . $cat['SiteCategorie']['title'];
                                        if ($i % 2 == 0) {
                                            echo "<br /><br />";
                                        }
                                        $i++;
                                    }
                                    //echo $this->Form->input('intetere',array('type'=>'checkbox'))
                                }
                                ?> </strong>
                        </p>
                        <p>
                            <span>Religious Views </span> 
                            <?php echo $this->Form->input('User.religon', array('class' => 'textbox', 'type' => 'text', 'div' => false, 'label' => false)); ?>
                        </p>
                    </div>
                    <div class="profileinfo">
                        <p>
                            <span>Date of birth</span> 
                            <?php echo $this->Form->input('User.dob', array('class' => 'textbox datepicker', 'type' => 'text', 'div' => false, 'label' => false)); ?>
                        </p>
                        <p>
                            <span>Languages</span> 
                            <?php echo $this->Form->input('User.language', array('class' => 'textbox', 'type' => 'text', 'div' => false, 'label' => false)); ?>
                        </p>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="clear">
                    <h2 class="title">Contact Info</h2>
                    <div class="profileinfo">
                        <p>
                            <span>Email</span> 
                            <?php echo $this->Form->input('User.username', array('class' => 'textbox', 'readonly', 'type' => 'text', 'div' => false, 'label' => false)); ?>
                        </p>
                        <p>
                            <span>Address</span> 
                            <?php echo $this->Form->input('User.address', array('class' => 'textbox', 'type' => 'text', 'div' => false, 'label' => false)); ?>
                        </p>
                    </div>
                    <div class="profileinfo">
                        <p>
                            <span>Website </span> 
                            <?php echo $this->Form->input('User.website', array('class' => 'textbox', 'type' => 'text', 'div' => false, 'label' => false)); ?>
                        </p>
                        <p>
                            <span>City</span> 
                            <?php echo $this->Form->input('User.city', array('class' => 'textbox', 'type' => 'text', 'div' => false, 'label' => false)); ?>
                        </p>
                    </div>
                    <div class="profileinfo">
                        <p>
                            <span>State </span> 
                            <?php echo $this->Form->input('User.state', array('class' => 'textbox', 'type' => 'text', 'div' => false, 'label' => false)); ?>
                        </p>

                        <p>
                            <span>Zip Code </span> 
                            <?php echo $this->Form->input('User.zip_code', array('class' => 'textbox', 'type' => 'text', 'div' => false, 'label' => false)); ?>
                        </p>
                    </div>
                    <div class="profileinfo">
                        <p>
                            <span>Country</span> 
                            <?php echo $this->Form->input('User.country', array('class' => 'textbox', 'type' => 'text', 'div' => false, 'label' => false)); ?>
                        </p>

                    </div>
                    <div class="clear"></div>
                </div>
                <div class="clear">
                    <h2 class="title">Change password</h2>
                    <div class="profileinfo">
                        <p>
                            <span>Old password</span>
                            <?php echo $this->Form->input('User.old_password', array('class' => 'textbox', 'type' => 'password', 'div' => false, 'label' => false)); ?>
                        </p>
                        <p>
                            <span>New password</span>
                            <?php echo $this->Form->input('User.new_password', array('class' => 'textbox', 'type' => 'password', 'div' => false, 'label' => false)); ?>
                        </p>
                    </div>
                    <div class="profileinfo">
                        <p>
                            <span>Confirm password </span> 
                            <?php echo $this->Form->input('User.confirm_password', array('class' => 'textbox', 'type' => 'password', 'div' => false, 'label' => false)); ?>
                        </p>
                    </div>
                    <div class="clear"></div>
                </div>

                <?php
                $options = array('value' => 'Update', 'type' => 'submit', 'div' => false,);
                echo $this->Form->end($options);
                ?>

            </div>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
    </div>
    <footer>
        <div class="featuredBox8">
            <div class="followUs"><span>Follow Us</span> <a href="#"><img src="<?php echo $this->webroot; ?>frontend/images/facebook.png" alt="" width="25" /></a> <a href="#"><img src="<?php echo $this->webroot; ?>frontend/images/twitter.png" alt=""  width="25" /></a></div>
            <div>&copy; All Rights Reserved. Pegg'd Events.</div>
        </div>
    </footer>
</div>

