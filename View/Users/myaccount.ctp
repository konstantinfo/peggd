<!--breadcrumb Section Start-->
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
<div id="breadcrumbs_Wraper">
    <section class="container">
        <ul class="crumb">
            <li> <a href="<?php echo Configure::read('Site.url') . 'users/myaccount/directories' ?>"> Dashboard </a> </li>        

            <?php
            $addTab = "My Account";
            if ($openPage == 'addDirectory') {
                $addTab = "Add Directory";
            } elseif ($openPage == 'directories') {
                $addTab = "My Directories";
            } elseif ($openPage == 'myaccount') {
                $addTab = "My Account";
            } elseif ($openPage == 'subscription') {
                $addTab = "Subscription";
            }
            ?>

            <li class="last"> <a href="javascript:void(0);" id="crumbText"><?php echo $addTab ?></a> </li>



        </ul>


    </section>


    <!--breadcrumb Section End-->
</div>
<div class="container">
    <script type="text/javascript">
        function activethis(id,textToShow)
        {
            //alert(textToShow);
            hideall();
            document.getElementById(id+'txt').style.display="block";
            document.getElementById(id).className = " active";
            document.getElementById('crumbText').innerHTML=textToShow;

        }
        function hideall()
        {
            document.getElementById("tab1txt").style.display="none";
            document.getElementById("tab2txt").style.display="none";
            document.getElementById("tab3txt").style.display="none";
            document.getElementById("tab4txt").style.display="none";

            document.getElementById("tab1").className = "";
            document.getElementById("tab2").className = "";
            document.getElementById("tab3").className = "";
            document.getElementById("tab4").className = "";

        }
    </script>


    <!--tab navigation start here-->
    <ul class="general_nav_tab">
        <li  <?php if ($openPage == 'addDirectory') { ?> class="active"  <?php } ?> onclick="activethis(this.id,'Add Directory')" id="tab1"><a href="javascript:void()">Add Directory</a></li>
        <li  <?php if ($openPage == 'directories') { ?> class="active"  <?php } ?> onclick="activethis(this.id,'My Directories')"  id="tab2"><a href="javascript:void()">My Directories</a></li>
        <li <?php if ($openPage == 'myaccount') { ?> class="active"  <?php } ?>  onclick="activethis(this.id,'My Account')"  id="tab3"><a href="javascript:void()">My Account</a></li>
        <li <?php if ($openPage == 'subscription') { ?> class="active"  <?php } ?>  onclick="activethis(this.id,'Subscription')"  id="tab4"><a href="javascript:void()">Subscription</a></li>
    </ul>
    <!--tab navigation end here-->

    <!--general tab content-->
    <div class="general_tab_content">
        <div id="tab1txt" <?php if ($openPage != 'addDirectory') { ?> style="display:none;" <?php } ?> >
           <?php echo $this->element('addDirectory'); ?>

        </div>
       
        <div id="tab2txt" <?php if ($openPage != 'directories') { ?> style="display:none;" <?php } ?> >
            <div class="my_account">
                <?php echo $this->Session->flash(); ?>            
                <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#fff">
                    <tr >
                        <th width="40%">Title</th>
                        
                        <th>Updated	</th>
                        <th>Edit</th>
                        <th>Order</th>
                        <th>Fields</th>
                        <th>Images</th>
                        <th>Export</th>
                        <th>Delete</th>
                    </tr>
                    <?php
                    
                    foreach ($directories as $directory) {
                      /*if ($directory['UserDirectory']['status'] == '1') {
                            $dirStatus = "Active";
                        } else {
                            $dirStatus = "Draft";
                        }*/
                        ?>
                        <tr <?php //if ($dirStatus == "Draft") { ?><?php //} else { ?> style="border-bottom: 1px solid #cccccc;" <?php //} ?> >
                            <td width="40%"><a href="<?php echo Configure::read('Site.url') . 'directories/search/' . $directory['UserDirectory']['id'] ?>" target="_blank"> <?php echo $directory['UserDirectory']['title']; ?> </a></td>
                           <!-- <td>
                                <?php //echo $dirStatus; ?>
                            </td>-->
                            <td><?php echo date('m/d/Y', strtotime($directory['UserDirectory']['updated'])); ?></td>
                            <td> <a href="<?php echo Configure::read('Site.url') . 'directories/editUploadCsv/' . $directory['UserDirectory']['id'] ?>" class="edit"></a> </td>
                            <td> <a href="<?php echo Configure::read('Site.url') . 'subscriptions/editColumn/' . $directory['UserDirectory']['id'] ?>" class="order"></a> </td>
                            <td> <a href="<?php echo Configure::read('Site.url') . 'subscriptions/editField/' . $directory['UserDirectory']['id'] ?>" class="edit_fields"></a> </td>
                            <td> <a href="<?php echo Configure::read('Site.url') . 'directories/uploadImages/' . $directory['UserDirectory']['id'] ?>" class="image_icon"></a> </td>
                            <td><a href="<?php echo Configure::read('Site.url') . 'directories/downloadCsv/' . $directory['UserDirectory']['id'] ?>" target="_blank" class="expand"></a></td>
                            
                            <td><a href="<?php echo Configure::read('Site.url') . 'directories/deleteDirectory/' . $directory['UserDirectory']['id']; ?>" onclick="if(confirm('Are you sure you want to delete this directory?')){return true;}else{return false;}"  class="delete"></a></td>
                        
                        </tr>

                    <?php } ?>     


                </table>

            </div>
        </div>



        <div id="tab3txt" <?php if ($openPage != 'myaccount') { ?> style="display:none;" <?php } ?> >
            <h1> Personal Information  </h1>
            <?php echo $this->Session->flash(); ?>            
            <form id="myaccountForm" name="myaccountForm" action="<?php echo Configure::read('Site.url') . 'users/myaccount'; ?>" method="POST">
                <?php $userInfo = $this->Session->read('Auth.User'); ?>
                <div class="account_tab">
                    <?php echo $this->Session->flash(); ?>   
                    <div class="inputdiv">
                        <label>First name:</label>
                        <div class="inputerror">
                            <input id="first_name" name="first_name" type="text" value="<?php echo $userInfo['first_name']; ?>" /> 
                            <p>&nbsp;</p>
                        </div>


                    </div>
                    <div class="inputdiv">
                        <label>Last name:</label>
                        <div class="inputerror">
                            <input id="last_name" name="last_name"  type="text" value="<?php echo $userInfo['last_name']; ?>" />
                            <p>&nbsp;</p>
                        </div>


                    </div>
                    <div class="inputdiv">
                        <label>Email:</label>
                        <div class="inputerror">
                            <?php echo $userInfo['username']; ?>

                        </div>


                    </div>
                    <div class="inputdiv">
                        <label>Company:</label>
                        <div class="inputerror">
                            <input type="text" name="company"  id="company"  value="<?php echo $userInfo['company']; ?>" />
                            <p>&nbsp;</p>

                        </div>


                    </div>

                    <h1>Change Password</h1>
                    <div class="inputdiv">
                        <label>Password:</label>
                        <div class="inputerror">
                            <input type="password" name="password"  id="password" /> 
                            <p>&nbsp;</p>
                        </div>


                    </div>
                    <div class="inputdiv">
                        <label>Verify your password:</label>
                        <div class="inputerror">
                            <input type="password" name="confirm_password"  id="confirm_password" /> 
                            <p>&nbsp;</p>
                        </div>


                    </div>

                    <input type="submit" class="submit" value="Save">
                </div>
            </form>


        </div>
        <div id="tab4txt"  style="display:none;">
            <h1>Subscription</h1>

            <div class="Subscription">
                <h2>
                    <?php
                    if (!empty($payment)) {
                        echo 'You Have Already ' . $payment['Payment']['plan_name'] . ' Plan. You can update at anytime by selecting Update Subscription. *';
                    } else {
                        echo 'You Currently have no Plan. You can update at anytime by selecting Update Subscription. *';
                    }
                    ?>
                </h2>
                <a href="<?php echo Configure::read('Site.url'); ?>subscriptions/plans" class="join_now updte"> Update Subscription </a>
                <a href="#" class="cancel join_now updte"> Cancel Subscription </a>


            </div>

        </div>


    </div>
    <!--general tab content-->



</div>