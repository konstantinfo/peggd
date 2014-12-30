<?php

App::uses('AuthComponent', 'Controller/Component');
App::uses('CakeSession', 'Model/Datasource');

class User extends AppModel {

    public $name = 'User';
    public $order = 'User.fullname asc';

    public function beforeSave($options = array()) {
        if (isset($this->data[$this->alias]['password'])) {
            $this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
        }


        return true;
    }

    public $validate = array(
        'password' => array(
            'rule' => array('between', 4, 16),
            'message' => 'Password length between 4 to 16 characters.'
        ),
        'confirmpassword' => array(
            'rule' => 'confirmpassword',
            'message' => 'Confirm Password doesn\'t match with password.'
        ),
        'oldpassword' => array(
            'rule' => array('between', 4, 16),
            'message' => 'Old Password doesn\'t match with current password.'
        ),
        'name' => array(
            'rule' => 'notEmpty',
            'message' => 'Please fill out name.'
        ),
        'dob' => array(
            'rule' => 'notEmpty',
            'message' => 'Please fill out date of birth.'
        ),
        'batch_year' => array(
            'rule' => 'notEmpty',
            'message' => 'Please fill out batch Year.'
        ),
        'username' => array(
            'email' => array(
                'rule' => 'email',
                'message' => 'Please enter valid email address.',
            ),
            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'That email address is already has been taken.',
            ),
        ),
        'input_email_from_user' => array(
            'email' => array(
                'empty' => array('rule' => 'notEmpty',
                    'required' => true,
                    'message' => 'This field is required.',
                ),
                'notEmpty' => array('rule' => 'email',
                    'message' => 'Please provide a valid email address.',
                )
            )
        ),
    );

    public function checkEmail($email_address = null) {

        if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email_address)) {
            return false;
        } else {
            return true;
        }
    }

    public function checkPassword($password = null) {

        $password = AuthComponent::password($password);
        $user_id = CakeSession::read('Auth.User.id');

        $result = $this->find('count', array('conditions' => array("User.password" => $password, "User.id" => $user_id)));
        if ($result == 0) {
            return false;
        } else {
            return true;
        }
    }

    public function isRecordUniqueemail($email_address = null) {

        $result = $this->find('count', array('conditions' => "User.email_address = '" . $email_address . "'"));
        if ($result) {
            return false;
        } else {
            return true;
        }
    }

    public function email_validation($email = null) {
        $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        if (preg_match($regex, $email))
            return true;
        else
            return false;
    }

    public function confirm_password($password = null, $confirmPassword = null) {

        if ($password === $confirmPassword)
            return true;
        else
            return false;
    }

}
