<div class="MainWraper">
    <?php //pr($this->Session->read('Auth.User')); ?>
    <?php echo $this->element("web-header"); ?>
    <?php //pr($userdetail);?>
    <div class="midcontent">
      <?php echo $this->element("profile-banner"); ?>
      <div class="profileblock">
        <div class="profileleft">
          <ul>
            <li class="active"><a href="#" class="myprofile">My Profile</a></li>
            <li><a href="#" class="event">Events</a></li>
            <li><a href="#" class="venue">Venues</a></li>
            <li><a href="#" class="peggevent">Pegg'd Events</a></li>
            <li><a href="#" class="rsvpevent">RSVP Events</a></li>
          </ul>
        </div>
        <div class="profileright">
          <div class="clear">
              <div class="editbtnblock"><?php echo $this->html->link('Edit',array('controller'=>'users','action'=>'edit_profile_info'),array('class'=>'editbtn'))?></div>
            <h2 class="title">Personal Info</h2>
            <div class="profileinfo">
              <p>
                  <span>Gender</span>
 <?php
 $gender = '';
 if(isset($userdetail['User']['gender']) && $userdetail['User']['gender']!=''){
     $gender = 'Male';
     if($userdetail['User']['gender']=='f'){
        $gender = 'Female'; 
     }
 }
 echo $gender; ?> </p>
             
              <p><span>Interested In</span><label> <?php echo isset($userdetail['User']['intrested_in'])?$userdetail['User']['intrested_in']:''; ?></label></p>
              <p><span>Religious Views </span> <label><?php echo isset($userdetail['User']['religon'])?$userdetail['User']['religon']:''; ?></label> </p>
            </div>
            <div class="profileinfo">
                <p><span>Date of birth</span> <?php if(isset($userdetail['User']['dob'])){
                    $dob = date("M d, Y",strtotime($userdetail['User']['dob']));
                }else{
                    $dob = '';
                } echo $dob; ?></p>
              <p><span>Languages</span> <label><?php echo isset($userdetail['User']['language'])?$userdetail['User']['language']:''; ?></label> </p>
            </div>
            <div class="clear"></div>
          </div>
          <div class="clear">
            <h2 class="title">Contact Info</h2>
            <div class="profileinfo">
              <p><span>Email</span><label> <?php echo isset($userdetail['User']['username'])?$userdetail['User']['username']:''; ?></label></p>
              <p><span>Address</span><label> <?php 
//              $address = isset($userdetail['User']['address'])?$userdetail['User']['address']:'';
//              $city = isset($userdetail['User']['city'])?$userdetail['User']['city']:'';
//              $state = isset($userdetail['User']['state'])?$userdetail['User']['state']:'';
//              $country = isset($userdetail['User']['country'])?$userdetail['User']['country']:'';
              
              echo  $add = $this->Session->read('Auth.User.address').', '.$this->Session->read('Auth.User.city').', '.$this->Session->read('Auth.User.state').', '.$this->Session->read('Auth.User.country');?></label></p>
            </div>
            <div class="profileinfo">
              <p><span>Website </span> <label><?php echo '<a href="'.isset($userdetail['User']['website'])?$userdetail['User']['website']:"".'">'.isset($userdetail['User']['website'])?$userdetail['User']['website']:"".'</a>'; ?></label></p>
            </div>
            <div class="clear"></div>
          </div>
          <div class="clear">
            <h2 class="title">Followers (268) <a href="#">view all</a></h2>
            <div class="followlisting">
              <ul>
                <li> <a href="#"><img alt="" src="<?php echo $this->webroot; ?>frontend/images/friend.png"></a> <br>
                  Alison <br>
                  <a href="#" class="followbtn">Follow</a></li>
                <li> <a href="#"><img alt="" src="<?php echo $this->webroot; ?>frontend/images/friend.png"></a> <br>
                  Alison <br>
                  <a href="#" class="unfollowbtn">Unfollow</a></li>
                <li> <a href="#"><img alt="" src="<?php echo $this->webroot; ?>frontend/images/friend.png"></a> <br>
                  Alison <br>
                  <a href="#" class="followbtn">Follow</a></li>
                <li> <a href="#"><img alt="" src="<?php echo $this->webroot; ?>frontend/images/friend.png"></a> <br>
                  Alison<br>
                  <a href="#" class="unfollowbtn">Unfollow</a> </li>
                <li> <a href="#"><img alt="" src="<?php echo $this->webroot; ?>frontend/images/friend.png"></a> <br>
                  Alison <br>
                  <a href="#" class="followbtn">Follow</a></li>
                <li> <a href="#"><img alt="" src="<?php echo $this->webroot; ?>frontend/images/friend.png"></a> <br>
                  Alison <br>
                  <a href="#" class="unfollowbtn">Unfollow</a></li>
              </ul>
              <div class="clear"></div>
            </div>
            <div class="clear"></div>
          </div>
          <div class="clear">
            <h2 class="title">Following (304) <a href="#">view all</a></h2>
            <div class="followlisting">
              <ul>
                <li> <a href="#"><img alt="" src="<?php echo $this->webroot; ?>frontend/images/friend.png"></a> <br>
                  Alison</li>
                <li> <a href="#"><img alt="" src="<?php echo $this->webroot; ?>frontend/images/friend.png"></a> <br>
                  Alison</li>
                <li> <a href="#"><img alt="" src="<?php echo $this->webroot; ?>frontend/images/friend.png"></a> <br>
                  Alison</li>
                <li> <a href="#"><img alt="" src="<?php echo $this->webroot; ?>frontend/images/friend.png"></a> <br>
                  Alison </li>
                <li> <a href="#"><img alt="" src="<?php echo $this->webroot; ?>frontend/images/friend.png"></a> <br>
                  Alison </li>
                <li> <a href="#"><img alt="" src="<?php echo $this->webroot; ?>frontend/images/friend.png"></a> <br>
                  Alison </li>
              </ul>
              <div class="clear"></div>
            </div>
            <div class="clear"></div>
          </div>
        </div>
        <div class="clear"></div>
      </div>
      <div class="clear"></div>
    </div>
    <footer>
      <div class="featuredBox8">
        <div class="followUs"><span>Follow Us</span> <a href="#"><img src="<?php echo $this->webroot; ?>frontend/images/facebook.png" alt="" width="25" /></a> <a href="#"><img src="images/twitter.png" alt=""  width="25" /></a></div>
        <div>&copy; All Rights Reserved. Pegg'd Events.</div>
      </div>
    </footer>
  </div>