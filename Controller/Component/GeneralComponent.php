<?php

class GeneralComponent extends Component {

    var $component = array('Auth', 'Session');

    function startup(Controller $controller) {
        $this->controller = $controller;
    }

    function less5hours($dateTime, $format = 'Y-m-d H:i:s') {
        //return date($format, strtotime('-5 hours', strtotime(str_replace('T', ' ', $time))));
        $eventDateTimeConvert = date($format, strtotime($dateTime));
        return $eventDateTimeConvert;

        $this->controller->loadModel('User');
        $userTimezone = $this->controller->Session->read('Auth.User.time_zone');

        if ($userTimezone == "")
            return date($format, strtotime('-5 hours', strtotime(str_replace('T', ' ', $dateTime))));

        $utcDateTime = strtotime(str_replace('T', ' ', $dateTime));
        $diffUtc = str_replace("GMT", "", $userTimezone);
        $diffUtc = str_replace(":", " hours ", $diffUtc);
        $diffUtc .= " minutes";
        $eventTime4User = strtotime($diffUtc, $utcDateTime);
        $eventDateTimeConvert = date($format, $eventTime4User);
        return $eventDateTimeConvert;
    }

    function eventInsertDateTime($dateTime, $format = 'Y-m-d H:i:s') {
        $eventDateTimeConvert = date($format, strtotime($dateTime));
        return $eventDateTimeConvert;

        $this->controller->loadModel('User');
        $userTimezone = $this->controller->Session->read('Auth.User.time_zone');

        if ($userTimezone == "")
            return date($format, strtotime($dateTime));

        $eventDateTime = strtotime($dateTime);
        $diffUtc = str_replace("GMT+", "-", $userTimezone);
        $diffUtc = str_replace("GMT-", "+", $userTimezone);

        $eventTime4User = strtotime($diffUtc, $eventDateTime);
        $eventDateTimeConvert = date($format, $eventTime4User);
        return $eventDateTimeConvert;
    }

    /**
     * time zone converter one to another
     * @param type $time
     * @param type $to_zone
     * @param type $from_zone
     * @return converted and old time with time zone
     */
    function convert_time_zone($time = null, $to_zone = '', $from_zone = '') {
        $date = new DateTime($time, new DateTimeZone($from_zone));
        $arr['from_time'] = $date->format('Y-m-d H:i:sP');

        $date->setTimezone(new DateTimeZone($to_zone));
        $arr['to_time'] = $date->format('Y-m-d H:i:sP');

        return $arr;
    }

    /**
     * generate csv file and save into webroot/csv folder
     * @param array $data_array 
     */
    function generate_csv($data_array = array(), $filename = 'users.csv') {

        $f = fopen('php://memory', 'w+');

        foreach ($data_array as $line) {
            fputcsv($f, $line, ',');
        }

        fseek($f, 0);

        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="' . $filename . '"');

        fpassthru($f);
        fclose($f);
        exit;
    }

    /**
     *  Front End get ip address
     * @access public
     */
    function getHowLongAgo($date, $display = array('Year', 'Month', 'Day', 'Hour', 'Minute', 'Second'), $ago = 'Ago') {
        $date = getdate(strtotime($date));
        $current = getdate();
        $p = array('year', 'mon', 'mday', 'hours', 'minutes', 'seconds');
        $factor = array(0, 12, 30, 24, 60, 60);

        for ($i = 0; $i < 6; $i++) {
            if ($i > 0) {
                $current[$p[$i]] += $current[$p[$i - 1]] * $factor[$i];
                $date[$p[$i]] += $date[$p[$i - 1]] * $factor[$i];
            }
            if ($current[$p[$i]] - $date[$p[$i]] > 1) {
                $value = $current[$p[$i]] - $date[$p[$i]];
                return $value . ' ' . $display[$i] . (($value != 1) ? 's' : '') . ' ' . $ago;
            }
        }

        return '';
    }

    function refreshAuth($field = '', $value = '') {
        $this->controller->loadModel('User');
        if (!empty($field) && !empty($value)) {
            $this->controller->Session->write('Auth.User.' . $field, $value);
        } else {
//            echo $this->controller->Auth->user('id');exit;
            $userInfo = $this->controller->User->find('first', array('conditions' => array('User.id' => $this->controller->Auth->user('id'))));
            $this->controller->Auth->login($userInfo['User']);
        }
    }

    function getResSize($width = 100, $height = 100) {
        $resSizes = array();
        $userAgent = 1;
        if ($this->controller->Session->check('userAgent')) {
            $userAgent = $this->controller->Session->read('userAgent');
        }

        switch ($userAgent) {
            case 1 :
                $width = ceil($width / 2);
                $height = ceil($height / 2);
                break;
        }

        $resSizes['width'] = $width;
        $resSizes['height'] = $height;
        return $resSizes;
    }

    function getUploadImage($imageName, $category = 'users', $width = 0, $height = 0, $crop = 1, $noimage = 'noimage.jpg') {
        $destination = realpath('../../app/webroot/uploads/' . $category . '/') . '/';
        if (!$width)
            $width = 100;
        if (!$height)
            $height = 100;
        $resSizes = $this->getResSize($width, $height);
        $width = $resSizes['width'];
        $height = $resSizes['height'];
        $images = 'uploads/' . $category . '/' . $imageName;
        $extString = "";
        $extString.="&width=" . $width;
        $extString.="&height=" . $height;
        $extString.="&quality=90";
        $imgURL = Configure::read('Site.url') . "image.php?image=" . $images . $extString;
        if ($imageName != '' && file_exists($destination . $imageName))
            $imageURL = urlencode($imgURL);
        else {
            if ($category == 'users') {
                $noimage = 'users/noimage.png';
            }
            $images = 'uploads/' . $noimage;
            $imgURL = Configure::read('Site.url') . "image.php?image=" . $images . $extString;
            $imageURL = urlencode($imgURL);
        }
        $imageURL .= '&f=1';
        if ($crop) {
            $imageURL .= '&cropratio=' . $width . ':' . $height . '';
        }
        //return $imageURL . '&f=1&time=' . time();
        return $imageURL;
    }

    /**
     *
     * @param type $eventImageUrl
     * @param type $width
     * @param type $height
     * @param type $page --> listing, detail
     * @param type $image_resampled_file --> '', 2
     * @return string 
     */
    function getEventImage($eventImageUrl, $width = 632, $height = 248, $page = 'listing', $image_resampled_file = '') {

        $resSizes = $this->getResSize($width, $height);
        $width = $resSizes['width'];
        $height = $resSizes['height'];

        $cropratio = $width . ":" . $height;
        $eventImage = $eventImageUrl;
        if (strstr($eventImage, '/performers-landscape/')) {
            $eventImage = str_replace("huge", "original", $eventImage);
        }
        if (!$eventImage) {
            $eventImage = Configure::read('Site.url') . 'uploads/events/noimage_' . $page . '.png';
        }

        $eventImage = Configure::read('Site.url') . 'image_resampled' . $image_resampled_file . '.php?image=' . urlencode($eventImage) . '&width=' . $width . '&height=' . $height . '&cropratio=' . $cropratio;

        return $eventImage;
    }

    function getusername($userid = '') {
        $users = $this->controller->User->find('first', array('conditions' => array('User.id' => $userid), 'fields' => array('User.id', 'User.full_name')));
        return $users['User']['full_name'];
    }

    function getSalt() {
        $validSalt = 'acbdefghijklmnopqrstuvwxyz1234567890';
        $saltLength = strlen($validSalt);

        //We want an 8 character salt key mixed from the values above
        $salt = '';
        for ($i = 0; $i < 6; $i++) {
            //pick a random number between 0 and the max of validsalt
            $rand = mt_rand(0, $saltLength);
            //grab the char at that position
            $selectedChar = substr($validSalt, $rand, 1);
            $salt = $salt . $selectedChar;
        }
        return $salt;
    }

    function convertUnits($unit = 'km', $change = '') {
        if ($unit == 'km') {
            return $change * 1000;
        } else {
            return $change * 1609.34;
        }
    }

    function checkMail($email) {
        $dotInString = substr_count(strstr($email, '@'), '.');

        if ($dotInString > 1)
            $preg_match_string = "/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+(\.[a-zA-Z]{2,6})+(\.[a-zA-Z]{2,6})+$/";
        else
            $preg_match_string = "/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+(\.[a-zA-Z]{2,6})+$/";

        if (preg_match($preg_match_string, $email) > 0) {
            return true;
        } else {
            return false;
        }
    }

    function getDayDifference($endDate = "", $mailDate = "") {
        $seconds_diff = $endDate - $mailDate;
        $days_diff = floor($seconds_diff / 3600 / 24);
        return $days_diff;
    }

    function getMessage($key = "") {
        require 'errors.php';
        if (!isset($errorMsgArr[$key])) {
            $errorMsgArr[$key] = str_replace("_", " ", $key);
        }
        return $errorMsgArr[$key];
    }

    function taxonomiesName($categoryId) {

        $getTaxonomies = ClassRegistry::init('SiteCategory')->find('first', array('conditions' => array('SiteCategory.id' => $categoryId), 'fields' => 'seatgeek_title'));

        $taxonomiesTitle = explode(',', $getTaxonomies['SiteCategory']['seatgeek_title']);
        $result = '';
        foreach ($taxonomiesTitle as $value) {
            $result .= "&taxonomies.name=" . $value;
        }
        return $result;
    }

    function getEvents($lat = 0, $long = 0, $dist = 0, $pageNo = '1', $search = null, $categoryId = null, $perPage = 20) {
        $type = 'mi';
//        $perPage = 20;
        //filter by taxonomies
        $taxonomies = '';
        if ($search != null) {
            $search = "&q=" . str_replace(" ", "+", $search);
        }
        if (!empty($categoryId))
            $taxonomies = $this->taxonomiesName($categoryId);

        $url = "http://api.seatgeek.com/2/events/?lat=" . $lat . "&lon=" . $long . "&range=" . $dist . $type . "&page=" . $pageNo . "&per_page=" . $perPage . $search . $taxonomies;
        // echo $url; die('sdjkf');
        // return $url;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 0);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, false);
        /* Set the curl option "returntransfer" to get the response */
        curl_setopt($ch, CURLOPT_REFERER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        /* execute the CURL */
        $result = curl_exec($ch);

        return $result;
    }

    function insertEvent($eventId) {
        $eventDetail = $this->getEventDetail($eventId);
        $creatEvent = array();
        $creatEvent['id'] = '';
        $eventDetails = $this->getEventDetail($eventId);
        $seatgeekEvent = json_decode($eventDetails);

        $start_date = date('Y-m-d H:i:s', strtotime('-5 hours', strtotime(str_replace('T', ' ', $seatgeekEvent->datetime_local))));
        $end_date = date('Y-m-d H:i:s', strtotime('-5 hours', strtotime(str_replace('T', ' ', $seatgeekEvent->visible_until_utc))));

        $creatEvent['seatgeek_id'] = $seatgeekEvent->id;
        $creatEvent['name'] = $seatgeekEvent->title;
        $creatEvent['category_id'] = $this->getCategoryId($seatgeekEvent->type);
        $creatEvent['start_date'] = $start_date;
        $creatEvent['end_date'] = $end_date;
        $creatEvent['isSeatgeek'] = '1';
        $creatEvent['user_id'] = '0';
        $creatEvent['status'] = '1';


//        pr($creatEvent);exit;
        /**
         * Venue Detail 
         */
        $venue = array();
        $seatgeekVenue = $seatgeekEvent->venue;
        $peggdVenueId = $this->getVenueId($seatgeekVenue->id);

        if (!$peggdVenueId) {
            $venue['id'] = '';
            $venue['seatgeek_id'] = $seatgeekVenue->id;
            $venue['name'] = $seatgeekVenue->name;
            $venue['address'] = $seatgeekVenue->address;
            $venue['extended_address'] = $seatgeekVenue->extended_address;
            $venue['city'] = $seatgeekVenue->city;
            $venue['state'] = $seatgeekVenue->state;
            $venue['country'] = $seatgeekVenue->country;
            $venue['post_code'] = $seatgeekVenue->postal_code;
            $venue['lat'] = $seatgeekVenue->location->lat;
            $venue['long'] = $seatgeekVenue->location->lon;
            $venue['status'] = '1';
            $this->controller->loadModel('Venue');
            $this->controller->Venue->save($venue);
            $peggdVenueId = $this->controller->Venue->getLastInsertId();
        }
        $creatEvent['venue_id'] = $peggdVenueId;
        $this->controller->loadModel('Event');
        $this->controller->Event->save($creatEvent);

        $peggdEventId = $this->controller->Event->getLastInsertId();

        return $peggdEventId;
    }

    function getCategoryId($categoryName) {
        $categoryId = '6';
        $category = ClassRegistry::init('Category')->find('first', array('conditions' => array('Category.name like' => $categoryName)));
        if (!empty($category)) {
            $categoryId = $category['Category']['id'];
            if ($category['Category']['site_category_id'] != '0') {
                $categoryId = $category['Category']['site_category_id'];
            }
        }
        return $categoryId;
    }

    function getCategoryName($categoryId) {
        $categoryName = '';
        $category = ClassRegistry::init('SiteCategorie')->find('first', array('conditions' => array('SiteCategorie.id' => $categoryId)));
        if (!empty($category)) {
            $categoryName = $category['SiteCategorie']['title'];
        }
        return $categoryName;
    }

    function getSiteCategoryName($categoryName) {

        $categoryId = $this->getCategoryId($categoryName);

        $categoryName = 'OTHER';
        $siteCategoryInfo = ClassRegistry::init('SiteCategory')->find('first', array('conditions' => array('SiteCategory.id' => $categoryId)));

        if (!empty($siteCategoryInfo['SiteCategory']['title']))
            $categoryName = $siteCategoryInfo['SiteCategory']['title'];

        return $categoryName;
    }

    function getSiteCategoryNameById($id) {
        $categoryName = 'OTHER';
        $siteCategoryInfo = ClassRegistry::init('SiteCategory')->find('first', array('conditions' => array('SiteCategory.id' => $id)));

        if (!empty($siteCategoryInfo['SiteCategory']['title']))
            $categoryName = $siteCategoryInfo['SiteCategory']['title'];

        return $categoryName;
    }

    function getEventDetail($eventId) {

        $aid = Configure::read('Site.partnerId');
        $url = "http://api.seatgeek.com/2/events/" . $eventId . "?aid=" . $aid;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 0);

        /* Set the curl option "returntransfer" to get the response */
        curl_setopt($ch, CURLOPT_REFERER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        /* execute the CURL */
        $result = curl_exec($ch);

        return $result;
    }

    function getEventId($seatgeekEventId = 0) {
        $peggdEventId = 0;
        if ($seatgeekEventId) {
            $eventDetail = ClassRegistry::init('Event')->find('first', array('conditions' => array('Event.seatgeek_id' => $seatgeekEventId)));
            $peggdEventId = $eventDetail['Event']['id'];
        }

        return $peggdEventId;
    }

    function isCommented($eventId = 0, $isSeatgeek = 0) {
        $isCommented = 0;
        if ($eventId) {
            if ($isSeatgeek) {
                $eventId = $this->getEventId($eventId);
            }
            $currentUser = $this->controller->Session->read('Auth.User.id');
            $isCommented = ClassRegistry::init('EventComment')->find('count', array('conditions' => array('EventComment.user_id' => $currentUser, 'EventComment.event_id' => $eventId)));
            if ($isCommented)
                $isCommented = '1';
        }

        return $isCommented;
    }

    function isCalendar($eventId = 0, $isSeatgeek = 0) {
        $isCalendar = 0;
        if ($eventId) {
            if ($isSeatgeek) {
                $eventId = $this->getEventId($eventId);
            }
            $currentUser = $this->controller->Session->read('Auth.User.id');
            $isCalendar = ClassRegistry::init('EventCalendar')->find('count', array('conditions' => array('EventCalendar.user_id' => $currentUser, 'EventCalendar.event_id' => $eventId, 'EventCalendar.status' => array('1', '2'))));
            if ($isCalendar)
                $isCalendar = '1';
        }

        return $isCalendar;
    }

    function isEventLiked($eventId = 0, $isSeatgeek = 0) {
        $isLiked = 0;
        if ($eventId) {
            if ($isSeatgeek) {
                $eventId = $this->getEventId($eventId);
            }

            $currentUser = $this->controller->Session->read('Auth.User.id');
            $isLiked = ClassRegistry::init('EventLike')->find('count', array('conditions' => array('EventLike.event_id' => $eventId, 'EventLike.user_id' => $currentUser), 'recursive' => -1));
        }
        if ($isLiked) {
            $isLiked = '1';
        } else {
            $isLiked = '0';
        }

        return $isLiked;
    }

    function totalEventLikes($eventId = 0, $isSeatgeek = 0) {
        $totalLikes = 0;
        if ($eventId) {
            if ($isSeatgeek) {
                $eventId = $this->getEventId($eventId);
            }
            $totalLikes = ClassRegistry::init('EventLike')->find('count', array('conditions' => array('EventLike.event_id' => $eventId)));
        }
        return $totalLikes;
    }

    function totalEventComments($eventId = 0, $isSeatgeek = 0) {
        $totalComments = 0;
        if ($eventId) {
            if ($isSeatgeek) {
                $eventId = $this->getEventId($eventId);
            }
            $totalComments = ClassRegistry::init('EventComment')->find('count', array('conditions' => array('EventComment.event_id' => $eventId)));
        }
        return $totalComments;
    }

    /**
     * exicute curl
     * params: url 
     */
    function run_curl($url) {
        //open connection
        $ch = curl_init();

        //set options 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: multipart/form-data"));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //needed so that the $result=curl_exec() output is the file and isn't just true/false
        //execute post
        $result = curl_exec($ch);

        curl_close($ch);

        return json_decode($result);
    }

    function followers($id) {
        return ClassRegistry::init('Follower')->find('count', array('conditions' => array('Follower.follower_id' => $id), 'group' => array('following_id'), 'recursive' => -1));
    }

    function following($id) {
        return ClassRegistry::init('Follower')->find('count', array('conditions' => array('Follower.following_id' => $id), 'group' => array('follower_id'), 'recursive' => -1));
    }

    function isFollowing($userId, $sid) {
        $userFollow = ClassRegistry::init('Follower')->find('count', array('conditions' => array('Follower.follower_id' => $userId, 'Follower.following_id' => $sid), 'recursive' => -1));

        if (!empty($userFollow))
            $return = '1';
        else
            $return = '0';

        return $return;
    }

    #### get vanue image from "api.foursquare.com"

    function old_getVenueImageUrl_with_foursquare($search = null, $lat = null, $long = null) {
        if ($search != null) {
            $search = urlencode($search);
        }

        $clientID = '5GWGFPLAY1R0H00RB3WJNCFYFOCN3RLUFXXWVPSOCGEWIRKU';
        $clientSecret = 'LNZSR0Z5HMJDUEBFOCQNMWZ3TBZ2R03YFMJLVVA1FBY1LTDN';
        $date = date('Ymd');
        $url = "https://api.foursquare.com/v2/venues/explore?client_id=" . $clientID . "&client_secret=" . $clientSecret . "&v=" . $date . "&ll=" . $lat . "," . $long . "&limit=1&query=" . $search . "&venuePhotos=1";
        $result = file_get_contents($url);
        $data = json_decode($result, TRUE);


        if (!empty($data['response']['groups'][0]['items'][0]['venue']['photos']['groups'][0]['items'][0])) {
            $common = $data['response']['groups'][0]['items'][0]['venue']['photos']['groups'][0]['items'][0];
            //pr($common);die;

            $venueImageUrl = $common['prefix'] . $common['width'] . 'x' . $common['height'] . $common['suffix'];
            return $venueImageUrl;
        }
        return true;
    }

    function old_getVenueDetail_with_seatgeek($venueId) {
        $url = "http://api.seatgeek.com/2/venues/" . $venueId;

        $ch = curl_init();

        //set options 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: multipart/form-data"));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //needed so that the $result=curl_exec() output is the file and isn't just true/false
        //execute post
        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
    }

    function getVenues($lat = 0, $long = 0, $dist = 10, $pageNo = '1', $perPage = 30) {
        $type = 'mi';

        $url = "http://api.seatgeek.com/2/venues/?lat=" . $lat . "&lon=" . $long . "&range=" . $dist . $type . "&page=" . $pageNo . "&per_page=" . $perPage;

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 0);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, false);
        /* Set the curl option "returntransfer" to get the response */
        curl_setopt($ch, CURLOPT_REFERER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        /* execute the CURL */
        $result = curl_exec($ch);
        //pr($result);die;

        return $result;
    }

    function getVenueDetail($venueId) {
        $url = "http://api.seatgeek.com/2/events?venue.id=" . $venueId . "&per_page=1";
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 0);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, false);
        /* Set the curl option "returntransfer" to get the response */
        curl_setopt($ch, CURLOPT_REFERER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        /* execute the CURL */
        $result = curl_exec($ch);
        //pr($result);die;

        return $result;
    }

    function getSimilarVenues($search, $pageNo = '1') {
        $perPage = 10;
        $search = urlencode($search);

        $url = "http://api.seatgeek.com/2/venues/?q=" . $search . "&page=" . $pageNo . "&per_page=" . $perPage;

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 0);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, false);
        /* Set the curl option "returntransfer" to get the response */
        curl_setopt($ch, CURLOPT_REFERER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        /* execute the CURL */
        $result = curl_exec($ch);
        //pr($result);die;

        return $result;
    }

    function getVenueId($seatgeekVenueId = 0) {
        $peggdVenueId = 0;
        if ($seatgeekVenueId) {
            $venueDetail = ClassRegistry::init('Venue')->find('first', array('conditions' => array('Venue.seatgeek_id' => $seatgeekVenueId)));
            $peggdVenueId = $venueDetail['Venue']['id'];
        }
        return $peggdVenueId;
    }

    function isVenueFavorite($venueId = 0, $isSeatgeek = 0) {
        $isFavorite = 0;
        if ($venueId) {
            if ($isSeatgeek) {
                $venueId = $this->getVenueId($venueId);
            }

            $currentUser = $this->controller->Session->read('Auth.User.id');
            $isFavorite = ClassRegistry::init('VenueFavorite')->find('count', array('conditions' => array('VenueFavorite.venue_id' => $venueId, 'VenueFavorite.user_id' => $currentUser), 'recursive' => -1));
            //die('done');
        }

        if ($isFavorite) {
            $isFavorite = '1';
        } else {
            $isFavorite = '0';
        }

        return $isFavorite;
    }

    function insertVenue($venueId) {

        $venueDetails = $this->getVenueDetail($venueId);
        $seatgeekVenue = json_decode($venueDetails);

//        pr($seatgeekVenue);
//        pr($seatgeekVenue->events[0]->performers[0]->image);die;
        //venue image
        $image = '';
        if (!empty($seatgeekVenue->events[0]->performers[0]->image))
            $image = $seatgeekVenue->events[0]->performers[0]->image;

        $seatgeekVenue = $seatgeekVenue->events[0]->venue;

        // Venue Detail 
        $venue = array();
        $venue['id'] = '';
        $venue['seatgeek_id'] = $seatgeekVenue->id;
        $venue['name'] = $seatgeekVenue->name;
        $venue['lat'] = $seatgeekVenue->location->lat;
        $venue['long'] = $seatgeekVenue->location->lon;
        $venue['image'] = $image;
        $venue['address'] = $seatgeekVenue->address;
        $venue['extended_address'] = $seatgeekVenue->extended_address;
        $venue['city'] = $seatgeekVenue->city;
        $venue['state'] = $seatgeekVenue->state;
        $venue['country'] = $seatgeekVenue->country;
        $venue['post_code'] = $seatgeekVenue->postal_code;
        $venue['status'] = '1';

//        pr($venue);die;
        $this->controller->loadModel('Venue');
        $this->controller->Venue->save($venue);
        $peggdVenueId = $this->controller->Venue->getLastInsertId();

        return $peggdVenueId;
    }

    function getLocations($search, $location = null) {
        $search = $search . ' ' . $location;
        $search = urlencode($search);


        $latLong = $this->getLatLong($search);


        $url = "https://maps.googleapis.com/maps/api/place/textsearch/json?location=" . $latLong['lat'] . "," . $latLong['long'] . "&radius=50000&query=" . $search . "&sensor=true&key=AIzaSyCef9ZcQvI3HGvNkunPxbEIefefAxZNN3M";

        $result = file_get_contents($url);


        return $result;
    }

    function getLatLong($address) {
        $url = "http://maps.googleapis.com/maps/api/geocode/json?address=" . $address . "&sensor=false";
        $googleResult = file_get_contents($url);
        $decodedResult = json_decode($googleResult);

        if ($decodedResult->results[0]->geometry->location) {
            $arr = array(
                'lat' => $decodedResult->results[0]->geometry->location->lat,
                'long' => $decodedResult->results[0]->geometry->location->lng
            );

            return $arr;
        }

        return false;
    }

    /**
     * set empty udid if found and update for current user
     * @param type $userId
     * @param type $udid 
     */
    function setUdid($userId = '', $device_token = '') {
        if ($userId != '' && $device_token != '') {
            $this->controller->loadModel('User');

            $this->controller->User->updateAll(array('User.device_token' => "''"), array('User.device_token' => $device_token));

            $this->controller->User->id = $userId;
            $this->controller->User->saveField('device_token', $device_token);
        }
    }

    /**
     * get device token
     * @param type $userId
     * @return type 
     */
    function getDeiceToken($userId) {
        $device_token = "";
        $userDeiceInfo = ClassRegistry::init('User')->find('first', array('conditions' => array('User.id' => $userId), 'fields' => array('User.device_token')));

        if (!empty($userDeiceInfo['User']['device_token']))
            $device_token = $userDeiceInfo['User']['device_token'];

        return $device_token;
    }

    /**
     * get event name in short format
     * @param type $string
     * @return type 
     */
    function subStingWords($string = null, $limit = 4) {
        $stringArr = explode(' ', $string);

        $return = implode(' ', array_slice($stringArr, 0, $limit));

        if (count($stringArr) > $limit)
            return $return . '...';
        else
            return $return;
    }

    /**
     * array sort by key
     */
    function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {


        $sort_col = array();
        foreach ($arr as $key => $row) {
            $sort_col[$key] = $row[$col];
        }

        array_multisort($sort_col, $dir, $arr);
    }

    /**
     * get user setting from settings table for check user able to got push or not 
     */
    function userPushNotificationSetting($user_id = 0, $setting_key = null) {

        if (!$user_id)
            return true;

        $userInfo = ClassRegistry::init('Setting')->find('first', array('conditions' => array('Setting.user_id' => $user_id), 'recursive' => -1));
        //pr($userInfo);die;

        if (empty($userInfo))
            return true;

        if (!empty($userInfo['Setting'][$setting_key]))
            return true;
        else
            return false;
    }

    /**
     * $this->General->iphone_push_notification($deviceInfo['User']['iphone_id'], $message, $pushType, $title);
     * send push notification to user
     * @param type $deviceId
     * @param type $message
     * @param type $pushType
     * @param type $title 
     */
    public function iphone_push_notification($deviceId = "", $message = "", $pushType = "", $title = "", $user_id = 0, $setting_key = null) {

        if (!$this->userPushNotificationSetting($user_id, $setting_key))
            return true;


        $badge = $this->update_return_badgeCount($user_id);


        if (Configure::read('Site.url') == 'http://192.168.0.122/peggd/')
            return true;


        App::import('Vendor', 'applepush', array('file' => 'applepush' . DS . 'class_APNS.php'));

        $apns = new APNS('certificates/Certificates_Production_new.pem', 'certificates/Certificates_Production_new.pem'); //sandbox production
        $apns->development = 'production'; // sandbox / production
        $devices = array($deviceId);
        // APPLE APNS EXAMPLE 1
        $apns->addMessage($message); //\ue022
        $apns->addMessageCustom('pushType', $pushType);
        $apns->addMessageCustom('title', $title);
        $apns->addMessageBadge($badge);
        $apns->addMessageSound('default');
        $apns->test($devices);
        $apns->sendPushNotification($devices);
    }

    /**
     * update user batch count then return that
     * $user_id 
     */
    function update_return_badgeCount($user_id = 0) {
        $batchCount = 1;
        if ($user_id) {
            //update badge field +1
            ClassRegistry::init('User')->updateAll(
            array('User.badgeCount' => 'User.badgeCount+1'), array('User.id' => $user_id)
            );

            //get updated value
            $userInfo = ClassRegistry::init('User')->find('first', array('conditions' => array('User.id' => $user_id), 'fields' => 'badgeCount'));
            $batchCount = $userInfo['User']['badgeCount'];
        }

        return $batchCount;
    }

    function getSiteCategoryIdByName($categoryName) {
        $SiteCategory = ClassRegistry::init('SiteCategory')->find('first', array('conditions' => array('SiteCategory.title like' => $categoryName), 'fields' => array('SiteCategory.id')));

        $categoryId = "";
        if (!empty($SiteCategory)) {
            $categoryId = $SiteCategory['SiteCategory']['id'];
        }
        return $categoryId;
    }

    /* FUNCTION USED TO REPLACE DYNAMIC VALUES OD META DETAILS */

    function dynamicMetaMaker($dynamicMetaText, $metaReplaceValues) {

        foreach ($metaReplaceValues as $k => $v) {


            $pos = strpos($dynamicMetaText, $k);
            if ($pos !== false) {


                if (isset($metaReplaceValues[$k]) && $metaReplaceValues[$k] != '')
                    $dynamicMetaText = str_replace($k, $metaReplaceValues[$k], $dynamicMetaText);
                else
                    $dynamicMetaText = str_replace($k, "", $dynamicMetaText);
            }
        }

        return $dynamicMetaText;
    }

    function get_categories() {
        $this->loadModel('SiteCategorie');
        $this->SiteCategorie->find('all', array('conditions' => array('SiteCategorie.status' => 1)));
    }

    function generateRandomPassword($length = 6) {
        $string = array();
        $string['ALPHHA'] = "ABCDEFHIJKLMNOPQRSTUVXYZ";
        $string['alpha'] = "abcdefghijklmlonpqrstuvwxyz";
        $string['NUM'] = "0123456789";
        $string['SPEC'] = "#$*";
        $string = str_shuffle($string['ALPHHA'] . $string['SPEC'] . $string['alpha'] . $string['NUM']);
        return substr($string, 0, $length);
    }

    function seatGeekDetail($seatgeekEvent) {

        $event = array();
        if (isset($seatgeekEvent->status) && $seatgeekEvent->status == 'error') {
            return $event;
        } else {
            $dateTime = $this->less5hours($seatgeekEvent->datetime_local, 'F d, Y');
            $eventdateTime = $this->less5hours($seatgeekEvent->datetime_local, 'F d, Y h:i A');
            $eventendTime = $this->less5hours($seatgeekEvent->visible_until_utc, 'F d, Y h:i A');

            if ($seatgeekEvent->time_tbd) {
                $eventdateTime = $eventdateTime;
            }
            $eventImageMain = $seatgeekEvent->performers[0]->image;
            $event['id'] = $seatgeekEvent->id . "";
            $event['title'] = '' . $seatgeekEvent->title;
            $event['category'] = '' . $seatgeekEvent->type;
            $event['event_date'] = $dateTime;
            $event['event_time'] = $eventdateTime;
            $event['event_endtime'] = $eventendTime;
            $eventImage = $this->getEventImage($seatgeekEvent->performers[0]->image, 1280, 1000, 'detail');
            $event['eventPicture'] = $eventImage;
            $event['url'] = Configure::read('Site.url') . 'events/track/' . $seatgeekEvent->id;
            $event['isSeatgeek'] = '1';
            $event ["apiType"] = 1;
            $event['user_id'] = '0';
            $event['isMyEvent'] = '0';
            $event['username'] = 'Seat Geek';
            $event['user_picture'] = Configure::read('Site.url') . 'images/seatgeak.jpg';
            $event['isCalendar'] = '' . $this->isCalendar($event['id'], 1);
            $event['isCommented'] = '' . $this->isCommented($event['id'], 1);
            $event['isLiked'] = '' . $this->isEventLiked($event['id'], 1);
            $event['totalLikes'] = '' . $this->totalEventLikes($event['id'], 1);
            $event['totalComments'] = '' . $this->totalEventComments($event['id'], 1);


            /**
             * Venue Detail 
             */
            $venue = array();
            $seatgeekVenue = $seatgeekEvent->venue;
            $venue['id'] = $seatgeekVenue->id . "";
            $venue['name'] = '' . $seatgeekVenue->name;
            $venue['address'] = '' . $seatgeekVenue->address;
            $venue['extended_address'] = '' . $seatgeekVenue->extended_address;
            $venue['city'] = '' . $seatgeekVenue->city;
            $venue['country'] = '' . $seatgeekVenue->country;
            $venue['postal_code'] = '' . $seatgeekVenue->postal_code;
            $venue['lat'] = $seatgeekVenue->location->lat . "";
            $venue['long'] = $seatgeekVenue->location->lon . "";

            $event['venue'] = $venue;
        }

        return $event;
    }

    function eventBriteDetail($eventBrite) {

        $event = array();
        if (isset($seatgeekEvent->status) && $seatgeekEvent->status == 'error') {
            return $event;
        } else {
            $dateTime = $this->less5hours($eventBrite->event->start_date, 'F d, Y');
            $eventdateTime = $this->less5hours($eventBrite->event->start_date, 'F d, Y h:i A');
            $eventendTime = $this->less5hours($eventBrite->event->start_date, 'F d, Y h:i A');


            $eventImageMain = $eventBrite->event->logo;
            $event['id'] = $eventBrite->event->id . "";
            $event['title'] = '' . $eventBrite->event->title;
            $event['category'] = '' . $eventBrite->event->category;
            $event['event_date'] = $dateTime;
            $event['event_time'] = $eventdateTime;
            $event['event_endtime'] = $eventBrite->event->end_date;
            $eventImage = $this->getEventImage($eventBrite->event->logo, 1000, 1000, 'detail');
            $event['eventPicture'] = $eventImage;
            $event['url'] = $eventBrite->event->url;
            $event['isSeatgeek'] = '';
            $event ["apiType"] = 2;
            $event['user_id'] = '0';
            $event['isMyEvent'] = '0';
            $event['username'] = 'Seat Geek';
            $event['user_picture'] = Configure::read('Site.url') . 'frontend/images/seatgeak.jpg';
            $event['isCalendar'] = '' . $this->isCalendar($event['id'], 1);
            $event['isCommented'] = '' . $this->isCommented($event['id'], 1);
            $event['isLiked'] = '' . $this->isEventLiked($event['id'], 1);
            $event['totalLikes'] = '' . $this->totalEventLikes($event['id'], 1);
            $event['totalComments'] = '' . $this->totalEventComments($event['id'], 1);


            /**
             * Venue Detail 
             */
            $venue = array();
            $eventBriteVenue = $eventBrite->event->venue;
            $venue['id'] = $eventBriteVenue->id . "";
            $venue['name'] = '' . $eventBriteVenue->name;
            $venue['address'] = '' . $eventBriteVenue->address;
            $venue['extended_address'] = '' . $eventBriteVenue->address_2;
            $venue['city'] = '' . $eventBriteVenue->city;
            $venue['country'] = '' . $eventBriteVenue->country;
            $venue['postal_code'] = '' . $eventBriteVenue->postal_code;
            $venue['lat'] = $eventBriteVenue->latitude;
            $venue['long'] = $eventBriteVenue->longitude;

            $event['venue'] = $venue;
        }

        return $event;
    }

    /*
     *  Get Event Detail of multiple apis seatgeek, eventbright */

    function getEventDetailApi($eventId, $apiType = 1) {
        switch ($apiType) {
            case 1:
                $aid = Configure::read('Site.partnerId');
                $url = "http://api.seatgeek.com/2/events/" . $eventId . "?aid=" . $aid;
                $result = $this->seatGeekDetail(json_decode(file_get_contents($url)));
                break;
            case 2:
                $app_key = Configure::read('EventBrite.app_key');
                $user_key = Configure::read('EventBrite.user_key');

                $url = "https://www.eventbrite.com/json/event_get?id=" . $eventId . "&app_key=" . $app_key . "&user_key=" . $user_key;
                $result = $this->eventBriteDetail(json_decode(file_get_contents($url)));
                break;
            default:
                $result = array();
                break;
        }

        return $result;
    }

}

?>