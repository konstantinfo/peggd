<?php
echo $this->element("web-header");
$actual_link = 'http://peggd.com' . $_SERVER['REQUEST_URI'];
$img_url = urldecode($eventDetail['eventPicture']);
?>

<div class="midcontent">
    <h1>Event Detail</h1>

    <span><?php echo $this->html->link('Back', array('controller' => 'index')); ?></span>
    <?php if (count($eventDetail) > 0) {
        ?>
        <div class="eventdetailblock">
            <div class="eventdetailleft">
                <?php
                //echo $this->Html->link($this->Html->image($eventDetail['eventPicture'], array('alt' => '')), array('controller' => 'events', 'action' => 'eventdetail', $eventDetail['id'], 1), array('escape' => false));
                echo $this->Html->image($eventDetail['eventPicture'], array('alt' => ''));
                ?>
            </div>
            <div class="eventdetailright">
                <div class="clear">
                    <div class="datebox"><?php echo strtoupper(date("M", strtotime($eventDetail['event_date']))); ?><br>
                        <span><?php echo date("j", strtotime($eventDetail['event_date'])); ?></span></div>

                    <?php
                    $style = '';
                    if (strlen($eventDetail['title']) > 120) {
                        $style = "style='font-size:17px; line-height:normal;'";
                    }
                    ?>
                    <h2 <?php echo $style; ?>class="deatiltitle"><?php echo $eventDetail['title']; ?></h2>
                    <div class="clear"></div>
                </div>
                <div class="mapblock">
                    <div class="mapdiv"><?php
                        echo $eventDetail['venue']['name'];
                        ?> 
                        <?php echo $eventDetail['venue']['address'] . ", " . $eventDetail['venue']['city'] . ", " . $eventDetail['venue']['country']; ?></div>
                    <time><?php echo date("g A", strtotime($eventDetail['event_time'])); ?></time>
                    <div class="clear"></div>
                </div>

                <div id="example" class="timeremaningblock">

                    <?php $datetime = date("m/d/Y h:i:s", strtotime($eventDetail['event_time'])); ?>
                    <input type="hidden" id="timeLeft" value="<?php echo $datetime; ?>" />
                    <h2> Time Remaining </h2>
                    <div class=" timeblock bdrnone">
                        <h6 class="days_text">DAYS</h6>
                        <h2 class="days digit">00</h2>
                    </div>
                    <div class=" timeblock">
                        <h6 class="hours_text">HOURS</h6>
                        <h2 class="hours digit">00</h2>
                    </div>
                    <div class=" timeblock">
                        <h6 class="minutes_text">MINUTES</h6>
                        <h2 class="minutes digit" >00</h2>
                    </div>
                    <div class="timeblock">
                        <h6 class="seconds_text">SECONDS</h6>
                        <h2 class="seconds digit">00</h2>
                    </div>
                    <div class="clear"> 

                        <?php
                        if (isset($eventDetail['url']) && $eventDetail['url'] != "") {
                            //pr($this->params->pass);
                            ?>
                            <a href="#" class="ticket" target="_blank">Invite</a>
                            <a href="<?php echo $eventDetail['url']; ?>" class="ticket" target="_blank">Buy Ticket</a> <div class="clear"> </div>
                        <?php } ?>

                    </div>
                    <div class="seatlogo">
                        <?php
                        switch ($eventDetail['apiType']) {
                            case 1:
                                $name = "SeatGeek";
                                $image = "sgimg.png";
                                break;
                            case 2:
                                $name = "EventBrite";
                                $image = "eb.png";
                                break;

                            default:
                                $name = "";
                                $image = "";
                                break;
                        }
                        ?>
                        <span><?php echo $name; ?> </span> <?php echo $this->html->image("../frontend/images/" . $image); ?> </div>

                </div>
                <!--Like Counts Start-->
                <?php
                $seedGeekEvent = 0;
                if ($eventDetail['isSeatgeek'] = 1) {
                    $seedGeekEvent = 1;
                }
                $totLiks = 0;

                $totLiks = $eventDetail['totalLikes'];
                if (strlen($totLiks) > 99) {
                    $totLiks .= "+";
                }
                echo $this->html->link($totLiks, 'javascript:void(0)', array('class' => 'like', 'id' => 'likesCount_' . $eventDetail['id'], 'onclick' => 'event_like(' . $eventDetail['id'] . ',' . $eventDetail['isSeatgeek'] . ')', 'style' => 'margin-left:25px;'));
                ?>


                <!--Like Counts End-->

                <!--Comment Count Start-->

                <a class="comment" href="#"><?php
                    //$totalComments = $this->Function->totalEventComments($eventDetail['Event']['id'],$seedGeekEvent);
                    echo $eventDetail['totalComments'];
                    if (strlen($eventDetail['totalComments']) > 99) {
                        echo "+";
                    }
                    ?></a>




                <script src="//platform.twitter.com/widgets.js" type="text/javascript"></script>

                <div style='float:left;padding-top:23px;width:91px;'>

                    <a href="https://twitter.com/share" class="twitter-share-button"

                       data-url="<?php echo $actual_link; ?>"

                       data-via="pegg`d.com"

                       data-text="<?php echo $eventDetail['title']; ?>"

                       data-count="horizontal">Tweet</a>

                </div>

                <a class="fbshare" onclick="postToFeed({'link': '<?php echo $actual_link; ?>', 'picture': '<?php echo $img_url; ?>', 'name': '<?php echo $eventDetail['title']; ?>', 'caption': 'All', 'description': '<?php echo $eventDetail['title']; ?>'}, this);
                            return false;" href="javascript:void(0);" title="share to Facebook"></a>
                   <?php
                   $fbshare = $this->General->get_likes($actual_link);
                   $share = $this->General->get_tweets($actual_link);
                   ?>
                <input type="text"  readonly="readonly" value="<?php echo $fbshare; ?>" class="fbinput" />


                <div class="clear"> </div>
            </div>


            <div class="clear"></div>
        </div>
        <?php if (count($eventAttendees) > 0) { ?>
            <div class="friendattendeeblock">
                <h2>Friends Attendee</h2>
                <ul>
                    <li> <a href="#"><?php echo $this->Html->image("../frontend/images/friend.png"); ?></a> </li>
                    <li> <a href="#"><?php echo $this->Html->image("../frontend/images/friend.png"); ?></a> </li>
                    <li> <a href="#"><?php echo $this->Html->image("../frontend/images/friend.png"); ?></a> </li>
                    <li> <a href="#"><?php echo $this->Html->image("../frontend/images/friend.png"); ?></a> </li>
                    <li> <a href="#"><?php echo $this->Html->image("../frontend/images/friend.png"); ?></a> </li>
                    <li> <a href="#"><?php echo $this->Html->image("../frontend/images/friend.png"); ?></a> </li>
                    <li> <a href="#"><?php echo $this->Html->image("../frontend/images/friend.png"); ?></a> </li>
                    <li> <a href="#"><?php echo $this->Html->image("../frontend/images/friend.png"); ?></a> </li>
                    <li> <a href="#"><?php echo $this->Html->image("../frontend/images/friend.png"); ?></a> </li>
                </ul>
                <div class="clear"></div>
            </div>
            <?php
        }
        if (count($eventComments) > 0) {

            $localAndSeedgeekComments = $eventDetail['totalComments'] + $total_comments;
            ?>
            <div class="numbercomments">
                <h2>Comments (<?php echo $eventDetail['totalComments']; ?>)</h2>
                <div class="usercommentmain">
                    <?php
                    if ($total_comments > 0) {
                        foreach ($eventComments as $key => $val) {
                            ?>
                            <div class="usercomment">
                                <div class="usercommentleft">
                                    <?php
                                    //$userinfo = $this->Function->getUserInfo($val['EventComment']['user_id']);
                                    $userImage = $this->Function->getUploadImage(urldecode($val['user_image']), 'users', 120, 120, 1, 'events/noimage_listing.png');
                                    echo $this->Html->image($userImage, array('alt' => ''));
                                    ?>
                                </div>
                                <div class="usercommentright left">
                                    <h2><?php echo $val['user_name']; ?></h2>
                                    <time><?php echo $val['created']; ?></time>
                                    <div class="clear"></div>
                                    <p><?php echo $val['comment']; ?></p>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <?php
                        }
                    }
                    ?>
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
            </div>
        <?php } ?>
        <div class="postcommentform">
            <?php echo $this->Form->create("Event", array('inputDefaults' => array('label' => false, 'div' => false))) ?> 

            <?php echo $this->Form->input('EventComment.comment_text', array('type' => 'textarea', 'id' => 'getCommentText', 'rows' => 7, 'placeholder' => 'Type comment here...')) ?>
            <?php echo $this->Form->hidden('EventComment.event_id', array('type' => 'text', 'id' => 'getCommentEventId', 'value' => isset($this->params->pass[1]) ? $this->params->pass[1] : '')) ?>
            <?php echo $this->Form->hidden('EventComment.isSeatGeek', array('type' => 'text', 'id' => 'isSeatGeekComment', 'value' => isset($this->params->pass[2]) ? $this->params->pass[2] : 0)) ?>
                                                                      <!--<textarea rows="7">Type comment here...</textarea>-->

            <div class="save_btn">

                <?php
                $options = array('value' => 'Submit', 'type' => 'button', 'onclick' => 'submit_event_comments();', 'id' => 'getComment', 'div' => false,);
                echo $this->Form->end($options);
                ?>
            </div>


            <div class="clear"></div>
        </div>
        <div class="clear"></div>
    <?php } else { ?>
        <div class="eventdetailblock" style="min-height:500px;">
            No record found
        <?php }
        ?>
    </div>



    <script>
        /* fb share functionality start */
        function postToFeed(appData, fbshare) {
            FB.ui(
                    {
                        method: 'feed',
                        link: appData.link,
                        picture: appData.picture,
                        name: appData.name,
                        caption: appData.caption,
                        description: appData.description,
                    },
                    function (response) {

                        if (response && response.post_id) {
                            //userid = (response.post_id).split("_");
                            userApp = appData.app_id;
                            if (appData.from !== undefined && appData.from == 'app_page') {
                                newgotolinkPoints(userApp, 16, fbshare, bubble_16);
                            }
                        } else {
                            //alert('Post was not published.');
                        }
                    }
            );

        }


        function twittshare(appData, tweeetshareid)
        {
            url = encodeURIComponent(appData.link);
            via = encodeURIComponent(appData.name);
            desc = via + "'%7C'" + encodeURIComponent(appData.description) + "'%7C'";
            var twturl = 'https://twitter.com/intent/tweet?button_hashtag=&url=' + url + '&text=' + desc;
            var sendid = "twtShareLink_";
            document.getElementById("twtShareLink").href = twturl;
            twttr.events.bind('tweet', function (event) {
                if ((appData.from !== undefined) && appData.from == 'app_page') {
                    tweetcallback(appData.app_id, tweeetshareid);
                }
            });
            function tweetcallback(id, positionid)
            {
                newgotolinkPoints(id, 17, positionid, bubble_17);

                return false;
            }
        }

    </script>   

    <?php echo $this->element('web-footer'); ?>

</div>
</div>
<?php echo $this->html->scriptBlock("jQuery(function(){ load_timer();myClass.hideLoading(); })", array('inline' => true)) ?>