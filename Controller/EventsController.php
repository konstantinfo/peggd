<?php

class EventsController extends AppController {

    public $name = 'Events';
    public $uses = array('Event', 'EmailTemplate', 'EventComment', 'DynamicPage');
    //public $helpers = array('Function');
    public $components = array('Geomap');
    public $tree_category = array();

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('printCategory', 'track', 'index', 'eventdetail', 'eventajax', 'category', 'listingweb', 'test', 'like_event', 'comment_event', 'share_this'));
    }

    public function admin_index() {

        $this->layout = 'admin';
        $condition = array();
        $separator = array();


        $this->set('title_for_layout', 'Event Manager');


        if (!empty($this->request->data)) {
            if (isset($this->request->data['Event']['keyword']) && $this->request->data['Event']['keyword'] != '') {
                $keyword = trim($this->data['Event']['keyword']);
            }

            if (isset($this->request->data['Event']['order_query']) && $this->request->data['Event']['order_query'] != '') {
                $order_query = trim($this->data['Event']['order_query']);
                $this->Session->write('limit', $order_query);
            }
        } elseif (!empty($this->request->params)) {

            if (isset($this->request->params['named']['keyword']) && $this->params['named']['keyword'] != '') {
                $keyword = trim($this->request->params['named']['keyword']);
                $this->request->data['Event']['keyword'] = $keyword;
            }
        }

        if (isset($keyword) && $keyword != '') {
            $separator[] = 'keyword:' . $keyword;
            $condition[] = " (Event.name like '%" . $keyword . "%') ";
        }

        $separator = implode("/", $separator);

        $this->set('separator', $separator);

        if (!($this->Session->check('limit'))) {
            $this->Session->write('limit', '10');
            $order_query = 10;
        } else {
            $order_query = $this->Session->read('limit');
            $this->request->data['Event']['order_query'] = $order_query;
        }

        $this->paginate = array(
            'conditions' => $condition,
            'limit' => $order_query
        );

        $this->set('events', $this->paginate('Event'));

        $this->set('all_events', $this->Event->find('count'));
        $this->set('active_events', $this->Event->find('count', array('conditions' => array('Event.status' => '1'))));
        $this->set('deactive_events', $this->Event->find('count', array('conditions' => array('Event.status' => '0'))));
    }

    public function admin_add() {
        $this->add();
    }

    public function admin_edit($id) {
        $this->add($id);
    }

    public function admin_tracklisting($id) {

        $this->loadModel('TicketDetail');
        $this->layout = 'admin';
        $this->set('title_for_layout', 'Ticket Detail');
        $this->TicketDetail->bindModel(
        array(
            'belongsTo' => array(
                'User' => array(
                    'fields' => array('User.id', 'User.fullname', 'User.username')
                ),
                'Event' => array(
                    'fields' => array('Event.id', 'Event.name')
                ),
            )
        )
        );
        if (!empty($this->request->data)) {

            if (isset($this->request->data['Event']['order_query']) && $this->request->data['Event']['order_query'] != '') {
                $order_query = trim($this->data['Event']['order_query']);
                $this->Session->write('limit', $order_query);
            }
        }
        if (!($this->Session->check('limit'))) {
            $this->Session->write('limit', '10');
            $order_query = 10;
        } else {
            $order_query = $this->Session->read('limit');
            $this->request->data['Event']['order_query'] = $order_query;
        }


        $this->paginate = array(
            'conditions' => array('TicketDetail.event_id' => $id),
            'limit' => $order_query,
        );
        $ticketDetail = $this->paginate('TicketDetail');
        $this->set('ticketDetails', $ticketDetail);
    }

    public function childcategory($id, $name) {
        $this->tree_category[$id] = $name;

        $subcats = $this->Category->find('list', array('fields' => array('category_id', 'name'), 'conditions' => array('Category.parent_id' => $id)));

        if (!empty($subcats)) {
            foreach ($subcats as $key => $val) {
                $newval = $name . " | " . $val;
                $this->childcategory($key, $newval);
            }
        }
    }

    function admin_loadstate() {
        $this->layout = false;
        $this->autoRender = false;
        if ($this->request->is('ajax')) {
            $this->loadModel('State');
            if (isset($_REQUEST['country_id']) && !empty($_REQUEST['country_id'])) {
                $country_id = $_REQUEST['country_id'];
                $list_state = $this->State->find('list', array('fields' => array('stateName', 'stateName'), 'conditions' => array('State.country_id' => $country_id)));
            } else {
                $list_state = '';
            }
            $this->set('list_state', $list_state);
            $this->render('admin_loadstate');
        }
    }

    public function add($id = null) {
        $this->set('includeJS', array("jquery.mapcontainer", "demo"));
        $this->set('includeCSS', array("demo"));

        $this->loadModel('Category');
        $list_category = $this->Category->find('list', array('fields' => array('category_id', 'name'), 'conditions' => array('Category.parent_id' => '0')));

        foreach ($list_category as $key => $val) {
            $this->childcategory($key, $val);
        }
        $this->set('list_category', $this->tree_category);

        $this->loadModel('Venue');
        $list_venue = $this->Venue->find('list', array('fields' => array('id', 'name'), 'conditions' => array('Venue.status' => '1')));
        $this->set('list_venue', $list_venue);

        $this->loadModel('Country');
        $list_country = $this->Country->find('list', array('fields' => array('id', 'countryName')));
        $this->set('list_country', $list_country);

        $state_list = array();

        $msgString = '';
        if (!empty($this->request->data)) {
            if (trim($this->request->data["Event"]["category_id"]) == '') {
                $msgString .="Category is required field.<br>";
            }
            if (trim($this->request->data["Event"]["name"]) == '') {
                $msgString .="Event name is required field.<br>";
            }
            if (trim($this->request->data["Event"]["start_date"]) == '') {
                $msgString .="Event start date is required field.<br>";
            }
            if (trim($this->request->data["Event"]["end_date"]) == '') {
                $msgString .="Event end date is required field.<br>";
            }
            if (trim($this->request->data["Event"]["description"]) == '') {
                $msgString .="Event description is required field.<br>";
            }


            if (trim($this->request->data["Venue"]["name"]) == '') {
                $msgString .="Venue name is required field.<br>";
            }
            if (trim($this->request->data["Venue"]["address"]) == '') {
                $msgString .="Venue address is required field.<br>";
            }
            if (trim($this->request->data["Venue"]["country_id"]) == '') {
                $msgString .="Venue country is required field.<br>";
            }
            if (trim($this->request->data["Venue"]["state"]) == '') {
                $msgString .="Venue state is required field.<br>";
            }
            if (trim($this->request->data["Venue"]["city"]) == '') {
                $msgString .="Venue city is required field.<br>";
            }
            if (trim($this->request->data["Venue"]["location"]) == '') {
                $msgString .="Venue location is required field.<br>";
            }
            if (trim($this->request->data["Venue"]["post_code"]) == '') {
                $msgString .="Venue post code is required field.<br>";
            }



            if (isset($msgString) && $msgString != '') {
                if (trim($this->request->data["Venue"]["country_id"]) != '') {
                    $this->loadModel('State');
                    $country_id = $this->request->data["Venue"]["country_id"];
                    $state_list = $this->State->find('list', array('fields' => array('stateName', 'stateName'), 'conditions' => array('State.country_id' => $country_id)));
                }
                $this->Session->write('messages', 'error');
                $this->Session->setFlash($msgString);
            } else {
                $new_event = array();
                $new_event['Event'] = $this->request->data['Event'];

                $new_venue = array();
                $new_venue['Venue'] = $this->request->data['Venue'];

                $new_location = array();
                //$new_location['Location'] = $this->request->data['Location'];

                if ($this->request->data['Event']['id'] == '') {
                    $new_event['Event']['score'] = 0;
                    $new_event['Event']['user_id'] = $this->Auth->user('id');
                    $this->Event->create();
                }

                if ($this->request->data['Event']['venue_id'] == '') {
                    $new_venue['Venue']['score'] = 0;
                    $new_venue['Venue']['status'] = 1;
                    $this->Venue->create();
                }

                if ($this->Venue->save($new_venue)) {
                    $venue_id = $this->Venue->getLastInsertId();

                    if ($new_event['Event']['venue_id'] == '') {
                        $new_event['Event']['venue_id'] = $venue_id;
                    }
                }

                if ($this->Event->save($new_event)) {
                    $this->Session->write('messages', 'confirm');
                    $this->Session->setFlash(__('The Event has been saved successfully.', true), 'default', array('class' => 'success'));
                    $this->redirect(array('action' => 'index'));
                } else {
                    $this->Session->write('messages', 'error');
                    $this->Session->setFlash(__('The Event could not be saved. Please, try again.', true), 'default', array('class' => 'error'));
                }
            }
        } else {
            if ($id) {

                $event = $this->Event->read(null, $id);

                $this->request->data = $event;
                $this->request->data['Venue']['country_id'] = $event['Venue']['country'];
                $this->request->data['Venue']['state'] = $event['Venue']['state'];
                $this->request->data['Venue']['location'] = $event['Venue']['extended_address'];

                $this->loadModel('State');

                $country_id = $event['Venue']['country'];
                $state_list = $this->State->find('list', array('fields' => array('stateName', 'stateName'), 'conditions' => array('State.country_id' => $country_id)));
            }
        }
        $this->set('state_list', $state_list);
    }

    function admin_loadvenue() {
        $this->set('includeJS', array("jquery.mapcontainer", "demo"));
        $this->set('includeCSS', array("demo"));

        $this->loadModel('Country');
        $list_country = $this->Country->find('list', array('fields' => array('id', 'countryName')));
        $this->set('list_country', $list_country);
        $state_list = array();

        $this->layout = false;
        $this->autoRender = false;
        if ($this->request->is('ajax')) {
            $this->loadModel('Venue');

            if (isset($_REQUEST['venue_id']) && !empty($_REQUEST['venue_id'])) {
                $venue_id = $_REQUEST['venue_id'];
                $venue = $this->Venue->find('first', array('conditions' => array('Venue.id' => $venue_id)));
                $this->loadModel('State');
                echo $country_id = $venue['Venue']['country'];
                $state_list = $this->State->find('list', array('fields' => array('stateName', 'stateName'), 'conditions' => array('State.country_id' => $country_id)));
                $this->request->data = $venue;
                $this->request->data['Venue']['country_id'] = $venue['Venue']['country'];
                $this->request->data['Venue']['location'] = $venue['Venue']['extended_address'];
                $this->request->data['Venue']['longitude'] = $venue['Venue']['long'];
                $this->request->data['Venue']['latitude'] = $venue['Venue']['lat'];
            } else {
                $list_state = '';
            }
            $this->set('state_list', $state_list);
            $this->render('admin_loadvenue');
        }
    }

    function admin_process() {

        $action = '';
        $action_msg = '';
        $redirect_page = 'index';



        if (isset($this->request->data['act']) && trim($this->request->data['act']) != '') {
            if (trim($this->request->data['act']) == 'active') {
                $action = '1';
                $action_msg = 'Event has been activated successfully.';
            } elseif (trim($this->request->data['act']) == 'deactive') {
                $action = '0';
                $action_msg = 'Event has been deactivated successfully.';
            } elseif (trim($this->request->data['act']) == 'delete') {
                $action = 'Delete';
                $action_msg = 'Event has been deleted successfully.';
            }
        }

        $ids = array();
        $mailsend = '';
        $cnt_chk = 0;


        if (isset($this->request->data['Events'])) {
            $cnt_chk = count($this->request->data['Events']);
            if ($cnt_chk == 0) {
                $this->Session->write('messages', 'error');
                $this->Session->setFlash(__('No items selected.', true), 'default', array('class' => 'error'));
                $this->redirect(array('action' => 'index/'));
            }
            $Details = '';

            foreach ($this->request->data['Events'] AS $id => $value) {

                if ($value['id'] != '0') {
                    $ids[] = $id;
                }
            }

            if (count($ids) == 0 || $action == null) {
                $this->Session->write('messages', 'error');
                $this->Session->setFlash(__('No items selected.', true), 'default', array('class' => 'error'));
                $this->redirect(array('index'));
            }

            foreach ($ids as $id) {
                if ($action == 'Delete') {

                    $this->Event->delete(array('Event.id' => $id));
                } else {
                    $updateEvent = array();
                    $updateEvent['id'] = $id;
                    $updateEvent['status'] = $action;
                    $this->Event->save($updateEvent);
                }
            }

            $this->Session->write('messages', 'confirm');
            $this->Session->setFlash(__($action_msg, true), 'default', array('class' => 'success'));

            $redirect_page = 'index';


            if (isset($_POST['redact']) or trim($_POST['redact']) == 'index') {
                $redirect_page = trim($_POST['redact']);
            }
        }
        $redirectTo = $_SERVER['HTTP_REFERER'];
        $this->redirect($redirectTo);
    }

    public function admin_search($keyword = "") {
        $this->set('title_for_layout', __('Search Event', true));

        $keyword = (!empty($_GET['keyword'])) ? $_GET['keyword'] : "";

        $currentUser = $this->Auth->user('id');

        if (!($this->Session->check('limit')))
            $this->Session->write('limit', '10');
        $this->paginate = array(
            'limit' => '10'
        );

        if ($keyword != "0" && $keyword != "") {
            $this->request->data['search']['keyword'] = $keyword;
        } else {
            $keyword = $this->request->data['search']['keyword'];
        }

        if (isset($this->request->data['Event']['order_query'])) {
            $this->Session->write('limit', $this->request->data['Event']['order_query']);
        }

        $this->request->data['Event']['order_query'] = $this->Session->read('limit');


        if ($keyword != "") {
            $this->loadModel('Event');
            $keyword = $keyword;
            $this->Event->recursive = 0;
            // check the type of searching 
            $condition = array('OR' => array("Event.name LIKE " => "%" . $keyword . "%"));
            // Searching and displaying the results 
            $events = $this->paginate('Event', $condition);
            $this->set('events', $events);

            $this->set('keyword', $keyword);


            $condition1 = array('Event.status' => '1');
            $this->set('active_events', $this->Event->find('count', array('conditions' => $condition1)));

            $condition2 = array('Event.status' => '0');
            $this->set('deactive_events', $this->Event->find('count', array('conditions' => $condition2)));

            $this->set('all_events', $this->Event->find('count'));
            if (!isset($events) || empty($events)) {
                $this->Session->write('messages', 'error');
                $this->Session->setFlash(__('No Search Result(s) Found.', true));
            }
            $this->render('admin_index');
        } else {
            $this->Session->write('messages', 'error');
            $this->Session->setFlash(__('Please enter any value in text fields', true));
            $this->redirect(array('action' => 'index'));
        }
    }

    function admin_active() {
        //pr($this->request->data);die;

        $this->set('title_for_layout', __('Active Event', true));

        $this->Event->recursive = 0;

        if (isset($this->request->data['Event']['order_query'])) {
            $this->Session->write('limit', $this->request->data['Event']['order_query']);
        }


        if (!($this->Session->check('limit')))
            $this->Session->write('limit', '10');

        $this->paginate = array(
            'limit' => $this->Session->read('limit')
        );

        $this->request->data['Event']['order_query'] = $this->Session->read('limit');


        $this->set('active_events', $this->Event->find('count', array('conditions' => array('Event.status' => '1'))));
        $this->set('deactive_events', $this->Event->find('count', array('conditions' => array('Event.status' => '0'))));
        $this->set('all_events', $this->Event->find('count'));
        $this->set('events', $this->paginate('Event', array('Event.status' => '1')));
        $this->render('admin_index');
    }

    function admin_deactive() {

        $this->set('title_for_layout', __('Deactive Event', true));

        $this->Event->recursive = 0;

        if (isset($this->request->data['Event']['order_query'])) {
            $this->Session->write('limit', $this->request->data['Event']['order_query']);
        }


        if (!($this->Session->check('limit')))
            $this->Session->write('limit', '10');

        $this->paginate = array(
            'limit' => $this->Session->read('limit')
        );

        $this->request->data['Event']['order_query'] = $this->Session->read('limit');


        $this->set('active_events', $this->Event->find('count', array('conditions' => array('Event.status' => '1'))));
        $this->set('deactive_events', $this->Event->find('count', array('conditions' => array('Event.status' => '0'))));
        $this->set('all_events', $this->Event->find('count'));
        $this->set('events', $this->paginate('Event', array('Event.status' => '0')));
        $this->render('admin_index');
    }

    function admin_delete($id = null) {
        if (!$id) {
            $this->Session->write('messages', 'error');
            $this->Session->setFlash(__('Invalid id for Event', true), 'default', array('class' => 'error'));
            $this->redirect(array('action' => 'index'));
        }

        $event = $this->Event->find('first', array('conditions' => array('Event.id' => $id)));

        if ($this->Event->delete(array('Event.id' => $id))) {
            $this->Session->write('messages', 'confirm');
            $this->Session->setFlash(__('Event has been deleted', true), 'default', array('class' => 'success'));
            $this->redirect(array('action' => 'index'));
        }
    }

    public function admin_googlemap() {
        $postdata = $_POST['postdata'];

        $url = "http://maps.googleapis.com/maps/api/geocode/json?address=$postdata&sensor=true";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_REFERER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $events = json_decode($result, true);

        $lng = $events['results']['0']['geometry']['location']['lng'];
        $lat = $events['results']['0']['geometry']['location']['lat'];

        $path = $lng . "," . $lat;

        echo $path;

        die();
    }

    /**
     * event searching 
     * http://192.168.0.79/peggd/events/searching/52.1565/-2.4564654/5000/boston+celtics/2/1396152000/sid:MTY/page:1
     * params:
     * others: lat/long/distance/searchingKeyWord/categoryId/timestamp
     */
    public function searching($lat = 0, $long = 0, $dist = 10, $search = null, $categoryId = null, $lastDate = null) {
        //$this->loadModel('SiteCategory');
        $search = trim($search);
        if (!empty($lastDate)) {
            $startDate = $lastDate;
            $endDate = strtotime(date('Y-m-d H:i:s', $startDate) . "+5 days");
        } else {
            $startDate = strtotime(date('Y-m-d H:i:s'));
            $endDate = strtotime(date('Y-m-d H:i:s', $startDate) . "+5 days");
        }
        //echo date('Y-m-d', $startDate).'=='.date('Y-m-d', $endDate);die;

        $perPageLimit = '100';
        $i = 0;

        if (!empty($this->params['named']['page']))
            $pageNo = $this->params['named']['page'];
        else
            $pageNo = 1;

        $categoryCondition = '';
        if (!empty($categoryId))
            $categoryCondition = 'Event.category_id=' . $categoryId;


        $events = array();
        $event = array();

        //Fetch events from Seatgeek 

        $seatgeekResults = $this->General->getEvents($lat, $long, $dist, $pageNo, $search, $categoryId, $perPageLimit);
        $resultArr = $this->Json->decode($seatgeekResults);


        if (!empty($resultArr->events)) {
            $seatgeekEvents = $resultArr->events;

            foreach ($seatgeekEvents as $seatgeekEvent) {

                if (!isset($seatgeekEvent->performers[0]->image) || $seatgeekEvent->performers[0]->image == '') {
                    continue;
                }

                $eventcategory = $this->General->getSiteCategoryName($seatgeekEvent->type);
                $event = array();
                $event['id'] = $seatgeekEvent->id . '';
                $event['title'] = $seatgeekEvent->title . '';
                $event['category_id'] = '' . $this->General->getCategoryId($seatgeekEvent->type);
                $event['category_name'] = '' . $eventcategory;
                $eventImage = $this->General->getEventImage($seatgeekEvent->performers[0]->image, 632, 248, 'listing', 2);
                $event['eventPicture'] = $eventImage;
                $event['isSeatgeek'] = '1';
                $event['timestamp'] = strtotime($this->General->less5hours($seatgeekEvent->datetime_local, 'F d, Y'));
                $event['date'] = $this->General->less5hours($seatgeekEvent->datetime_local, 'F d, Y');

                if ($categoryId)
                    $events[] = $event;
                else
                    $events[$eventcategory][] = $event;

                //get seatgeek first & last event date
                if ($i == 0)
                    $startDate = $event['timestamp'];
                $endDate = $event['timestamp'];

                $i++;
            }
        }




        //get events from local database
        $this->Event->unbindModel(array('belongsTo' => array('Venue')));
        $this->Event->bindModel(
        array(
            'belongsTo' => array(
                'User' => array(
                    'fields' => array('User.id', 'User.fullname', 'User.image')
                ),
                'Venue' => array(
                    'fields' => array('Venue.id', 'Venue.lat', 'Venue.long')
                ),
            )
        )
        );

        $newSearch = str_replace("+", " ", $search);
        $peggdEventCount = $this->Event->find('count', array(
            'conditions' => array(
                'Event.seatgeek_id' => 0, 'Event.isPublic' => 1, 'Event.status ' => '1', 'Event.end_date > ' => date('Y-m-d H:i:s', $startDate),
                'Event.category_id' => $categoryId,
                'OR' => array(
                    array('Event.name LIKE' => $newSearch . '%'),
                    array('Event.name LIKE' => '% ' . $newSearch . '%')
                ),
            )
        )
        );


        if ($peggdEventCount > 0) {
            do {
                $peggdEvents = $this->Event->find('all', array(
                    'conditions' => array(
                        'Event.seatgeek_id' => 0, 'Event.isPublic' => 1, 'Event.status' => '1', 'Event.end_date > ' => date('Y-m-d H:i:s'), "Event.end_date BETWEEN '" . date('Y-m-d H:i:s', $startDate) . "' AND '" . date('Y-m-d H:i:s', $endDate) . "'",
                        'Event.category_id' => $categoryId,
                        'OR' => array(
                            array('Event.name LIKE' => $newSearch . '%'),
                            array('Event.name LIKE' => '% ' . $newSearch . '%')
                        ),
                    ),
                    'order' => 'Event.end_date ASC',
                )
                );
                $startDate = $endDate;
                $endDate = strtotime(date('Y-m-d H:i:s', $startDate) . "+5 days");
            } while (empty($peggdEvents));
        } else {
            $peggdEvents = array();
        }

        if (!empty($peggdEvents)) {
            foreach ($peggdEvents as $value) {
                $i++;
                $event = array();
                $eventImage = $this->General->getUploadImage($value['Event']['image'], 'events', 632, 248, 1, 'events/noimage_listing.png');
                $eventcategory = $this->General->getCategoryName($value['Event']['category_id']);

                $event['id'] = '' . $value['Event']['id'];
                $event['title'] = '' . stripcslashes($value['Event']['name']);
                $event['category_id'] = '' . $value['Event']['category_id'];
                $event['category_name'] = '' . $eventcategory;
                $event['eventPicture'] = $eventImage;
                $event['isSeatgeek'] = '0';
                $event['timestamp'] = strtotime($this->General->less5hours($value['Event']['end_date'], 'F d, Y'));
                $event['date'] = $this->General->less5hours($value['Event']['end_date'], 'F d, Y');


                if ($categoryId)
                    $events[] = $event;
                else
                    $events[$eventcategory][] = $event;
            }
        }

        if (!empty($events))
            $this->General->array_sort_by_column($events, 'timestamp');



        $currentPage = $pageNo;
        if (!$i) {
            if ($i < $perPageLimit) {
                $currentPage = $resultArr->meta->page + ceil($this->params['paging']['Event']['count'] / $perPageLimit - 1);
            }
        }
        if (!empty($resultArr->meta->total))
            $seatgeekTotal = $resultArr->meta->total;
        else
            $seatgeekTotal = '0';

        $outPutArray['replyCode'] = 'success';
        $outPutArray['replyMsg'] = 'events searching';
        $outPutArray['total_events'] = ($seatgeekTotal + $this->params['paging']['Event']['count']) . '';
        $outPutArray['per_page'] = $perPageLimit . '';
        $outPutArray['page'] = $currentPage . '';
        $outPutArray['events'] = $events;
        $output = $this->Json->encode($outPutArray);
        echo $output;
        exit;
    }

    /**
     * event listing 
     * http://192.168.0.79/peggd/events/explorPage/52.1565/-2.4564654/5000/1/1396152000/sid:Mjc/page:1
     * params:
     * others: lat/long/distance/categoryId/timestamp/
     */
    public function explorPage($lat = 0, $long = 0, $dist = 10, $categoryId = null, $lastDate = null) {
        //$this->loadModel('SiteCategory');

        if (!empty($lastDate)) {
            $startDate = $lastDate;
            $endDate = strtotime(date('Y-m-d H:i:s', $startDate) . "+5 days");
        } else {
            $startDate = strtotime(date('Y-m-d H:i:s'));
            $endDate = strtotime(date('Y-m-d H:i:s', $startDate) . "+5 days");
        }


        $perPageLimit = '100';
        $i = 0;

        if (!empty($this->params['named']['page']))
            $pageNo = $this->params['named']['page'];
        else
            $pageNo = 1;



        $categoryCondition = '';
        if (!empty($categoryId))
            $categoryCondition = 'Event.category_id=' . $categoryId;



        $events = array();
        $event = array();


        $seatgeekResults = $this->General->getEvents($lat, $long, $dist, $pageNo, '', $categoryId, $perPageLimit);

        $resultArr = $this->Json->decode($seatgeekResults);


        if (!empty($resultArr->events)) {
            $seatgeekEvents = $resultArr->events;

            foreach ($seatgeekEvents as $seatgeekEvent) {

                if (!isset($seatgeekEvent->performers[0]->image) || $seatgeekEvent->performers[0]->image == '') {
                    continue;
                }

                $eventcategory = $this->General->getSiteCategoryName($seatgeekEvent->type);
                $event = array();
                $event['id'] = $seatgeekEvent->id . '';
                $event['title'] = $seatgeekEvent->title . '';
                $event['category_id'] = '' . $this->General->getCategoryId($seatgeekEvent->type);
                $event['category_name'] = '' . $eventcategory;
                $eventImage = $this->General->getEventImage($seatgeekEvent->performers[0]->image, 632, 248, 'listing', 2);
                $event['eventPicture'] = $eventImage;
                $event['isSeatgeek'] = '1';
                $event['timestamp'] = strtotime($this->General->less5hours($seatgeekEvent->datetime_local, 'F d, Y'));
                $event['date'] = $this->General->less5hours($seatgeekEvent->datetime_local, 'F d, Y');

                if ($categoryId)
                    $events[] = $event;
                else
                    $events[$eventcategory][] = $event;

                //get seatgeek first & last event date
                if ($i == 0)
                    $startDate = $event['timestamp'];
                $endDate = $event['timestamp'];

                $i++;
            }
        }

        //get events from local database
        $this->Event->unbindModel(array('belongsTo' => array('Venue')));
        $this->Event->bindModel(
        array(
            'belongsTo' => array(
                'User' => array(
                    'fields' => array('User.id', 'User.fullname', 'User.image')
                ),
                'Venue' => array(
                    'fields' => array('Venue.id', 'Venue.lat', 'Venue.long')
                ),
            )
        )
        );
        $peggdEventCount = $this->Event->find('count', array(
            'conditions' => array(
                'Event.seatgeek_id' => 0, 'Event.isPublic' => 1, 'Event.status ' => '1', 'Event.end_date > ' => date('Y-m-d H:i:s', $startDate), 'Event.category_id' => $categoryId
            )
        )
        );

        if ($peggdEventCount > 0) {
            do {
                $peggdEvents = $this->Event->find('all', array(
//                'conditions' => array('Event.seatgeek_id'=>0, 'Event.status'=>'1', 'Event.end_date > '=>date('Y-m-d H:i:s'), "Event.end_date BETWEEN '". date('Y-m-d H:i:s', $startDate) ."' AND '2014-12-12 00:00:00'"),
                    'conditions' => array(
                        'Event.seatgeek_id' => 0, 'Event.isPublic' => 1, 'Event.status' => '1', 'Event.end_date > ' => date('Y-m-d H:i:s'), "Event.end_date BETWEEN '" . date('Y-m-d H:i:s', $startDate) . "' AND '" . date('Y-m-d H:i:s', $endDate) . "'",
                        'Event.category_id' => $categoryId
                    ),
                    'order' => 'Event.end_date ASC',
//                    'limit' => $perPageLimit
                )
                );
                $startDate = $endDate;
                $endDate = strtotime(date('Y-m-d H:i:s', $startDate) . "+5 days");
            } while (empty($peggdEvents));
        } else {
            $peggdEvents = array();
        }

        if (!empty($peggdEvents)) {
            foreach ($peggdEvents as $value) {
                $i++;
                $event = array();
                $eventImage = $this->General->getUploadImage($value['Event']['image'], 'events', 632, 248, 1, 'events/noimage_listing.png');
                $eventcategory = $this->General->getCategoryName($value['Event']['category_id']);

                $event['id'] = '' . $value['Event']['id'];
                $event['title'] = '' . stripcslashes($value['Event']['name']);
                $event['category_id'] = '' . $value['Event']['category_id'];
                $event['category_name'] = '' . $eventcategory;
                $event['eventPicture'] = $eventImage;
                $event['isSeatgeek'] = '0';
                //$event['timestamp'] = strtotime($value['Event']['end_date']);
                //$event['date'] = $value['Event']['end_date'];
                $event['timestamp'] = strtotime($this->General->less5hours($value['Event']['end_date'], 'F d, Y'));
                $event['date'] = $this->General->less5hours($value['Event']['end_date'], 'F d, Y');

                if ($categoryId)
                    $events[] = $event;
                else
                    $events[$eventcategory][] = $event;
            }
        }

        //NOTE:- that function is working only when you can't filter data based on category name
        if (!empty($events))
            $this->General->array_sort_by_column($events, 'timestamp');


        $currentPage = $pageNo;
        if (!$i) {
            if ($i < $perPageLimit) {
                $currentPage = $resultArr->meta->page + ceil($this->params['paging']['Event']['count'] / $perPageLimit - 1);
            }
        }

        $outPutArray['replyCode'] = 'success';
        $outPutArray['replyMsg'] = 'events listing';
        $outPutArray['total_events'] = ($resultArr->meta->total + $this->params['paging']['Event']['count']) . '';
        $outPutArray['per_page'] = $perPageLimit . '';
        $outPutArray['page'] = $currentPage . '';
        $outPutArray['events'] = $events;
        $output = $this->Json->encode($outPutArray);
        echo $output;
        exit;
    }

    /**
     * event listing
     * http://192.168.0.122/peggd/events/listing/32.7478/-97.0928/20/1396152000/page:1/sid:Mjc
     * params: lat/long/distance/timestamp/page
     * others: 
     */
    public function listing($lat = 0, $long = 0, $dist = 10, $lastDate = null) {
        $currentUser = $this->Auth->user('id');

        $events = array();

        if (!empty($lastDate)) {
            $startDate = $lastDate;
            $endDate = strtotime(date('Y-m-d H:i:s', $startDate) . "+5 days");
        } else {
            $startDate = strtotime(date('Y-m-d H:i:s'));
            $endDate = strtotime(date('Y-m-d H:i:s', $startDate) . "+5 days");
        }

        $perPageLimit = 50;
        $i = 0;

        if (!empty($this->params['named']['page']))
            $pageNo = $this->params['named']['page'];
        else
            $pageNo = 1;

        //Fetch events from Seatgeek 

        $seatgeekResults = $this->General->getEvents($lat, $long, $dist, $pageNo, '', '', $perPageLimit);
        $resultArr = $this->Json->decode($seatgeekResults);

        if (isset($resultArr->events) && !empty($resultArr->events)) {
            $seatgeekEvents = $resultArr->events;
            foreach ($seatgeekEvents as $seatgeekEvent) {

                if (!isset($seatgeekEvent->performers[0]->image) || $seatgeekEvent->performers[0]->image == '') {
                    continue;
                }

                $event['id'] = $seatgeekEvent->id . '';
                $event['title'] = '' . $seatgeekEvent->title;
                $event['category_type'] = '' . $this->General->getCategoryId($seatgeekEvent->type);
                $event['timestamp'] = strtotime($this->General->less5hours($seatgeekEvent->datetime_local, 'F d, Y'));
                $event['event_date'] = $this->General->less5hours($seatgeekEvent->datetime_local, 'F d, Y');
                $eventImage = $this->General->getEventImage($seatgeekEvent->performers[0]->image);
                $event['eventPicture'] = $eventImage;
                $event['isSeatgeek'] = '1';
                $event['latitude'] = $seatgeekEvent->venue->location->lat . '';
                $event['longitude'] = $seatgeekEvent->venue->location->lon . '';
                $event['user_id'] = '0';
                $event['username'] = 'Seat Geek';
                $event['user_picture'] = Configure::read('Site.url') . 'images/seatgeak.jpg';
                $event['isLiked'] = '' . $this->General->isEventLiked($event['id'], 1);
                $event['totalLikes'] = '' . $this->General->totalEventLikes($event['id'], 1);
                $event['totalComments'] = '' . $this->General->totalEventComments($event['id'], 1);

                $events[] = $event;

                //get seatgeek first & last event date
                if ($i == 0)
                    $startDate = $event['timestamp'];
                $endDate = $event['timestamp'];

                $i++;
            }
        }



        $this->Event->unbindModel(array('belongsTo' => array('Venue')));
        $this->Event->bindModel(
        array(
            'belongsTo' => array(
                'User' => array(
                    'fields' => array('User.id', 'User.fullname', 'User.first_name', 'User.last_name', 'User.image')
                ),
                'Venue' => array(
                    'fields' => array('Venue.id', 'Venue.lat', 'Venue.long')
                ),
            )
        )
        );


        $event = array();

        //get events from local database
        //echo date('Y-m-d H:i:s', strtotime($startDate)).'==to=='.$endDate;

        $peggdEvents = $this->Event->find('all', array(
            'conditions' => array('Event.seatgeek_id' => 0, 'Event.isPublic' => 1, 'Event.status' => '1', 'Event.end_date > ' => date('Y-m-d H:i:s'), "Event.end_date BETWEEN '" . date('Y-m-d H:i:s', $startDate) . "' AND '" . date('Y-m-d H:i:s', $endDate) . "'"),
            'order' => 'Event.end_date ASC',
//            'limit' => $perPageLimit
        )
        );



        if (!empty($peggdEvents)) {
            foreach ($peggdEvents as $value) {

                $eventImage = $this->General->getUploadImage($value['Event']['image'], 'events', 632, 248, 1, 'events/noimage_listing.png');
                $user_image = $this->General->getUploadImage($value['User']['image'], 'users', 80, 80);

                $event['id'] = '' . $value['Event']['id'];
                $event['title'] = '' . stripcslashes($value['Event']['name']);
                $event['category_type'] = '' . $value['Event']['category_id'];

                $event['timestamp'] = strtotime($this->General->less5hours($value['Event']['end_date'], 'F d, Y'));
                $event['event_date'] = $this->General->less5hours($value['Event']['end_date'], 'F d, Y');
                $event['eventPicture'] = $eventImage;
                $event['isSeatgeek'] = '0';
                $event['latitude'] = '' . $value['Venue']['lat'];
                $event['longitude'] = '' . $value['Venue']['long'];
                $event['user_id'] = '' . $value['User']['id'];
                $event['username'] = '' . $value['User']['first_name'] . ' ' . $value['User']['last_name'];
                $event['user_picture'] = $user_image;
                $event['isLiked'] = $this->General->isEventLiked($event['id'], 0);
                $event['totalLikes'] = '' . $this->General->totalEventLikes($event['id'], 0);
                $event['totalComments'] = '' . $this->General->totalEventComments($event['id'], 0);

                $events[] = $event;
            }
        }

        if (!empty($events))
            $this->General->array_sort_by_column($events, 'timestamp');

        $currentPage = $pageNo;
        if (!$i) {
            if ($i < $perPageLimit) {
                $seatgeekPage = (!empty($resultArr->meta->page)) ? $resultArr->meta->page : 0;
                $currentPage = $seatgeekPage + ceil(count($peggdEvents) / $perPageLimit - 1);
            }
        }

        $seatgeekTotal = (!empty($resultArr->meta->total)) ? $resultArr->meta->total : 0;
        $total_events = $seatgeekTotal + count($peggdEvents);

        $outPutArray['replyCode'] = 'success';
        $outPutArray['replyMsg'] = 'events listing';
        $outPutArray['total_events'] = $total_events . '';
        $outPutArray['per_page'] = $perPageLimit . '';
        $outPutArray['page'] = $currentPage . '';
        $outPutArray['events'] = $events;
        $output = $this->Json->encode($outPutArray);
        echo $output;
        exit;
    }

    public function eventajax($lat = 0, $long = 0, $dist = 10, $lastDate = null) {

        $this->layout = false;
        $this->autoRender = false;
        $eventAttendees = array();
        $currentUser = $this->Auth->user('id');
        $search = $cat_type = "";
        $this->loadModel('EventCalendar');
        if (isset($this->request->data['search']) && $this->request->data['search'] != '') {

            $search = $this->request->data['search'];
        }
        $borderclass = '';
        if (isset($this->request->data['cat_type']) && $this->request->data['cat_type'] != 0 && $this->request->data['cat_type'] != 'all') {
            $cat_type = $this->request->data['cat_type'];
            $borderclass = '';
            $catId = $this->request->data["cat_type"];
            switch ($cat_type) {
                case 1:
                    $borderclass = 'artbg';
                    break;
                case 2:
                    $borderclass = 'musicbg';
                    break;
                case 3:
                    $borderclass = 'socialbg';
                    break;
                case 4:
                    $borderclass = 'sportbg';
                    break;
                case 5:
                    $borderclass = 'mindbg';
                    break;

                default:
                    $borderclass = '';
                    break;
            }
        }

        $events = array();

        if (!empty($lastDate)) {
            $startDate = $lastDate;
            $endDate = strtotime(date('Y-m-d H:i:s', $startDate) . "+5 days");
        } else {
            $startDate = strtotime(date('Y-m-d H:i:s'));
            $endDate = strtotime(date('Y-m-d H:i:s', $startDate) . "+5 days");
        }

        if ($this->request->data['group_no'] == 0) {
            $perPageLimit = 48;
        } else {
            $perPageLimit = 24;
        }

        $i = 0;

        if (!empty($this->request->data['group_no']))
            $pageNo = $this->request->data['group_no'] + 1;
        else
            $pageNo = 1;

        /* Fetch lat long from url */


        if ($this->Session->read('locationData')) {

            $lat = $this->Session->read('locationData.latitude');
            $long = $this->Session->read('locationData.longitude');
        }

        //Fetch events from Seatgeek 
        $seatgeekResults = $this->General->getEvents($lat, $long, $dist, $pageNo, $search, $cat_type, $perPageLimit);
        $resultArr = $this->Json->decode($seatgeekResults);

        if (isset($resultArr->events) && !empty($resultArr->events)) {
            $seatgeekEvents = $resultArr->events;

            foreach ($seatgeekEvents as $seatgeekEvent) {
                $eventdateTime = $this->General->less5hours($seatgeekEvent->datetime_local, 'F d, Y h:i A');
                $eventendTime = $this->General->less5hours($seatgeekEvent->visible_until_utc, 'F d, Y h:i A');

                if (!isset($seatgeekEvent->performers[0]->image) || $seatgeekEvent->performers[0]->image == '') {
                    continue;
                }

                $event['id'] = $seatgeekEvent->id . '';
                $event['title'] = '' . $seatgeekEvent->title;
                $event['category_type'] = '' . $this->General->getCategoryId($seatgeekEvent->type);
                $event['event_time'] = $eventdateTime;
                $event['event_endtime'] = $eventendTime;
                $event['timestamp'] = strtotime($this->General->less5hours($seatgeekEvent->datetime_local, 'F d, Y'));
                $event['end_timestamp'] = strtotime($this->General->less5hours($seatgeekEvent->visible_until_utc, 'F d, Y'));
                $event['event_date'] = $this->General->less5hours($seatgeekEvent->datetime_local, 'M d, Y');
                $event['end_date'] = $this->General->less5hours($seatgeekEvent->visible_until_utc, 'M d, Y');
                $eventImage = $this->General->getEventImage($seatgeekEvent->performers[0]->image, 512, 300);
                $event['eventPicture'] = $eventImage;
                $event['isSeatgeek'] = '1';
                $event['latitude'] = $seatgeekEvent->venue->location->lat . '';
                $event['longitude'] = $seatgeekEvent->venue->location->lon . '';
                $event['address'] = $seatgeekEvent->venue->address . '';
                $event['city'] = $seatgeekEvent->venue->city . '';
                $event['state'] = $seatgeekEvent->venue->state . '';
                $event['country'] = $seatgeekEvent->venue->country . '';
                $event['user_id'] = '0';
                $event['username'] = 'Seat Geek';
                $event['user_picture'] = Configure::read('Site.url') . 'images/seatgeak.jpg';
                $event['isLiked'] = '' . $this->General->isEventLiked($event['id'], 1);
                $event['totalLikes'] = '' . $this->General->totalEventLikes($event['id'], 1);
                $event['totalComments'] = '' . $this->General->totalEventComments($event['id'], 1);

                $events[] = $event;


                /* For Event Attendies */

                $peggdEventId = $this->General->getEventId($seatgeekEvent->id);
                $conditions = array('EventCalendar.event_id' => $peggdEventId, 'EventCalendar.status' => array('1', '2'));
                if (isset($currentUser) && $currentUser != '') {
                    $conditions['EventCalendar.user_id <>'] = $currentUser;
                }

                $eventCalendars = $this->EventCalendar->find('all', array('conditions' => $conditions, 'group' => 'EventCalendar.user_id', 'limit' => '10', 'recursive' => '0'));

                if (!empty($eventCalendars)) {
                    foreach ($eventCalendars as $eventCalendar) {
                        $eventAttendee = array();
                        $eventAttendee['user_id'] = '' . $eventCalendar['User']['id'];
                        $eventAttendee['image'] = $this->General->getUploadImage($eventCalendar['User']['image'], 'users', 100, 100);
                        $eventAttendees[] = $eventAttendee;
                    }
                }

                /* For Event Attendies */

                //get seatgeek first & last event date
                if ($i == 0)
                    $startDate = $event['timestamp'];
                $endDate = $event['timestamp'];

                $i++;
            }
        }



        $this->Event->unbindModel(array('belongsTo' => array('Venue')));
        $this->Event->bindModel(
        array('belongsTo' => array('User' => array('fields' => array('User.id', 'User.fullname', 'User.first_name', 'User.last_name', 'User.image')), 'Venue' => array('fields' => array('Venue.id', 'Venue.lat', 'Venue.long')),)));


        $event = array();

        //get events from local database


        $peggdEvents = $this->Event->find('all', array('conditions' => array('Event.seatgeek_id' => 0, 'Event.isPublic' => 1, 'Event.status' => '1', 'Event.end_date > ' => date('Y-m-d H:i:s'), "Event.end_date BETWEEN '" . date('Y-m-d H:i:s', $startDate) . "' AND '" . date('Y-m-d H:i:s', $endDate) . "'"), 'order' => 'Event.end_date ASC',
//            'limit' => $perPageLimit
        ));



        if (count($peggdEvents) > 0) {
            foreach ($peggdEvents as $value) {

                $eventImage = $this->General->getUploadImage($value['Event']['image'], 'events', 632, 248, 1, 'events/noimage_listing.png');
                $user_image = $this->General->getUploadImage($value['User']['image'], 'users', 80, 80);

                $event['id'] = '' . $value['Event']['id'];
                $event['title'] = '' . stripcslashes($value['Event']['name']);
                $event['category_type'] = '' . $value['Event']['category_id'];
                //$event['timestamp'] = strtotime($value['Event']['end_date']);
                //$event['event_date'] = date('F d, Y', strtotime($value['Event']['end_date']));
                $event['timestamp'] = strtotime($this->General->less5hours($value['Event']['end_date'], 'F d, Y'));
                $event['event_date'] = $this->General->less5hours($value['Event']['end_date'], 'F d, Y');
                $event['eventPicture'] = $eventImage;
                $event['isSeatgeek'] = '0';
                $event['latitude'] = '' . $value['Venue']['lat'];
                $event['longitude'] = '' . $value['Venue']['long'];
                $event['user_id'] = '' . $value['User']['id'];
                $event['username'] = '' . $value['User']['first_name'] . ' ' . $value['User']['last_name'];
                $event['user_picture'] = $user_image;
                $event['isLiked'] = $this->General->isEventLiked($event['id'], 0);
                $event['totalLikes'] = '' . $this->General->totalEventLikes($event['id'], 0);
                $event['totalComments'] = '' . $this->General->totalEventComments($event['id'], 0);

                $events[] = $event;
                $peggdEventId = $event['id'];



                $conditions = array('EventCalendar.event_id' => $peggdEventId, 'EventCalendar.status' => array('1', '2'));
                if (isset($currentUser) && $currentUser != '') {
                    $conditions['EventCalendar.user_id <>'] = $currentUser;
                }

                $eventCalendars = $this->EventCalendar->find('all', array('conditions' => $conditions, 'group' => 'EventCalendar.user_id', 'limit' => '10', 'recursive' => '0'));

                if (!empty($eventCalendars)) {
                    foreach ($eventCalendars as $eventCalendar) {
                        $eventAttendee = array();
                        $eventAttendee['user_id'] = '' . $eventCalendar['User']['id'];
                        $eventAttendee['image'] = $this->General->getUploadImage($eventCalendar['User']['image'], 'users', 100, 100);
                        $eventAttendees[] = $eventAttendee;
                    }
                }
            }
        }

        if (!empty($events))
            $this->General->array_sort_by_column($events, 'timestamp');

        $currentPage = $pageNo;
        if (!$i) {
            if ($i < $perPageLimit) {
                $seatgeekPage = (!empty($resultArr->meta->page)) ? $resultArr->meta->page : 0;
                $currentPage = $seatgeekPage + ceil(count($peggdEvents) / $perPageLimit - 1);
            }
        }

        $seatgeekTotal = (!empty($resultArr->meta->total)) ? $resultArr->meta->total : 0;
        $total_events = $seatgeekTotal + count($peggdEvents);




        $view = new View($this, false);
        $content = $view->element("eventajax", array("eventAjaxData" => $events, 'total_events_ajax' => $total_events, 'perPageLimit_ajax' => $perPageLimit, 'currentPage' => $currentPage, 'group_no' => $this->request->data['group_no'], 'borderclass' => $borderclass, 'eventAttendees' => $eventAttendees));



        echo $content;
    }

    /**
     * http://support24hour.com/workplace2/peggd/events/detail/1589519/1/sid:MjQ/agent:2 //is seatgeek == 1
     * http://support24hour.com/workplace2/peggd/events/detail/148/0/sid:Mjc/agent:2 //is seatgeek == 0
     * @param type $eventId
     * @param type $isSeatgeek 
     */
    function detail($eventId = 0, $isSeatgeek = 0) {

        //convert time one to another zone
        //$newTime = $this->General->convert_time_zone($eventdateTime, 'UTC', $this->Auth->user('time_zone'));

        $currentUser = $this->Auth->user('id');

        //pr($this->params['named']['page']);die;

        $eventDetail = array();
        $event = array();
        $eventAttendees = array();
        if ($isSeatgeek)
            $peggdEventId = $this->General->getEventId($eventId);
        else
            $peggdEventId = $eventId;
        $perPageLimit = 3;

        $currentPage = (!empty($this->params['named']['page'])) ? $this->params['named']['page'] : '1';
        if ($currentPage == 1) {
            if ($isSeatgeek) {
                //$peggdEventId = $this->General->getEventId($eventId);
                /**
                 * Fetch event details from seatgeek 
                 */
                $eventDetails = $this->General->getEventDetail($eventId);
                $seatgeekEvent = $this->Json->decode($eventDetails);
                //pr($seatgeekEvent);die;

                if (isset($seatgeekEvent->status) && $seatgeekEvent->status == 'error') {
                    $arr = array('replyCode' => 'error', 'replyMsg' => $this->General->getMessage('event_not_found'));
                    echo $this->Json->encode($arr);
                    exit;
                }

                $dateTime = $this->General->less5hours($seatgeekEvent->datetime_local, 'F d, Y');
                $eventdateTime = $this->General->less5hours($seatgeekEvent->datetime_local, 'F d, Y h:i A');
                if ($seatgeekEvent->time_tbd) {
                    $eventdateTime = $dateTime . ' at TBD';
                }
                $event['id'] = $seatgeekEvent->id . "";
                $event['title'] = '' . $seatgeekEvent->title;
                $event['category'] = '' . $seatgeekEvent->type;
                $event['event_date'] = $dateTime;
                $event['event_time'] = $eventdateTime;
                $eventImage = $this->General->getEventImage($seatgeekEvent->performers[0]->image, 640, 360, 'detail');
                $event['eventPicture'] = $eventImage;
                //$event['url'] = $seatgeekEvent->url;
                $event['url'] = Configure::read('Site.url') . 'events/track/' . $seatgeekEvent->id;
                $event['isSeatgeek'] = '1';
                $event['user_id'] = '0';
                $event['isMyEvent'] = '0';
                $event['username'] = 'Seat Geek';
                $event['user_picture'] = Configure::read('Site.url') . 'images/seatgeak.jpg';
                $event['isCalendar'] = '' . $this->General->isCalendar($event['id'], 1);
                $event['isCommented'] = '' . $this->General->isCommented($event['id'], 1);
                $event['isLiked'] = '' . $this->General->isEventLiked($event['id'], 1);
                $event['totalLikes'] = '' . $this->General->totalEventLikes($event['id'], 1);
                $event['totalComments'] = '' . $this->General->totalEventComments($event['id'], 1);


                /**
                 * Venue Detail 
                 */
                //            pr($seatgeekEvent->venue);die;
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
            } else {

                $this->Event->bindModel(array('belongsTo' => array('User' => array('fields' => array('User.id', 'User.fullname', 'User.first_name', 'User.last_name', 'User.image')))));

                //$peggdEventId = $eventId;
                $eventDetail = $this->Event->find('first', array('conditions' => array('Event.id' => $peggdEventId)));
                //            pr($eventDetail);die;

                if (empty($eventDetail)) {
                    $arr = array('replyCode' => 'error', 'replyMsg' => $this->General->getMessage('event_not_found'));
                    echo $this->Json->encode($arr);
                    exit;
                }

                if (!empty($eventDetail)) {

                    $dateTime = $this->General->less5hours($eventDetail['Event']['start_date'], 'F d, Y');
                    $eventdateTime = $this->General->less5hours($eventDetail['Event']['start_date'], 'F d, Y h:i A');

                    $event['id'] = '' . $eventDetail['Event']['id'];
                    $event['title'] = '' . stripcslashes($eventDetail['Event']['name']);
                    $event['category'] = $this->General->getCategoryName($eventDetail['Event']['category_id']);
                    //$event['event_date'] = date('F d, Y', strtotime($eventDetail['Event']['start_date']));
                    //$event['event_time'] = date('F d, Y h:i A', strtotime($eventDetail['Event']['start_date']));
                    $event['event_date'] = $dateTime;
                    $event['event_time'] = $eventdateTime;

                    $event['eventPicture'] = $this->General->getUploadImage($eventDetail['Event']['image'], 'events', 640, 360);
                    $event['isSeatgeek'] = '0';
                    $event['user_id'] = (!empty($eventDetail['User']['id'])) ? $eventDetail['User']['id'] : '0';
                    $event['isMyEvent'] = ($currentUser == $eventDetail['Event']['user_id']) ? '1' : '0';
                    $event['username'] = '' . $eventDetail['User']['first_name'] . ' ' . $eventDetail['User']['last_name'];
                    $userImage = (!empty($eventDetail['User']['image'])) ? $eventDetail['User']['image'] : '';
                    $event['user_picture'] = $this->General->getUploadImage($userImage, 'users', 100, 100);
                    $event['isCalendar'] = '' . $this->General->isCalendar($event['id'], 0);
                    $event['isCommented'] = '' . $this->General->isCommented($event['id'], 0);
                    $event['isLiked'] = '' . $this->General->isEventLiked($eventDetail['Event']['id'], 0);
                    $event['totalLikes'] = '' . $this->General->totalEventLikes($eventDetail['Event']['id'], 0);
                    $event['totalComments'] = '' . $this->General->totalEventComments($eventDetail['Event']['id'], 0);


                    /**
                     * Venue Detail 
                     */
                    $venue = array();
                    $venue['id'] = '' . $eventDetail['Venue']['id'];
                    $venue['name'] = '' . stripcslashes($eventDetail['Venue']['name']);
                    $venue['address'] = '' . $eventDetail['Venue']['address'];
                    $venue['extended_address'] = '' . $eventDetail['Venue']['extended_address'];
                    $venue['city'] = '' . $eventDetail['Venue']['city'];
                    $venue['country'] = '' . $eventDetail['Venue']['country'];
                    $venue['postal_code'] = '' . $eventDetail['Venue']['post_code'];
                    $venue['lat'] = '' . $eventDetail['Venue']['lat'];
                    $venue['long'] = '' . $eventDetail['Venue']['long'];
                    $event['venue'] = $venue;
                }
            }



            //        $this->loadModel('EventLike');
            //        $eventLikes = $this->EventLike->find('all', array('conditions' => array('EventLike.event_id' => $peggdEventId, 'EventLike.user_id <>' => $currentUser), 'limit' => '10', 'recursive' => '0'));

            $this->loadModel('EventCalendar');
            $eventCalendars = $this->EventCalendar->find('all', array('conditions' => array('EventCalendar.event_id' => $peggdEventId, 'EventCalendar.user_id <>' => $currentUser, 'EventCalendar.status' => array('1', '2')), 'group' => 'EventCalendar.user_id', 'limit' => '10', 'recursive' => '0'));

            if (!empty($eventCalendars)) {
                foreach ($eventCalendars as $eventCalendar) {
                    $eventAttendee = array();
                    $eventAttendee['user_id'] = '' . $eventCalendar['User']['id'];
                    $eventAttendee['image'] = $this->General->getUploadImage($eventCalendar['User']['image'], 'users', 100, 100);
                    $eventAttendees[] = $eventAttendee;
                }
            }
        }


        $this->loadModel('EventComment');
        $eventComments = array();
        //$comments = $this->EventComment->find('all', array('conditions' => array('EventComment.event_id' => $peggdEventId), 'recursive' => '0'));

        $this->paginate = array(
            'conditions' => array('EventComment.event_id' => $peggdEventId),
            'limit' => $perPageLimit,
            'recursive' => '0',
        );
        $comments = $this->paginate('EventComment');

        if (!empty($comments)) {
            foreach ($comments as $comment) {
                $eventComment = array();
                $eventComment['id'] = '' . $comment['EventComment']['id'];
                $eventComment['comment'] = '' . $comment['EventComment']['comment_text'];
                $eventComment['created'] = $this->General->getHowLongAgo($comment['EventComment']['created']);
                $eventComment['user_id'] = '' . $comment['User']['id'];
                $eventComment['user_name'] = '' . $comment['User']['first_name'] . ' ' . $comment['User']['last_name'];
                $eventComment['user_image'] = $this->General->getUploadImage($comment['User']['image'], 'users', 100, 100);
                $eventComment['isComment'] = "1";
                $eventComments[] = $eventComment;
            }
        }


        $outPutArray['replyCode'] = 'success';
        $outPutArray['replyMsg'] = 'events listing';
        $outPutArray['total_comments'] = $this->params['paging']['EventComment']['count'] . '';
        $outPutArray['per_page'] = $perPageLimit . '';
        $outPutArray['page'] = $currentPage . '';
        $outPutArray['eventDetail'] = $event;
        $outPutArray['eventAttendees'] = $eventAttendees;
        $outPutArray['eventComments'] = $eventComments;
        $output = $this->Json->encode($outPutArray);
        echo $output;
        exit;
    }

    public function likeEvent($eventId = 0, $isSeatGeek = 0) {
        $currentUser = $this->Auth->user('id');
        if (!$eventId) {
            $outPutArray['replyCode'] = 'error';
            $outPutArray['replyMsg'] = 'Event not found';
            $output = $this->Json->encode($outPutArray);
            echo $output;
            exit;
        }

        $isLikeEvent = 0;
        $this->loadModel('EventLike');
        $outPutArray['replyCode'] = 'success';
        if ($isSeatGeek) {
            $peggdEventId = $this->General->getEventId($eventId);
            if (!$peggdEventId) {
                /**
                 * insert into our database 
                 */
                $peggdEventId = $this->General->insertEvent($eventId);
            }
        } else {
            $peggdEventId = $eventId;
        }

        $isLikeEvent = $this->General->isEventLiked($peggdEventId, 0);

        $addEventLike = array();
        $addEventLike['event_id'] = $peggdEventId;
        $addEventLike['user_id'] = $currentUser;

        if (!$isLikeEvent) {
            $addEventLike['id'] = '';
            $this->EventLike->save($addEventLike);

            ######################################   SEND PUSH NOTIFICATION      ##############################################
            // NOTE :- Member likes or dislikes your created events
            $getEventInfo = $this->Event->find('first', array('conditions' => array('Event.id' => $peggdEventId, 'Event.user_id != 0'), 'fields' => array('Event.name', 'Event.user_id'), 'recursive' => 2));
            if (!empty($getEventInfo['Event']['user_id'])) {
                if ($currentUser != $getEventInfo['Event']['user_id']) {
                    $getDeiceToken = $this->General->getDeiceToken($getEventInfo['Event']['user_id']);

                    if (!empty($getDeiceToken)) {
                        $eventName = $this->General->subStingWords(stripcslashes($getEventInfo['Event']['name']));
                        $message = $this->Auth->user('fullname') . " liked " . $eventName;
                        $type = "isLikeCreated";
                        $this->General->iphone_push_notification($getDeiceToken, $message, $type, '', $getEventInfo['Event']['user_id'], $type);
                    }
                }
            }
            ######################################   END PUSH NOTIFICATION CODE  ##############################################

            $outPutArray['replyMsg'] = 'Event liked successfully';
        } else {
            $this->EventLike->deleteAll(array('EventLike.event_id' => $peggdEventId, 'EventLike.user_id' => $currentUser));
            $outPutArray['replyMsg'] = 'Event Unliked successfully';
        }

        $output = $this->Json->encode($outPutArray);
        echo $output;
        exit;
    }

    public function like_event($eventId = 0, $isSeatGeek = 0) {

        //pr($this->request->data); die;
        $this->layout = false;
        $this->autoRender = false;
        $currentUser = $this->Auth->user('id');
        if (isset($this->request->data['eventId']) && $this->request->data['eventId'] != '') {
            $eventId = $this->request->data['eventId'];
        }
        if (isset($this->request->data['isSeatGeek']) && $this->request->data['isSeatGeek'] != '') {
            $isSeatGeek = $this->request->data['isSeatGeek'];
        }
        if (!$eventId) {
            $outPutArray['replyCode'] = 'error';
            $outPutArray['replyMsg'] = 'Event not found';
            $output = $this->Json->encode($outPutArray);
            echo $output;
            exit;
        }

        $isLikeEvent = 0;
        $this->loadModel('EventLike');
        $outPutArray['replyCode'] = 'success';
        if ($isSeatGeek) {
            //pr($this->request->data); die;
            $peggdEventId = $this->General->getEventId($eventId);
            if (!$peggdEventId) {
                /**
                 * insert into our database 
                 */
                $peggdEventId = $this->General->insertEvent($eventId);
            }
        } else {
            $peggdEventId = $eventId;
        }

        $isLikeEvent = $this->General->isEventLiked($peggdEventId, 0);

        $addEventLike = array();
        $addEventLike['event_id'] = $peggdEventId;
        $addEventLike['user_id'] = $currentUser;

        if (!$isLikeEvent) {
            $addEventLike['id'] = '';
            $this->EventLike->save($addEventLike);

            ######################################   SEND PUSH NOTIFICATION      ##############################################
            // NOTE :- Member likes or dislikes your created events
            $getEventInfo = $this->Event->find('first', array('conditions' => array('Event.id' => $peggdEventId, 'Event.user_id != 0'), 'fields' => array('Event.name', 'Event.user_id'), 'recursive' => 2));
            if (!empty($getEventInfo['Event']['user_id'])) {
                if ($currentUser != $getEventInfo['Event']['user_id']) {
                    $getDeiceToken = $this->General->getDeiceToken($getEventInfo['Event']['user_id']);

                    if (!empty($getDeiceToken)) {
                        $eventName = $this->General->subStingWords(stripcslashes($getEventInfo['Event']['name']));
                        $message = $this->Auth->user('fullname') . " liked " . $eventName;
                        $type = "isLikeCreated";
                        $this->General->iphone_push_notification($getDeiceToken, $message, $type, '', $getEventInfo['Event']['user_id'], $type);
                    }
                }
            }
            ######################################   END PUSH NOTIFICATION CODE  ##############################################
            $eventCount = 0;
            $eventCount = $this->EventLike->find('count', array('conditions' => array('EventLike.event_id' => $peggdEventId)));

            $outPutArray['eventCount'] = $eventCount;
            $outPutArray['replyMsg'] = 'Event liked successfully';
        } else {

            $this->EventLike->deleteAll(array('EventLike.event_id' => $peggdEventId, 'EventLike.user_id' => $currentUser));

            $eventCount = 0;
            $eventCount = $this->EventLike->find('count', array('conditions' => array('EventLike.event_id' => $peggdEventId)));
            $outPutArray['eventCount'] = $eventCount;
            $outPutArray['replyMsg'] = 'Event Unliked successfully';
        }

        $output = $this->Json->encode($outPutArray);
        echo $output;
        exit;
    }

    public function commentEvent($eventId = 0, $isSeatGeek = 0) {

        $currentUser = $this->Auth->user('id');
        if (!$eventId || !isset($_POST['comment']) || $_POST['comment'] == '') {
            $outPutArray['replyCode'] = 'error';
            $outPutArray['replyMsg'] = 'Event not found';
            $output = $this->Json->encode($outPutArray);
            echo $output;
            exit;
        }

        $commentText = $_POST['comment'];
        $this->loadModel('EventComment');

        if ($isSeatGeek) {
            $peggdEventId = $this->General->getEventId($eventId);
            if (!$peggdEventId) {
                /**
                 * insert into our database 
                 */
                $peggdEventId = $this->General->insertEvent($eventId);
            }
        } else {
            $peggdEventId = $eventId;
        }


        $addEventComment = array();
        $addEventComment['id'] = '';
        $addEventComment['event_id'] = $peggdEventId;
        $addEventComment['user_id'] = $currentUser;
        $addEventComment['comment_text'] = $commentText;
        $this->EventComment->save($addEventComment);


        ######################################   SEND PUSH NOTIFICATION      ##############################################
        // NOTE :- Member comments on your created events
        $getEventInfo = $this->Event->find('first', array('conditions' => array('Event.id' => $peggdEventId, 'Event.user_id != 0'), 'fields' => array('Event.name', 'Event.user_id'), 'recursive' => 2));
        if (!empty($getEventInfo['Event']['user_id'])) {
            if ($currentUser != $getEventInfo['Event']['user_id']) {
                $getDeiceToken = $this->General->getDeiceToken($getEventInfo['Event']['user_id']);

                if (!empty($getDeiceToken)) {
                    $eventName = $this->General->subStingWords(stripcslashes($getEventInfo['Event']['name']));
                    $message = $this->Auth->user('fullname') . " commented on " . $eventName;
                    $type = "isCommentCreated";
                    $this->General->iphone_push_notification($getDeiceToken, $message, $type, '', $getEventInfo['Event']['user_id'], $type);
                }
            }
        }


        ############################################
        ############################################
        ############################################
        // NOTE :- Member comments on your liked events
        $this->loadModel('EventLike');
        $getEventInfo = $this->EventLike->find('all', array('conditions' => array('EventLike.event_id' => $peggdEventId), 'fields' => array('Event.name', 'EventLike.user_id'), 'recursive' => 2));
        foreach ($getEventInfo as $value) {
            if (!empty($value['Event']['name'])) {
                if ($currentUser != $value['EventLike']['user_id']) {
                    $getDeiceToken = $this->General->getDeiceToken($value['EventLike']['user_id']);

                    if (!empty($getDeiceToken)) {
                        $eventName = $this->General->subStingWords(stripcslashes($value['Event']['name']));
                        $message = $this->Auth->user('fullname') . " commented on " . $eventName;
                        $type = "isCommentLiked";
                        $this->General->iphone_push_notification($getDeiceToken, $message, $type, '', $value['EventLike']['user_id'], $type);
                    }
                }
            }
        }
        ######################################   END PUSH NOTIFICATION CODE  ##############################################


        $outPutArray['replyCode'] = 'success';
        $outPutArray['replyMsg'] = 'Comment posted successfully';
        $output = $this->Json->encode($outPutArray);
        echo $output;
        exit;
    }

    public function comment_event() {
        $this->layout = false;
        $this->autoRender = false;
        // pr($this->request->data);die;
        if (isset($this->request->data['eventId']) && $this->request->data['eventId'] != '') {
            $eventId = $this->request->data['eventId'];
        }
        if (isset($this->request->data['isSeatGeek']) && $this->request->data['isSeatGeek'] != '') {
            $isSeatGeek = $this->request->data['isSeatGeek'];
        }
        if (isset($this->request->data['commentTxt']) && $this->request->data['commentTxt'] != '') {
            $commentText = $this->request->data['commentTxt'];
        }
        $currentUser = 0;
        if ($this->Auth->user('id') != '') {
            $currentUser = $this->Auth->user('id');
        }

        if (!$eventId || !isset($commentText) || $commentText == '') {
            $outPutArray['replyCode'] = 'error';
            $outPutArray['replyMsg'] = 'Event not found';
            $output = $this->Json->encode($outPutArray);
            echo $output;
            exit;
        }


        $this->loadModel('EventComment');

        if ($isSeatGeek) {
            $peggdEventId = $this->General->getEventId($eventId);
            if (!$peggdEventId) {
                /**
                 * insert into our database 
                 */
                $peggdEventId = $this->General->insertEvent($eventId);
            }
        } else {
            $peggdEventId = $eventId;
        }


        $addEventComment = array();
        $addEventComment['id'] = '';
        $addEventComment['event_id'] = $peggdEventId;
        $addEventComment['user_id'] = $currentUser;
        $addEventComment['comment_text'] = $commentText;
        $this->EventComment->save($addEventComment);


        ######################################   SEND PUSH NOTIFICATION      ##############################################
        // NOTE :- Member comments on your created events
        $getEventInfo = $this->Event->find('first', array('conditions' => array('Event.id' => $peggdEventId, 'Event.user_id != 0'), 'fields' => array('Event.name', 'Event.user_id'), 'recursive' => 2));
        if (!empty($getEventInfo['Event']['user_id'])) {
            if ($currentUser != $getEventInfo['Event']['user_id']) {
                $getDeiceToken = $this->General->getDeiceToken($getEventInfo['Event']['user_id']);

                if (!empty($getDeiceToken)) {
                    $eventName = $this->General->subStingWords(stripcslashes($getEventInfo['Event']['name']));
                    $message = $this->Auth->user('fullname') . " commented on " . $eventName;
                    $type = "isCommentCreated";
                    $this->General->iphone_push_notification($getDeiceToken, $message, $type, '', $getEventInfo['Event']['user_id'], $type);
                }
            }
        }


        ############################################
        ############################################
        ############################################
        // NOTE :- Member comments on your liked events
        $this->loadModel('EventLike');
        $getEventInfo = $this->EventLike->find('all', array('conditions' => array('EventLike.event_id' => $peggdEventId), 'fields' => array('Event.name', 'EventLike.user_id'), 'recursive' => 2));
        foreach ($getEventInfo as $value) {
            if (!empty($value['Event']['name'])) {
                if ($currentUser != $value['EventLike']['user_id']) {
                    $getDeiceToken = $this->General->getDeiceToken($value['EventLike']['user_id']);

                    if (!empty($getDeiceToken)) {
                        $eventName = $this->General->subStingWords(stripcslashes($value['Event']['name']));
                        $message = $this->Auth->user('fullname') . " commented on " . $eventName;
                        $type = "isCommentLiked";
                        $this->General->iphone_push_notification($getDeiceToken, $message, $type, '', $value['EventLike']['user_id'], $type);
                    }
                }
            }
        }
        ######################################   END PUSH NOTIFICATION CODE  ##############################################


        $outPutArray['replyCode'] = 'success';
        $outPutArray['replyMsg'] = 'Comment posted successfully';
        $output = $this->Json->encode($outPutArray);
        echo $output;
        exit;
    }

    public function allAttendee($eventId = 0, $isSeatGeek = 0) {

        $currentUser = $this->Auth->user('id');
        if (!$eventId) {
            $outPutArray['replyCode'] = 'error';
            $outPutArray['replyMsg'] = 'Event not found';
            $output = $this->Json->encode($outPutArray);
            echo $output;
            exit;
        }

        if ($isSeatGeek) {
            $peggdEventId = $this->General->getEventId($eventId);
        } else {
            $peggdEventId = $eventId;
        }

        $eventAttendees = array();

//        $this->loadModel('EventLike');
//        $eventLikes = $this->EventLike->find('all', array('conditions' => array('EventLike.event_id' => $peggdEventId, 'EventLike.user_id <>' => $currentUser), 'recursive' => '0'));

        $this->loadModel('EventCalendar');
        $eventCalendars = $this->EventCalendar->find('all', array('conditions' => array('EventCalendar.event_id' => $peggdEventId, 'EventCalendar.user_id <>' => $currentUser, 'EventCalendar.status' => array('1', '2'))));

//        $eventLikes = $this->EventComment->find('all', array('conditions' => array('EventComment.event_id' => $peggdEventId, 'EventComment.user_id <>' => $currentUser), 'group'=>'EventComment.user_id', 'recursive' => '0'));
        if (!empty($eventCalendars)) {
            foreach ($eventCalendars as $eventCalendar) {
                $eventAttendee = array();
                $eventAttendee['user_id'] = '' . $eventCalendar['User']['id'];
                $eventAttendee['user_name'] = '' . $eventCalendar['User']['first_name'] . ' ' . $eventCalendar['User']['last_name'];
                ;
                $eventAttendee['image'] = $this->General->getUploadImage($eventCalendar['User']['image'], 'users', 100, 100);
                $eventAttendees[] = $eventAttendee;
            }
        }

        $outPutArray['replyCode'] = 'success';
        $outPutArray['replyMsg'] = 'Event Attendees';
        $outPutArray['eventAttendees'] = $eventAttendees;
        $output = $this->Json->encode($outPutArray);
        echo $output;
        exit;
    }

    function printCategory($catId = 0) {
        $this->loadModel('Category');

        $this->showCat();

        exit;
    }

    function showCat($catId = 0) {
        $this->loadModel('Category');
        $categories = $this->Category->find('all', array('conditions' => array('Category.parent_id' => $catId)));

        echo "<ul>";
        foreach ($categories as $category) {
            echo "<li>" . $category['Category']['name'];
            $this->showCat($category['Category']['category_id']);
            echo "</li>";
        }
        echo "</ul>";
//          exit;
    }

    /**
     * add event
     * http://support24hour.com/workplace2/peggd/events/addEvent/sid:MjQ
     * http://192.168.0.122/peggd/events/addEvent/sid:MjQ
     * params: name, event_date, category_id, image, lat, long , isInviteFriend=(1/0), isPublic=(1/0)
     * others:  
     */
    public function addEvent() {

        if (empty($_POST['name']) || empty($_POST['event_date']) || empty($_POST['category_id']) || empty($_POST['lat']) || empty($_POST['long'])) {
            $arr = array('replyCode' => 'error', 'replyMsg' => $this->General->getMessage('please_fill_complete_details'));
            echo $this->Json->encode($arr);
            exit;
        }

        $this->loadModel('Venue');
        $currentUser = $this->Auth->user('id');


        $venueInfo = $this->Venue->find('first', array('conditions' => array('Venue.name LIKE' => '%' . $_POST['name'] . '%', 'Venue.lat LIKE' => '%' . $_POST['lat'] . '%', 'Venue.long LIKE' => '%' . $_POST['long'] . '%')));



        $image = '';
        //upload profile image 
        if (!empty($_FILES['image'])) {

            $file = $_FILES['image'];
            $imgName = time() . '_' . $file['name'];
            $destination = realpath('../../app/webroot/uploads/events') . '/';

            if (empty($venueInfo['Venue']['id'])) {
                $destination_venue = realpath('../../app/webroot/uploads/venues') . '/';
                copy($file['tmp_name'], $destination_venue . $imgName);
            }

            if (move_uploaded_file($file['tmp_name'], $destination . $imgName)) {
                //copy image
                $image = $imgName;
            } else {
                unset($_FILES['image']);
            }
        }

        if (!empty($venueInfo['Venue']['id'])) {
            $venue_id = $venueInfo['Venue']['id'];
        } else {
            //save new venue
            $venue_id = $this->addVenue($_POST, $image);
        }

        $event_date = date('Y-m-d H:i:s', strtotime($_POST['event_date']));
        $event = array();
        $event['Event']['name'] = $_POST['name'];
        $event['Event']['image'] = $image;
        $event['Event']['start_date'] = $this->General->eventInsertDateTime($event_date);
        $event['Event']['end_date'] = $this->General->eventInsertDateTime($event_date);
        $event['Event']['category_id'] = $_POST['category_id'];
        $event['Event']['venue_id'] = $venue_id;
        $event['Event']['user_id'] = $currentUser;
        $event['Event']['status'] = 1;
        $event['Event']['isInviteFriend'] = (!empty($_POST['isInviteFriend'])) ? $_POST['isInviteFriend'] : 0;
        $event['Event']['isPublic'] = (!empty($_POST['isPublic'])) ? $_POST['isPublic'] : 0;

        //save event
        if ($this->Event->save($event)) {
            $event_id = $this->Event->getLastInsertId();


            // SAVE THIS EVENT INTO CALENDAR
            $this->loadModel('EventCalendar');
            //if event already added into user calender
            $eventAlreadyAdded = $this->EventCalendar->find('first', array('conditions' => array('EventCalendar.event_id' => $event_id, 'EventCalendar.user_id' => $currentUser)));
            if ($eventAlreadyAdded['EventCalendar']['status'] == 0) {
                $this->EventCalendar->id = $eventAlreadyAdded['EventCalendar']['id'];
                $infoArr['event_id'] = $event_id;
                $infoArr['user_id'] = $currentUser;
                $infoArr['status'] = 1;
                $this->EventCalendar->save($infoArr);
            }


            ######################################   SEND PUSH NOTIFICATION      ##############################################
            // NOTE :- send push to followers when user create public event
            if (!empty($_POST['isPublic'])) {
                $this->loadModel('Follower');

                //$getFollowers = $this->Follower->find('list', array('conditions'=>array('Follower.follower_id'=>$currentUser, 'User.device_token !='=>""), 'fields'=>array('User.device_token'), 'recursive'=>1 ));
                $getFollowers = $this->Follower->find('all', array('conditions' => array('Follower.follower_id' => $currentUser, 'User.device_token !=' => ""), 'fields' => array('User.id', 'User.device_token'), 'recursive' => 1));
                //pr($getFollowers);
                foreach ($getFollowers as $value) {
                    //$getDeiceToken = $value;
                    $getDeiceToken = $value['User']['device_token'];

                    if (!empty($getDeiceToken)) {
                        $eventName = $this->General->subStingWords($_POST['name']);
                        $message = $this->Auth->user('fullname') . " created " . $eventName;
                        $type = "isFollowingEvent";
                        $this->General->iphone_push_notification($getDeiceToken, $message, $type, '', $value['User']['id'], $type);
                    }
                }
            }


            ############################################
            ############################################
            ############################################
            // NOTE :- when new event create in a category and user belongs to that category then he got push
            $categoryUsers = $this->User->find('list', array('conditions' => array('User.category_id LIKE ' => '%' . $_POST['category_id'] . '%', 'User.device_token != ' => ""), 'fields' => array('User.device_token'), 'reqursive' => -1));
            //pr($categoryUsers);die;
            $eventName = $this->General->subStingWords($_POST['name']);
            $categoryName = $this->General->getSiteCategoryNameById($_POST['category_id']);

            foreach ($categoryUsers as $key => $value) {
                //$getDeiceToken = $this->General->getDeiceToken($value['EventLike']['user_id']);
                $getDeiceToken = $value;
                if ($currentUser != $key) {
                    if (!empty($getDeiceToken)) {
                        $message = $eventName . " is added for " . $categoryName;
                        $type = "isSpecific";
                        $this->General->iphone_push_notification($getDeiceToken, $message, $type, '', $key, $type);
                    }
                }
            }
            ######################################   END PUSH NOTIFICATION CODE  ##############################################


            $arr = array('replyCode' => 'success', 'replyMsg' => $this->General->getMessage('the_event_has_been_saved_successfully'), 'event_id' => $event_id);
        } else {
            $arr = array('replyCode' => 'error', 'replyMsg' => $this->General->getMessage('the_event_could_not_be_saved_Please_try_again'));
        }

        echo $this->Json->encode($arr);
        exit;
    }

    function addVenue_old($venue, $image = null) {

        if (!empty($venue)) {

            //get country state and address
            $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $venue['lat'] . "," . $venue['long'] . "&sensor=false";
            $result = file_get_contents($url);
            $first_key = $this->Json->decode($result);
            //pr($first_key);die;
            $result = '';
            if (!empty($first_key->results[0]->address_components))
                $result = $first_key->results[0]->address_components;

            $venueAdd['Venue']['name'] = $_POST['name'];
            $venueAdd['Venue']['image'] = $image;
            $venueAdd['Venue']['lat'] = $_POST['lat'];
            $venueAdd['Venue']['long'] = $_POST['long'];
            $venueAdd['Venue']['status'] = 1;
            if (!empty($first_key->results[0]->formatted_address))
                $venueAdd['Venue']['address'] = $first_key->results[0]->formatted_address;
            if (!empty($first_key->results[0]->formatted_address))
                $venueAdd['Venue']['extended_address'] = $first_key->results[0]->formatted_address;
            if (!empty($result[5]->short_name))
                $venueAdd['Venue']['post_code'] = $result[5]->short_name;
            if (!empty($result[2]->short_name))
                $venueAdd['Venue']['city'] = $result[2]->short_name;
            if (!empty($result[3]->long_name))
                $venueAdd['Venue']['state'] = $result[3]->long_name;
            if (!empty($result[4]->short_name))
                $venueAdd['Venue']['country'] = $result[4]->short_name;
            //pr($venueAdd);die;
            //save venue
            $this->Venue->create();
            if ($this->Venue->save($venueAdd))
                return $this->Venue->id;
            else
                return 0;
        }

        return 0;
    }

    function addVenue($venue, $image = null) {

        if (!empty($venue)) {

            //get country state and address
            $geomapAddress = $this->Geomap->getGeomapAddress($venue['lat'], $venue['long']);
            $venueAdd['Venue']['address'] = $geomapAddress['address'];
            $venueAdd['Venue']['extended_address'] = $geomapAddress['extended_address'];
            $venueAdd['Venue']['post_code'] = $geomapAddress['postal_code'];
            $venueAdd['Venue']['city'] = $geomapAddress['city'];
            $venueAdd['Venue']['state'] = $geomapAddress['state'];
            $venueAdd['Venue']['country'] = $geomapAddress['country']['short_name'];

            $venueAdd['Venue']['name'] = $_POST['name'];
            $venueAdd['Venue']['image'] = $image;
            $venueAdd['Venue']['lat'] = $_POST['lat'];
            $venueAdd['Venue']['long'] = $_POST['long'];
            $venueAdd['Venue']['status'] = 1;

            //pr($venueAdd);die;
            //save venue
            $this->Venue->create();
            if ($this->Venue->save($venueAdd))
                return $this->Venue->id;
            else
                return 0;
        }

        return 0;
    }

    function test($lat = '134.049999', $long = '-118.250000') {
        $this->layout = false;
        $this->autoRender = false;
        $address = $this->Geomap->geo_address($lat);
        pr($address);
        die("dsh");
    }

    /**
     * for get (address, extended_address, post_code, city, state, country) using google geocode place api by passing lat, long
     * @param type $venue
     * @param type $image 
     */
    function getGeocodeAddress($geocode_stats = null) {
        //http://maps.googleapis.com/maps/api/geocode/json?latlng=51.47144,-0.21101&sensor=true
        //http://maps.googleapis.com/maps/api/geocode/json?latlng=-37.84237,144.94707&sensor=true

        $city = "";


        $geocode_stats = @file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $lat . "," . $long . "&sensor=false");
        $output = json_decode($geocode_stats);

        if (isset($output->results)) {
            $cityGeoCodeArr = $output->results;
//                pr($cityGeoCodeArr);die;
            foreach ($cityGeoCodeArr as $singleCityGeoCode) {
                if (isset($singleCityGeoCode->types)) {
                    $addressComponentTypes = $singleCityGeoCode->types;
                    foreach ($addressComponentTypes as $addrTypeObj) {
                        if ($addrTypeObj == 'street_address') {
                            if (isset($singleCityGeoCode->address_components)) {
                                $addressComponents = $singleCityGeoCode->address_components;
                                $singleTypeArr = array();
                                foreach ($addressComponents as $singleAddrComponent) {
                                    //pr($addressComponents);die;
                                    $singleTypeArr = $singleAddrComponent->types;
                                    if (!empty($singleTypeArr)) {
                                        foreach ($singleTypeArr as $singleType) {
                                            if ($singleType == 'locality') {
                                                $city = $singleAddrComponent->long_name;
                                                break;
                                            }

                                            if ($city != "") {
                                                break;
                                            }
                                        }
                                    }
                                    if ($city != "") {
                                        break;
                                    }
                                }
                            }

                            if ($city != "") {
                                break;
                            }
                        } elseif ($addrTypeObj == 'locality') {


                            if (isset($singleCityGeoCode->address_components)) {
                                $addressComponents = $singleCityGeoCode->address_components;
                                $singleTypeArr = array();
                                foreach ($addressComponents as $singleAddrComponent) {
                                    //pr($singleAddrComponent);die;
                                    $singleTypeArr = $singleAddrComponent->types;
                                    if (!empty($singleTypeArr)) {
                                        foreach ($singleTypeArr as $singleType) {
                                            if ($singleType == 'locality') {
                                                $city = $singleAddrComponent->short_name;
                                                break;
                                            }
                                            if ($city != "") {
                                                break;
                                            }
                                        }
                                    }
                                    if ($city != "") {
                                        break;
                                    }
                                }
                            }
                        } elseif ($addrTypeObj == 'country') {
                            if (isset($singleCityGeoCode->address_components)) {
                                $addressComponents = $singleCityGeoCode->address_components;
                                $singleTypeArr = array();
                                foreach ($addressComponents as $singleAddrComponent) {
                                    //pr($singleAddrComponent);die;
                                    $singleTypeArr = $singleAddrComponent->types;
                                    if (!empty($singleTypeArr)) {
                                        foreach ($singleTypeArr as $singleType) {
                                            if ($singleType == 'locality') {
                                                $city = $singleAddrComponent->short_name;
                                                break;
                                            }
                                            if ($city != "") {
                                                break;
                                            }
                                        }
                                    }
                                    if ($city != "") {
                                        break;
                                    }
                                }
                            }
                        }
                        if ($city != "") {
                            break;
                        }
                    }
                }
                if ($city != "") {
                    break;
                }
            }
        }
    }

    /**
     * peggd events listing
     * http://192.168.0.79/peggd/events/peggdEvents/0/sid:Mjc/page:1
     * params: user id
     * others: 
     */
    public function peggdEvents($currentUser = null) {
        $conditions = array();
        if (empty($currentUser)) {
            $currentUser = $this->Auth->user('id');
        } else {
            $conditions['Event.isPublic'] = '1';
        }
//        $conditions = array('Event.seatgeek_id' => 0, 'Event.user_id' => $currentUser, 'Event.end_date > ' => date('Y-m-d H:i:s'));
        $conditions = array('Event.seatgeek_id' => 0, 'Event.user_id' => $currentUser);
        $limit = 5;

        $this->Event->unbindModel(array('belongsTo' => array('Venue')));
        $this->Event->bindModel(
        array(
            'belongsTo' => array(
                'User' => array(
                    'fields' => array('User.id', 'User.fullname', 'User.image')
                )
            )
        )
        );

        $this->paginate = array(
            'conditions' => $conditions,
            'order' => 'Event.end_date ASC',
            'limit' => $limit,
        );

        $events = $this->paginate('Event');


        $data = array();
        foreach ($events as $value) {

            $eventImage = $this->General->getUploadImage($value['Event']['image'], 'events', 632, 248, 1, 'events/noimage_listing.png');
            $user_image = $this->General->getUploadImage($value['User']['image'], 'users', 80, 80);

            $data[] = array(
                'event_id' => '' . $value['Event']['id'],
                'image' => $eventImage,
                'name' => '' . stripcslashes($value['Event']['name']),
                'category_id' => '' . $value['Event']['category_id'],
                'date' => date('F d, Y', strtotime($value['Event']['end_date'])),
                'isInviteFriend' => '' . $value['Event']['isInviteFriend'],
                'isPublic' => '' . $value['Event']['isPublic'],
                'isSeatgeek' => '0',
                'user_name' => (!empty($value['User']['fullname'])) ? $value['User']['fullname'] : '',
                'user_image' => $user_image,
            );
        }


        $outPutArray['replyCode'] = 'success';
        $outPutArray['replyMsg'] = 'peggd events';
        $outPutArray['total'] = $this->params['paging']['Event']['count'];
        $outPutArray['per_page'] = $limit;
        $outPutArray['page'] = (!empty($this->params['named']['page'])) ? $this->params['named']['page'] : 1;
        $outPutArray['data'] = $data;
        echo $this->Json->encode($outPutArray);
        exit;
    }

    /**
     * delete event from event table
     * http://192.168.0.122/peggd/events/deleteEvent/2/sid:MjQ
     * params: eventId
     * others: 
     */
    function deleteEvent($eventId = null) {

        if (empty($eventId)) {
            $arr = array('replyCode' => 'error', 'replyMsg' => $this->General->getMessage('please_fill_complete_details'));
            echo $this->Json->encode($arr);
            exit;
        }

//        $currentUser = $this->Auth->user('id');

        $this->Event->id = $eventId;

        $eventInfo = $this->Event->find('first', array('conditions' => array('Event.id' => $eventId), 'fields' => 'Event.image'));
        if ($this->Event->delete()) {
            //unlink event & venue image
            @unlink('uploads/events/' . $eventInfo['Event']['image']);
            @unlink('uploads/venues/' . $eventInfo['Event']['image']);

            //delete all entries from comments/likes/calander events
            $this->loadModel('EventCalendar');
            $this->loadModel('EventComment');
            $this->loadModel('EventLike');

            $this->EventCalendar->deleteAll(array('event_id' => $eventId));
            $this->EventComment->deleteAll(array('event_id' => $eventId));
            $this->EventLike->deleteAll(array('event_id' => $eventId));

            $arr = array('replyCode' => 'success', 'replyMsg' => $this->General->getMessage('the_event_has_been_deleted_successfully'));
        } else {
            $arr = array('replyCode' => 'error', 'replyMsg' => $this->General->getMessage('the_event_could_not_be_deleted_Please_try_again'));
        }

        echo $this->Json->encode($arr);
        exit;
    }

    /**
     * get event
     * http://192.168.0.122/peggd/events/getEvent/10/sid:MjQ
     * params: eventId
     * others: 
     */
    function getEvent($eventId = null) {

        if (empty($eventId)) {
            $arr = array('replyCode' => 'error', 'replyMsg' => $this->General->getMessage('please_fill_complete_details'));
            echo $this->Json->encode($arr);
            exit;
        }

        $this->Event->unbindModel(array('belongsTo' => array('Venue')));
        $this->Event->bindModel(
        array(
            'belongsTo' => array(
                'User' => array(
                    'fields' => array('User.id', 'User.fullname', 'User.image')
                ),
                'Venue' => array(
                    'fields' => array('Venue.id', 'Venue.extended_address', 'Venue.lat', 'Venue.long')
                ),
            )
        )
        );

        $events = $this->Event->find('all', array('conditions' => array('Event.id' => $eventId)));
        //pr($events);die;

        $data = array();
        foreach ($events as $value) {

            $eventImage = $this->General->getUploadImage($value['Event']['image'], 'events', 632, 248, 1, 'events/noimage_listing.png');
            $user_image = $this->General->getUploadImage($value['User']['image'], 'users', 80, 80);
            $eventcategory = $this->General->getCategoryName($value['Event']['category_id']);

            $data = array(
                'event_id' => '' . $value['Event']['id'],
                'image' => $eventImage,
                'name' => '' . stripcslashes($value['Event']['name']),
                'category_id' => '' . $value['Event']['category_id'],
                'category_name' => '' . $eventcategory,
                'date' => date('F d, Y', strtotime($value['Event']['end_date'])),
                'isInviteFriend' => '' . $value['Event']['isInviteFriend'],
                'isPublic' => '' . $value['Event']['isPublic'],
                'isSeatgeek' => '0',
                'user_name' => '' . $value['User']['fullname'],
                'location' => '' . $value['Venue']['extended_address'],
                'lat' => '' . $value['Venue']['lat'],
                'long' => '' . $value['Venue']['long'],
            );
        }


        $outPutArray['replyCode'] = 'success';
        $outPutArray['replyMsg'] = 'event detail';
        $outPutArray['data'] = $data;
        echo $this->Json->encode($outPutArray);
        exit;
    }

    /**
     * edit event
     * http://192.168.0.122/peggd/events/editEvent/2/sid:MjQ
     * params: name, event_date, category_id, image, lat, long , isInviteFriend=(1/0), isPublic=(1/0)
     * others:  
     */
    public function editEvent($eventId = null) {


        if (empty($eventId) || empty($_POST['name']) || empty($_POST['event_date']) || empty($_POST['category_id']) || empty($_POST['lat']) || empty($_POST['long'])) {
            $arr = array('replyCode' => 'error', 'replyMsg' => $this->General->getMessage('please_fill_complete_details'));
            echo $this->Json->encode($arr);
            exit;
        }

        $this->loadModel('Venue');
        $currentUser = $this->Auth->user('id');


        $venueInfo = $this->Venue->find('first', array('conditions' => array('Venue.name LIKE' => '%' . $_POST['name'] . '%', 'Venue.lat LIKE' => '%' . $_POST['lat'] . '%', 'Venue.long LIKE' => '%' . $_POST['long'] . '%')));
//        pr($venueInfo);die;


        $image = '';
        //upload profile image 
        if (!empty($_FILES['image'])) {

            $file = $_FILES['image'];
            $imgName = time() . '_' . $file['name'];
            $destination = realpath('../../app/webroot/uploads/events') . '/';

            if (empty($venueInfo['Venue']['id'])) {
                $destination_venue = realpath('../../app/webroot/uploads/venues') . '/';
                copy($file['tmp_name'], $destination_venue . $imgName);
            }

            if (move_uploaded_file($file['tmp_name'], $destination . $imgName)) {
                //unlink old image of this venue and upload new image
                $eventInfo = $this->Event->find('first', array('conditions' => array('Event.id' => $eventId), 'fields' => 'Event.image'));
                @unlink('uploads/events/' . $venueInfo['Venue']['image']);

                //copy image
                $image = $imgName;
            } else {
                unset($_FILES['image']);
            }
        }

        if (!empty($venueInfo['Venue']['id'])) {
            $venue_id = $venueInfo['Venue']['id'];
        } else {
            //save new venue
            $venue_id = $this->addVenue($_POST, $image);
        }

        $event_date = date('Y-m-d H:i:s', strtotime($_POST['event_date']));
        $event = array();
        $event['Event']['id'] = $eventId;
        $event['Event']['name'] = $_POST['name'];
        if (!empty($image))
            $event['Event']['image'] = $image;
        $event['Event']['start_date'] = $event_date;
        $event['Event']['end_date'] = $event_date;
        $event['Event']['category_id'] = $_POST['category_id'];
        $event['Event']['venue_id'] = $venue_id;
        $event['Event']['user_id'] = $currentUser;
        $event['Event']['status'] = 1;
        $event['Event']['isInviteFriend'] = (!empty($_POST['isInviteFriend'])) ? $_POST['isInviteFriend'] : 0;
        $event['Event']['isPublic'] = (!empty($_POST['isPublic'])) ? $_POST['isPublic'] : 0;
        //pr($event);die;
        //save event
        if ($this->Event->save($event)) {
            $arr = array('replyCode' => 'success', 'replyMsg' => $this->General->getMessage('the_event_has_been_updated_successfully'));
        } else {
            $arr = array('replyCode' => 'error', 'replyMsg' => $this->General->getMessage('the_event_could_not_be_update_Please_try_again'));
        }

        echo $this->Json->encode($arr);
        exit;
    }

    //http://support24hour.com/workplace2/peggd/events/track/1716575
    //http://192.168.0.122/peggd/events/track/1716575
    function track($eventId = 0) {
        //get peggd event id
        $peggdEventId = $this->General->getEventId($eventId);
        if (!$peggdEventId) {
            // insert into our database 
            $peggdEventId = $this->General->insertEvent($eventId);
        }

        $eventInfo = $this->Event->find('first', array('conditions' => array('Event.id' => $peggdEventId), 'fields' => array('Event.seatgeek_id', 'Event.track_count')));

        //update event track count
        $this->Event->id = $peggdEventId;
        $track_count = $eventInfo['Event']['track_count'] + 1;
        $this->Event->saveField('track_count', $track_count);

        /* Save Tickit details in database */
        $this->loadmodel('TicketDetail');
        $currentUser = $this->Auth->user('id');
        if ($currentUser == "") {
            $currentUser = 0;
        }
        $data['TicketDetail']['event_id'] = $peggdEventId;
        $data['TicketDetail']['user_id'] = $currentUser;
        $data['TicketDetail']['ip_address'] = $_SERVER['REMOTE_ADDR'];
        //pr($data);die;
        $this->TicketDetail->save($data);

        /* end */
        // Fetch event details from seatgeek 
        $eventDetails = $this->General->getEventDetail($eventId);
        $seatgeekEvent = $this->Json->decode($eventDetails);
        //pr($seatgeekEvent);die;

        if (isset($seatgeekEvent->status) && $seatgeekEvent->status == 'error') {
            echo 'Event not found.';
            exit;
        }

        //redirect to seat geek event page
        $eventUrl = $seatgeekEvent->url;
        header("Location: $eventUrl");
        exit;
    }

    /* Front end start from here */

    function index($lat = 0, $long = 0, $dist = 50, $lastDate = null) {
        $search = "";
        if (isset($this->request->data['Event']['keyword']) && $this->request->data['Event']['keyword'] != "") {
            $search = $this->request->data['Event']['keyword'];
        }

        /* Dynamic Meta TItle and description */
        $pageData = $this->DynamicPage->getMetaDetails('events');

        if (isset($pageData['DynamicPage']['meta_title']) && $pageData['DynamicPage']['meta_title'] != '')
            $title_for_layout = $pageData['DynamicPage']['meta_title'];

        if (isset($pageData['DynamicPage']['meta_description']) && $pageData['DynamicPage']['meta_description'] != '')
            $metaDescription = $pageData['DynamicPage']['meta_description'];
        /* end meta work */

        $total_groups = '100';
        $this->set(compact(array('total_groups', 'search', 'title_for_layout', 'metaDescription')));
    }

    function index_old($lat = 0, $long = 0, $dist = 50, $lastDate = null) {

        $this->loadmodel("SiteCategories");
        $conditions = array('Event.status' => 1);
        $items_per_group = 24;
        $pageNo = 1;
        $search = "";
        if (isset($this->request->data['Event']['keyword']) && $this->request->data['Event']['keyword'] != "") {
            $search = $this->request->data['Event']['keyword'];
        }
        //$address = $this->Geomap->geo_address($_SERVER['REMOTE_ADDR'],'ip-city');
        //$lat = isset($address['latitude'])?$address['latitude']:0;
        //$long = isset($address['longitude'])?$address['longitude']:0;
        // pr($address); die("dsh");        
        $seatgeekResults = $this->General->getEvents($lat, $long, $dist, $pageNo, $search, '', $items_per_group);
        $resultArr = $this->Json->decode($seatgeekResults);
        // pr($resultArr->events[0]->datetime_local); die;
        $startDate = $endDate = time();
        if (isset($resultArr->events) && count($resultArr->events) > 0) {
            $event['timestamp'] = strtotime($this->General->less5hours($resultArr->events[0]->datetime_local, 'F d, Y'));
            $startDate = $event['timestamp'];
            $endDate = $event['timestamp'];
        }
        $this->Event->unbindModel(array('belongsTo' => array('Venue')));
        $this->Event->bindModel(
        array('belongsTo' => array('User' => array('fields' => array('User.id', 'User.fullname', 'User.first_name', 'User.last_name', 'User.image')), 'Venue' => array('fields' => array('Venue.id', 'Venue.lat', 'Venue.long')),)));

//get events from local database
        $peggdEvents = $this->Event->find('count', array('conditions' => array('Event.seatgeek_id' => 0, 'Event.isPublic' => 1, 'Event.status' => '1', 'Event.end_date > ' => date('Y-m-d H:i:s'), "Event.end_date BETWEEN '" . date('Y-m-d H:i:s', $startDate) . "' AND '" . date('Y-m-d H:i:s', $endDate) . "'"), 'order' => 'Event.end_date ASC',));

        $seatgeekTotal = (!empty($resultArr->meta->total)) ? $resultArr->meta->total : 0;
        $total_events = $seatgeekTotal + $peggdEvents;
        $total_groups = ceil($total_events / $items_per_group);
        $total_groups = '';
        $this->set(compact(array('total_groups', 'search')));
    }

    /* event listing page based on */

    function category() {

        $this->loadmodel("SiteCategories");
        $conditions = array('Event.status' => 1);
        $categoryId = '';
        if (isset($this->params->pass[0])) {
            if ($this->params->pass[0] != 'all') {
                $categoryId = $this->General->getSiteCategoryIdByName($this->params->pass[0]);
                if ($categoryId != "") {
                    $conditions['Event.category_id'] = $categoryId;
                }
            } elseif ($this->params->pass[0] == 'all') {
                $categoryId = 0;
            }
        }
        $items_per_group = 24;
        $events = $this->Event->find("count", array("conditions" => $conditions));


        /* Dynamic Meta TItle and description */
        $pageData = $this->DynamicPage->getMetaDetails('category_' . strtolower($this->params->pass[0]));

        if (isset($pageData['DynamicPage']['meta_title']) && $pageData['DynamicPage']['meta_title'] != '')
            $title_for_layout = $pageData['DynamicPage']['meta_title'];

        if (isset($pageData['DynamicPage']['meta_description']) && $pageData['DynamicPage']['meta_description'] != '')
            $metaDescription = $pageData['DynamicPage']['meta_description'];
        /* end meta work */

        $total_groups = ceil($events / $items_per_group);
        $this->set(compact(array('events', 'total_groups', 'categoryId', 'title_for_layout', 'metaDescription')));
    }

    /* event detail page web
      ## evenType 0 = Peggd Events , 1 = SeatGeek, 2= EventBrite

     */

    function eventdetail($eventTitle, $eventId = 0, $eventType = 1) {

        $currentUser = 0;
        if ($this->Auth->user('id') != "") {
            $currentUser = $this->Auth->user('id');
        }

        $eventDetail = array();
        $event = array();
        $eventAttendees = array();

        if ($eventType == 1) {
            $peggdEventId = $this->General->getEventId($eventId);
        } else {
            $peggdEventId = $eventId;
        }
        $perPageLimit = 3;

        $currentPage = (!empty($this->params['named']['page'])) ? $this->params['named']['page'] : '1';
        if ($currentPage == 1) {
            if ($eventType == 1) {

                /**
                 * Fetch event details from seatgeek 
                 */
                $event = $this->General->getEventDetailApi($eventId, $eventType);
            } elseif ($eventType == 2) {

                /**
                 * Fetch event details from eventbrite 
                 */
                $event = $this->General->getEventDetailApi($eventId, $eventType);
            } else {

                $this->Event->bindModel(array('belongsTo' => array('User' => array('fields' => array('User.id', 'User.fullname', 'User.first_name', 'User.last_name', 'User.image')))));
                $eventDetail = $this->Event->find('first', array('conditions' => array('Event.id' => $peggdEventId)));


                if (empty($eventDetail)) {
                    $arr = array('replyCode' => 'error', 'replyMsg' => $this->General->getMessage('event_not_found'));
                }

                if (!empty($eventDetail)) {

                    $dateTime = $this->General->less5hours($eventDetail['Event']['start_date'], 'F d, Y');
                    $eventdateTime = $this->General->less5hours($eventDetail['Event']['start_date'], 'F d, Y h:i A');
                    $eventImageMain = $eventDetail['Event']['image'];
                    $event['id'] = '' . $eventDetail['Event']['id'];
                    $event['title'] = '' . stripcslashes($eventDetail['Event']['name']);
                    $event['category'] = $this->General->getCategoryName($eventDetail['Event']['category_id']);
                    $event['event_date'] = $dateTime;
                    $event['event_time'] = $eventdateTime;
                    $event['eventPicture'] = $this->General->getUploadImage($eventDetail['Event']['image'], 'events', 640, 360);
                    $event['isSeatgeek'] = '0';
                    $event['user_id'] = (!empty($eventDetail['User']['id'])) ? $eventDetail['User']['id'] : '0';
                    $event['isMyEvent'] = ($currentUser == $eventDetail['Event']['user_id']) ? '1' : '0';
                    $event['username'] = '' . $eventDetail['User']['first_name'] . ' ' . $eventDetail['User']['last_name'];
                    $userImage = (!empty($eventDetail['User']['image'])) ? $eventDetail['User']['image'] : '';
                    $event['user_picture'] = $this->General->getUploadImage($userImage, 'users', 100, 100);
                    $event['isCalendar'] = '' . $this->General->isCalendar($event['id'], 0);
                    $event['isCommented'] = '' . $this->General->isCommented($event['id'], 0);
                    $event['isLiked'] = '' . $this->General->isEventLiked($eventDetail['Event']['id'], 0);
                    $event['totalLikes'] = '' . $this->General->totalEventLikes($eventDetail['Event']['id'], 0);
                    $event['totalComments'] = '' . $this->General->totalEventComments($eventDetail['Event']['id'], 0);


                    /**
                     * Venue Detail 
                     */
                    $venue = array();
                    $venue['id'] = '' . $eventDetail['Venue']['id'];
                    $venue['name'] = '' . stripcslashes($eventDetail['Venue']['name']);
                    $venue['address'] = '' . $eventDetail['Venue']['address'];
                    $venue['extended_address'] = '' . $eventDetail['Venue']['extended_address'];
                    $venue['city'] = '' . $eventDetail['Venue']['city'];
                    $venue['country'] = '' . $eventDetail['Venue']['country'];
                    $venue['postal_code'] = '' . $eventDetail['Venue']['post_code'];
                    $venue['lat'] = '' . $eventDetail['Venue']['lat'];
                    $venue['long'] = '' . $eventDetail['Venue']['long'];
                    $event['venue'] = $venue;
                }
            }

            $this->loadModel('EventCalendar');
            $eventCalendars = $this->EventCalendar->find('all', array('conditions' => array('EventCalendar.event_id' => $peggdEventId, 'EventCalendar.user_id <>' => $currentUser, 'EventCalendar.status' => array('1', '2')), 'group' => 'EventCalendar.user_id', 'limit' => '10', 'recursive' => '0'));

            if (!empty($eventCalendars)) {
                foreach ($eventCalendars as $eventCalendar) {
                    $eventAttendee = array();
                    $eventAttendee['user_id'] = '' . $eventCalendar['User']['id'];
                    $eventAttendee['image'] = $this->General->getUploadImage($eventCalendar['User']['image'], 'users', 100, 100);
                    $eventAttendees[] = $eventAttendee;
                }
            }
        }
        //pr($event);die;
        $this->loadModel('EventComment');
        $eventComments = array();
        $comments = $this->EventComment->find('all', array('conditions' => array('EventComment.event_id' => $peggdEventId), 'recursive' => '0'));

        $this->paginate = array('conditions' => array('EventComment.event_id' => $peggdEventId), 'limit' => $perPageLimit, 'recursive' => '0',
        );
        $comments = $this->paginate('EventComment');

        if (!empty($comments)) {
            foreach ($comments as $comment) {
                $eventComment = array();
                $eventComment['id'] = '' . $comment['EventComment']['id'];
                $eventComment['comment'] = '' . $comment['EventComment']['comment_text'];
                $eventComment['created'] = $this->General->getHowLongAgo($comment['EventComment']['created']);
                $eventComment['user_id'] = '' . $comment['User']['id'];
                $eventComment['user_name'] = '' . $comment['User']['first_name'] . ' ' . $comment['User']['last_name'];
                $eventComment['user_image'] = $this->General->getUploadImage($comment['User']['image'], 'users', 100, 100);
                $eventComment['isComment'] = "1";
                $eventComments[] = $eventComment;
            }
        }

        $total_comments = $this->params['paging']['EventComment']['count'] . '';
        $per_page = $perPageLimit . '';
        $page = $currentPage . '';
        $eventDetail = $event;
        $eventAttendees = $eventAttendees;
        $eventComments = $eventComments;

        /* get page meta and keyword dynamically */
        $pageData = $this->DynamicPage->getMetaDetails('event_detail');
        $metaReplaceValues = array("[EVENT_NAME]" => $event['title']);

        if (isset($pageData['DynamicPage']['meta_title']) && $pageData['DynamicPage']['meta_title'] != '')
            $title_for_layout = $this->General->dynamicMetaMaker($pageData['DynamicPage']['meta_title'], $metaReplaceValues);

        if (isset($pageData['DynamicPage']['meta_description']) && $pageData['DynamicPage']['meta_description'] != '')
            $metaDescription = $this->General->dynamicMetaMaker($pageData['DynamicPage']['meta_description'], $metaReplaceValues);



        $this->set(compact(array('eventDetail', 'eventComments', 'eventAttendees', 'total_comments', 'title_for_layout', 'metaDescription', 'eventImageMain')));
    }

    /* event search for web */

    public function search($keyword = "") {
        $this->set('title_for_layout', __('Search Event', true));

        $keyword = (!empty($_GET['keyword'])) ? $_GET['keyword'] : "";

        $currentUser = $this->Auth->user('id');

        if (!($this->Session->check('limit')))
            $this->Session->write('limit', '10');
        $this->paginate = array(
            'limit' => '10'
        );

        if ($keyword != "0" && $keyword != "") {
            $this->request->data['search']['keyword'] = $keyword;
        } else {
            $keyword = $this->request->data['search']['keyword'];
        }

        if (isset($this->request->data['Event']['order_query'])) {
            $this->Session->write('limit', $this->request->data['Event']['order_query']);
        }

        $this->request->data['Event']['order_query'] = $this->Session->read('limit');

        //echo "<pre>";
        //print_r($this->request->data);
        //die();

        if ($keyword != "") {
            $this->loadModel('Event');
            $keyword = $keyword;
            $this->Event->recursive = 0;
            // check the type of searching 
            $condition = array('OR' => array("Event.name LIKE " => "%" . $keyword . "%"));
            // Searching and displaying the results 
            $events = $this->paginate('Event', $condition);
            $this->set('events', $events);

            $this->set('keyword', $keyword);


            $condition1 = array('Event.status' => '1');
            $this->set('active_events', $this->Event->find('count', array('conditions' => $condition1)));

            $condition2 = array('Event.status' => '0');
            $this->set('deactive_events', $this->Event->find('count', array('conditions' => $condition2)));

            $this->set('all_events', $this->Event->find('count'));
            if (!isset($events) || empty($events)) {
                $this->Session->write('messages', 'error');
                $this->Session->setFlash(__('No Search Result(s) Found.', true));
            }
            // $this->render('index');
        } else {
            $this->Session->write('messages', 'error');
            $this->Session->setFlash(__('Please enter any value in text fields', true));
            $this->redirect(array('action' => 'index'));
        }
    }

    /* social share on with the help of sharethis api */

    function shareThis() {

        $this->layout = 'ajax';
        $this->render('/Elements/sharethis');
    }

}
