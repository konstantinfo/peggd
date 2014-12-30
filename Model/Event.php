<?php

App::uses('AuthComponent', 'Controller/Component');
App::uses('CakeSession', 'Model/Datasource');

class Event extends AppModel {

    public $name = 'Event';
    public $order = 'Event.id desc';
    
    var $belongsTo = 'Venue';
}