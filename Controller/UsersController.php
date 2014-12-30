<?php

class UsersController extends AppController {

    public $name = 'Users';
    public $uses = array('User', 'EmailTemplate');
    public $helpers = array('Function');
    public $components = array('Upload', 'Email', 'Image');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(
        'admin_logout', 'admin_login', 'checkemail', 'changeCity', 'changeState', 'login', 'logout', 'forgetPassword', 'register', 'socialLogin', 'userProfile', 'checkAppVersion', 'copyImageFromFB', 'userProfileFromFB', 'signin', 'signup', 'forgot', 'fblogin', 'changeLocation', 'twitterouth');
    }

    // register user  function end here	 

    /**
     *  Admin Find the States for any country
     * @access public
     */
    function admin_dashboard() {
        $this->set('title_for_layout', __('Dashboard', true));

        $today = date("Y-m-d");
        $dateCondWeek = "DATE_ADD('" . $today . "', INTERVAL -7 DAY)";
        $dateCondMonth = "DATE_ADD('" . $today . "', INTERVAL -30 DAY)";

        $week_web_user = $this->User->find('count', array('conditions' => array('User.role_id' => 3, 'User.type' => 'w', 'User.created >= ' . $dateCondWeek)));
        $week_fb_user = $this->User->find('count', array('conditions' => array('User.role_id' => 3, 'User.type' => 'f', 'User.created >= ' . $dateCondWeek)));
        $week_tw_user = $this->User->find('count', array('conditions' => array('User.role_id' => 3, 'User.type' => 't', 'User.created >= ' . $dateCondWeek)));

        $month_web_user = $this->User->find('count', array('conditions' => array('User.role_id' => 3, 'User.type' => 'w', 'User.created >= ' . $dateCondMonth)));
        $month_fb_user = $this->User->find('count', array('conditions' => array('User.role_id' => 3, 'User.type' => 'f', 'User.created >= ' . $dateCondMonth)));
        $month_tw_user = $this->User->find('count', array('conditions' => array('User.role_id' => 3, 'User.type' => 't', 'User.created >= ' . $dateCondMonth)));


        $this->set(compact('week_web_user', 'week_fb_user', 'week_tw_user', 'month_web_user', 'month_fb_user', 'month_tw_user'));
    }

    function admin_login() {


        if ($this->Session->read('Auth.User')) {
            if ($this->Session->read('Auth.User.role') == 'admin') {
                $this->redirect(array('controller' => 'users', 'action' => 'dashboard'));
            } else {
                
            }
        }

        if ($this->request->is('post')) {



            $this->request->data['User']['password'] = trim($this->request->data['User']['password']);


            if ($this->Auth->login()) {

                $this->redirect(array('controller' => 'users', 'action' => 'dashboard'));
            } else {
                $this->Session->setFlash(__('Invalid username or password.'));
            }
        }


        $this->set('title_for_layout', __('Admin Login', true));
        $this->layout = "admin_login";
    }

    function admin_logout() {
        $this->Session->write('messages', 'confirm');
        $this->Session->setFlash(__('Log out successful.', true), 'default', array('class' => 'success'));
        $this->Session->delete("Privilege");
        $this->redirect($this->Auth->logout());
    }

    function push_notification($pushId = "", $push_message = "", $key_note = "") {
        $pushData = array("message" => $push_message, 'KEY_NOTE' => $key_note);

        $returnArray = $this->General->sendMessage($pushData, $pushId);

        if (!empty($returnArray) && $returnArray['status'] == 1) {
            if (isset($returnArray['registration_id']) && $returnArray['registration_id'] != '') {
                $this->loadModel('User');
                $this->User->query("update users set gcm_id = '" . $returnArray['registration_id'] . "' where gcm_id = '" . $pushID . "' ");
            }
        }
    }

    /**
     * http://192.168.0.79/peggd/users/login/sid:OA
     * method: POST
     * params: username, password, time_zone
     */
    function login($email = null, $password = null) {
        if (isset($_POST['username']) && isset($_POST['password'])) {
            $email = $_POST['username'];
            $password = $_POST['password'];
        }

        if (isset($_POST['time_zone']))
            $time_zone = $_POST['time_zone'];
        else
            $time_zone = "";

        $outPutArray = array();
        //check login form is submitted
        if (!$email || !$password) {
            $arr = array("replyCode" => "error", "replyMsg" => $this->General->getMessage('users_login_ERROR_USERNAME_PASSWORD_INCORRECT'));
            echo $this->Json->encode($arr);
            exit;
        }
        $this->request->data = $this->User->find('first', array('conditions' => array('User.username' => $email, 'User.role_id' => '3', 'User.password' => $this->Auth->password($password))));

        if (!empty($this->request->data)) {
            if (!$this->request->data['User']['status']) {
                $outPutArray['replyCode'] = $this->General->getMessage('ERROR');
                $outPutArray['replyMsg'] = "Your account has been deactivated.  Please contact administrator.";
                $output = $this->Json->encode($outPutArray);
                echo $output;
                exit;
            }


            $isEqualVersion = '';
            $isMandatory = '';
            if ($this->Auth->login($this->request->data['User'])) {
                $currentUser = $this->Auth->user('id');

                //update time zone
                $this->User->id = $currentUser;
                $this->User->saveField('time_zone', $time_zone);

                //update iphone id
                if (!empty($_POST['device_token']))
                    $this->General->setUdid($currentUser, $_POST['device_token']);

                $sid = trim(base64_encode($currentUser), '==');
                $arr1 = array(
                    "replyCode" => "success",
                    "replyMsg" => $this->General->getMessage('users_login_SUCCESS_LOGED_IN'),
                    "sid" => $sid,
                    "name" => $this->Auth->user('first_name') . ' ' . $this->Auth->user('last_name'),
                    "image" => $this->General->getUploadImage($this->Auth->user('image'), 'users', 80, 80),
                    "location" => $this->Auth->user('address'),
                );
                $output = $this->Json->encode($arr1);
            } else {
                $arr = array("replyCode" => "error", "replyMsg" => $this->General->getMessage($errorMsgArr, 'users_login_ERROR_USERNAME_PASSWORD_INCORRECT'));
                $output = $this->Json->encode($arr);
            }
        } else {
            $arr = array("replyCode" => "error", "replyMsg" => $this->General->getMessage('users_login_ERROR_USERNAME_PASSWORD_INCORRECT'));
            $output = $this->Json->encode($arr);
        }

        echo $output;
        exit;
    }

    /**
     * http://192.168.0.79/peggd/users/login/sid:OA
     * method: POST
     * params: username, password, time_zone
     */
    function signin() {
        $this->layout = false;
        $this->autoRender = false;


        $sendArray = array();
        if (empty($this->request->data)) {
            $sendArray['msg'] = 'Cannot Login, please try again ';
            $sendArray['replyCode'] = 'faliure';
        }

        if (!empty($this->request->data)) {
            $this->request->data['User'] = $this->request->data;

            if ($this->Auth->login()) {
                $this->Session->delete('locationData');
                $sendArray['replyCode'] = 'success';
            } else {
                $sendArray['msg'] = 'Invalid User name and password ';
                $sendArray['replyCode'] = 'faliure';
            }
        }
        echo $this->Json->encode($sendArray);
    }

    function signup() {

        $this->layout = false;
        $this->autoRender = false;
        if (empty($this->request->data)) {
            $sendArray['msg'] = 'There is some error in create account, please try again ';
            $sendArray['replyCode'] = 'faliure';
        }

        if (!empty($this->request->data)) {
            $this->request->data['User'] = $this->request->data;

            $this->request->data['User']['role_id'] = 3;
            $this->request->data['User']['fullname'] = $this->request->data['User']['first_name'] . ' ' . $this->request->data['User']['last_name'];
            $this->request->data['User']['dob'] = $this->request->data['User']['year'] . '-' . $this->request->data['User']['month'] . '-' . $this->request->data['User']['date'];

            if ($this->User->save($this->request->data['User'])) {
                $message = "<b>Welcome to Life in Peggd.</b>" . "<br/><br/>";
                $message .= "Hi " . $this->request->data['User']['fullname'] . ",<br/><br/>";
                $message .= "Thank you for registering with Peggd! We hope you enjoy the experience and all of the extra time you'll have." . "<br/><br/>";
                $message .= "Your Username :<strong>" . $this->request->data['User']['username'] . "</strong>" . "<br/><br/>";
                $message .= "Your Password :<strong>" . $this->request->data['User']['password'] . "</strong>" . "<br/><br/>";

                $message .= "Kind regards," . "<br/><br/>";

                $message .= "The Peggd Team" . "<br/><br/>";
                $this->Email->to = $this->request->data['User']['username'];
                $this->Email->from = ' <' . Configure::read('Site.fromMail') . '>';
                $this->Email->subject = 'Welcome!';
                $this->Email->sendAs = 'html';
// Send Email Funcality 
                if ($this->Email->send($message)) {
                    $sendArray['replyCode'] = 'success';
                    $sendArray['msg'] = 'An email has been sent successfully.Thanks';
                    $sendArray['response']['message'] = 'An email has been sent successfully.Thanks';
                } else {
                    $sendArray['replyCode'] = 'error';
                    $sendArray['msg'] = 'An error occurred while sending the email, please try again.';
                }

                $sendArray['msg'] = 'Welcome! Thank you for registering a new account with Peggd.';
                $sendArray['replyCode'] = 'success';
            } else {
                $sendArray['msg'] = 'There is some error,Please try again ';
                $sendArray['replyCode'] = 'faliure';
            }
        } else {
            $sendArray['msg'] = 'There is some error in create account, please try again ';
            $sendArray['replyCode'] = 'faliure';
        }
        echo $this->Json->encode($sendArray);
    }

    function signout() {
        $currentUser = $this->Auth->user('id');

        $this->User->id = $currentUser;
        if ($this->User->exists()) {
            $this->User->id = $currentUser;
        }
        if ($this->Auth->logout()) {
            $this->Session->delete('locationData');
            $this->redirect(array('controller' => '', 'action' => 'index'));
        }
    }

    function forgot() {
        $email = $this->request->data['User']['username'] = $this->request->data['username'];

        $outPutArray = array();
        if ($email == "") {
            $outPutArray['replyCode'] = $this->General->getMessage('ERROR');
            $outPutArray['replyMsg'] = $this->General->getMessage('users_forgetPassword_ERROR_NO_EMAIL');
        } else {
            $users = $this->User->find('first', array('conditions' => array('username' => $email)));
            if (!isset($users['User']['username'])) {
                $outPutArray['replyCode'] = $this->General->getMessage('ERROR');
                $outPutArray['replyMsg'] = $this->General->getMessage('users_forgetPassword_ERROR_EMAIL_NOT_REGISTERED');
            } else {
                $fullName = $users['User']['fullname'];
                $email = $users['User']['username'];
                $password = $this->General->getSalt();
                //save new password
                $data['id'] = $users['User']['id'];

                $new_pass = $password;

                $data['password'] = $new_pass;
                $this->User->save($data);

                $message = "Hi " . $fullName . ",<br/><br/>";
                $message .= "We've recently received notice that you've forgotten your password. Please use the password below to acces your account.<br/><br/>";
                $message .= "New password: <strong>" . $password . "</strong><br/><br/>Once logged in on the app, you can reset your password in the settings.";

                //send email back to user
                $this->Email->to = $email;
                $this->Email->from = ' <' . Configure::read('Site.fromMail') . '>';
                $this->Email->subject = 'Forgot Your Password?';
                $this->Email->sendAs = 'html';

                if ($this->Email->send($message)) {
                    $outPutArray['replyCode'] = 'success';
                    $outPutArray['replyMsg'] = 'An email has been sent successfully with your login details. Thanks';
                } else {
                    $outPutArray['replyCode'] = 'error';
                    $outPutArray['replyMsg'] = 'An error occurred while sending the email, please try again.';
                }
            }
        }


        echo $this->Json->encode($outPutArray);

        exit;
    }

    /**
     * http://support24hour.com/workplace2/peggd/users/forgetPassword/vivek@pegd.com
     * @param type $email 
     */
    function forgetPassword($email = "") {

        $outPutArray = array();
        if ($email == "") {
            $outPutArray['replyCode'] = $this->General->getMessage('ERROR');
            $outPutArray['replyMsg'] = $this->General->getMessage('users_forgetPassword_ERROR_NO_EMAIL');
        } else {

            if ($this->General->checkMail($email)) {

                $users = $this->User->find('first', array('conditions' => array('username' => $email)));
                if (!isset($users['User']['username'])) {
                    $outPutArray['replyCode'] = $this->General->getMessage('ERROR');
                    $outPutArray['replyMsg'] = $this->General->getMessage('users_forgetPassword_ERROR_EMAIL_NOT_REGISTERED');
                } else {
                    $fullName = $users['User']['fullname'];
                    $email = $users['User']['username'];
                    $password = $this->General->getSalt();
                    //save new password
                    $data['id'] = $users['User']['id'];
                    //$new_pass = Security::hash(Configure::read('Security.salt') . $password);
                    $new_pass = $password;

                    $data['password'] = $new_pass;
                    $this->User->save($data);

                    $message = "Hi " . $fullName . ",<br/><br/>";
                    $message .= "We've recently received notice that you've forgotten your password. Please use the password below to acces your account.<br/><br/>";
                    $message .= "New password: <strong>" . $password . "</strong><br/><br/>Once logged in on the app, you can reset your password in the settings.";

                    //send email back to user
                    $this->Email->to = $email;
                    $this->Email->from = ' <' . Configure::read('Site.fromMail') . '>';




                    $this->Email->subject = 'Forgot Your Password?';


                    $this->Email->sendAs = 'html';

                    if ($this->Email->send($message)) {
                        $outPutArray['replyCode'] = 'success';
                        $outPutArray['replyMsg'] = 'An email has been sent successfully with your login details. Thanks';
//                       
                    } else {
                        $outPutArray['replyCode'] = 'error';
                        $outPutArray['replyMsg'] = 'An error occurred while sending the email, please try again.';
                    }
                }
            } else {
                $outPutArray['replyCode'] = 'error';
                $outPutArray['replyMsg'] = 'Email is not in valid format.';
            }
        }


        $output = $this->Json->encode($outPutArray);
        echo $output;
        exit;
    }

    /**
     * register
     * http://192.168.0.122/peggd/users/register
     * params: email, password, name, last_name, image, time_zone, device_token, category_id=(1,2,3,4,5)
     */
    function register() {

        if (isset($_POST['email']) && $_POST['email'] != "" && !empty($_POST['password'])) {

            if ($this->General->checkMail($_POST['email'])) {

                $isUserExist = $this->User->find('first', array('conditions' => array('User.username' => $_POST['email'], 'User.role_id' => '3')));
                if (!empty($isUserExist)) {
                    $outPutArray['replyCode'] = $this->General->getMessage('ERROR');
                    $outPutArray['replyMsg'] = $this->General->getMessage('users_register_ERROR_EMAIL_ALREADY_EXIST');
                } else {
                    $this->User->create();
                    $addUser = array();
                    $addUser['id'] = '';

                    if (!empty($_FILES['image'])) {

                        $file = $_FILES['image'];
                        $imgName = time() . '_' . $file['name'];
                        $destination = realpath('../../app/webroot/uploads/users') . '/';

                        if (move_uploaded_file($file['tmp_name'], $destination . $imgName)) {
                            //copy image
                            $addUser['image'] = $imgName;
                        } else {
                            unset($_FILES['image']);
                        }
                    }

                    if (!empty($_POST['name'])) {
                        $addUser['first_name'] = $_POST['name'];
                        $addUser['fullname'] = $_POST['name'];
                    }

                    if (!empty($_POST['last_name'])) {
                        $addUser['last_name'] = $_POST['last_name'];
                    }

                    if (isset($_POST['email'])) {
                        $addUser['username'] = $_POST['email'];
                    }

                    if (isset($_POST['password'])) {
                        $addUser['password'] = $_POST['password'];
                    }
                    if (isset($_POST['category_id'])) {
                        $addUser['category_id'] = $_POST['category_id'];
                    }
                    if (isset($_POST['time_zone'])) {
                        $addUser['time_zone'] = $_POST['time_zone'];
                    }

                    $addUser['type'] = 'w';
                    $addUser['status'] = '1';

                    $addUser['role_id'] = '3';


                    if ($this->User->save($addUser)) {

                        $lastInsertId = $this->User->id;

                        //update iphone id
                        if (!empty($_POST['device_token']))
                            $this->General->setUdid($lastInsertId, $_POST['device_token']);

                        //if user save then insert this user event setting
                        $this->loadModel('Setting');

                        $eventSetting['Setting']['user_id'] = $lastInsertId;
                        $this->Setting->save($eventSetting);
                        //end user event setting code
                        ////////add preference...............
                        ////////////Send Email..............................

                        $fullName = $_POST['name'];


                        $message = "<b>Welcome to Life in Peggd.</b>" . "<br/><br/>";
                        $message .= "Hi " . $fullName . ",<br/><br/>";
                        $message .= "Thank you for registering with Peggd! We hope you enjoy the experience and all of the extra time you'll have." . "<br/><br/>";
                        $message .= "Your Username :<strong>" . $_POST['email'] . "</strong>" . "<br/><br/>";
                        $message .= "Your Password :<strong>" . $_POST['password'] . "</strong>" . "<br/><br/>";

                        $message .= "Kind regards," . "<br/><br/>";

                        $message .= "The Peggd Team" . "<br/><br/>";


                        $this->Email->to = $_POST['email'];
                        $this->Email->from = ' <' . Configure::read('Site.fromMail') . '>';



                        $this->Email->subject = 'Welcome!';


                        $this->Email->sendAs = 'html';

                        if ($this->Email->send($message)) {
                            $outPutArray['replyCode'] = 'success';
                            $outPutArray['replyMsg'] = 'An email has been sent successfully.Thanks';
                            $outPutArray['response']['message'] = 'An email has been sent successfully.Thanks';
                        } else {
                            $outPutArray['replyCode'] = 'error';
                            $outPutArray['replyMsg'] = 'An error occurred while sending the email, please try again.';
                        }

                        $outPutArray['replyCode'] = 'success';
                        $outPutArray['replyMsg'] = 'Welcome! Thank you for registering a new account with Peggd.';

                        $userId = $this->User->getLastInsertId();
                        $sid = trim(base64_encode($userId), '==');

                        $outPutArray['sid'] = $sid;

                        $this->request->data = $this->User->find('first', array('conditions' => array('User.id' => $userId)));

                        if (!empty($this->request->data)) {
                            if ($this->Auth->login($this->request->data['User'])) {
                                $outPutArray['name'] = $this->Auth->user('first_name') . ' ' . $this->Auth->user('last_name');
                                $outPutArray['image'] = $this->General->getUploadImage($this->Auth->user('image'), 'users', 80, 80);
                                $outPutArray['location'] = $this->Auth->user('address');
                            }
                        }
                    } else {
                        $outPutArray['replyCode'] = $this->General->getMessage('ERROR');
                        $outPutArray['replyMsg'] = $this->General->getMessage('users_register_ERROR_USER_CANT_ADDED');
                    }
                }
            } else {
                $outPutArray['replyCode'] = $this->General->getMessage('ERROR');
                $outPutArray['replyMsg'] = "Email is not in valid format.";
            }
        } else {
            $outPutArray['replyCode'] = $this->General->getMessage('ERROR');
            $outPutArray['replyMsg'] = $this->General->getMessage('users_register_ERROR_INSUFFICIENT_DATA');
        }


        $output = $this->Json->encode($outPutArray);
        echo $output;
        exit;
    }

    /**
     *  Logout app user
     * http://support24hour.com/workplace2/peggd/users/logout/sid:OA
     */
    function logout() {
        $currentUser = $this->Auth->user('id');
        //pr($this->Auth->request->data);die;
        //remove iphone id
        if ($this->User->exists($currentUser)) {
            $this->User->id = $currentUser;
            $this->User->saveField('device_token', '');
        }

        $this->Cookie->delete('cookie_uname');
        $this->Cookie->delete('cookie_pass');
        $this->Cookie->delete('cookie_rem');

        if ($this->Auth->logout()) {
            $arr = array('replyCode' => 'success', 'replyMsg' => "You are logout successfully.");
        } else {
            $arr = array('replyCode' => 'error', 'replyMsg' => "You are not logout, Please try again.");
        }

        echo $this->Json->encode($arr);
        exit;
    }

    function admin_reset_password() {

        $currentUser = $this->Auth->user('id');
        $this->set('title_for_layout', __('Setting', true));



        if (!empty($this->request->data)) {

            if (trim($this->request->data['User']['current_password']) == '') {
                $this->Session->write('messages', 'error');
                $this->Session->setFlash(__('Please enter new password and confirm password both.', true), 'default', array('class' => 'error'));
            } elseif (trim($this->request->data['User']['conf_password']) == '') {
                $this->Session->write('messages', 'error');
                $this->Session->setFlash(__('Please enter new password and confirm password both.', true), 'default', array('class' => 'error'));
            } elseif (trim($this->request->data['User']['current_password']) != trim($this->request->data['User']['conf_password'])) {
                $this->Session->write('messages', 'error');
                $this->Session->setFlash(__('New Password and Confirm Password did not match. Please, try again.', true), 'default', array('class' => 'error'));
            } elseif (trim($this->request->data['User']['current_password']) == trim($this->request->data['User']['conf_password'])) {

                $this->request->data['User']['password'] = $this->request->data['User']['current_password'];

                if ($this->User->save($this->request->data['User'])) {
                    $this->Session->write('messages', 'confirm');
                    $this->Session->setFlash(__('Password has been reset.', true), 'default', array('class' => 'success'));
                } else {
                    $this->Session->write('messages', 'error');
                    $this->Session->setFlash(__('Password could not be reset. Please, try again.', true), 'default', array('class' => 'error'));
                }
            } else {
                $this->Session->write('messages', 'error');
                $this->Session->setFlash(__('Please, Try Again.', true), 'default', array('class' => 'error'));
            }
        }
        if (empty($this->request->data)) {
            $this->request->data = $this->User->read(null, $currentUser);
        }
    }

    /**
     *  Function for check email whether it is available or not
     * @access public
     */
    function checkemail() {
        $username = html_entity_decode($_REQUEST['email_id']);
        $users = $this->User->find('count', array('conditions' => array('username' => $username), 'recursive' => '-1'));

        if ($users) {
            echo "false";
        } else {
            echo "true";
        }
        exit;
    }

    /**
     * register, update, & login for social sites (FB & TWITTER)
     * http://192.168.0.122/peggd/users/socialLogin
     * params: email, social_id, name, last_name, image, time_zone, display_name, type(t,f), friends('1000,1203,1502'), device_token
     */
    function socialLogin() {

        if (empty($_POST['email']) || empty($_POST['social_id']) || empty($_POST['name']) || empty($_POST['type'])) {
            $arr1 = array("replyCode" => "ERROR", "sid" => "", "replyMsg" => "You have missed any of the information");
            echo $this->Json->encode($arr1);
            exit;
        }

        //Check email validation
        if (!empty($_POST['email'])) {
            if (!$this->General->checkMail($_POST['email'])) {
                $outPutArray['replyCode'] = $this->General->getMessage('ERROR');
                $outPutArray['replyMsg'] = "Provided email address is not valid. Please provided an valid email address.";
                $output = $this->Json->encode($outPutArray);
                echo $output;
                exit;
            }
        }

        $addUser['status'] = 1;
        $addUser['role_id'] = 3;
        $addUser['last_login'] = date('Y-m-d H:i:s');

        //user time zone
        if (isset($_POST['time_zone']))
            $time_zone = $_POST['time_zone'];
        else
            $time_zone = "";

        $addUser['time_zone'] = $time_zone;

        if (!empty($_POST['name'])) {
            $addUser['first_name'] = $_POST['name'];
            $addUser['fullname'] = $_POST['name'];
        }

        if (!empty($_POST['last_name']))
            $addUser['last_name'] = $_POST['last_name'];


        if (!empty($_POST['email'])) {
            $addUser['username'] = $_POST['email'];
        }

        if (!empty($_POST['display_name'])) {
            if ($_POST['type'] == 'f')
                $addUser['fb_name'] = $_POST['display_name'];

            if ($_POST['type'] == 't')
                $addUser['tw_name'] = $_POST['display_name'];
        }

        if (!empty($_POST['social_id'])) {
            if ($_POST['type'] == 'f') {
                $condition_id = 'facebook_id';
                $addUser['facebook_id'] = $_POST['social_id'];
                $addUser['type'] = 'f';
                $registrationFrom = 'FaceBook';
            }
            if ($_POST['type'] == 't') {
                $condition_id = 'twitter_id';
                $addUser['twitter_id'] = $_POST['social_id'];
                $addUser['type'] = 't';
                $registrationFrom = 'Twitter';
            }
        }



        $isUserExist = $this->User->find('first', array('conditions' => array('User.username' => $_POST['email'])));


        if (!empty($isUserExist)) {

            //if user account status is disabled
            if (!empty($isUserExist)) {
                if (!$isUserExist['User']['status']) {
                    $outPutArray['replyCode'] = $this->General->getMessage('ERROR');
                    $outPutArray['replyMsg'] = "Your account has been deactivated.  Please contact to admin.";
                    $output = $this->Json->encode($outPutArray);
                    echo $output;
                    exit;
                }
            }

            $emailFind = 0;
            //IF FB ID is not empty
            if (!empty($isUserExist['User']['facebook_id']) && !empty($_POST['social_id']) && $_POST['type'] == 'f') {
                if ($isUserExist['User']['facebook_id'] != $_POST['social_id'])
                    $emailFind = 1;
            }

            //IF TWITTER ID is not empty
            if (!empty($isUserExist['User']['twitter_id']) && !empty($_POST['social_id']) && $_POST['type'] == 't') {
                if ($isUserExist['User']['twitter_id'] != $_POST['social_id'])
                    $emailFind = 1;
            }

            //IF email is allready registred and social id is not mached
            if (!empty($emailFind)) {
                $outPutArray['replyCode'] = $this->General->getMessage('ERROR');
                $outPutArray['replyMsg'] = "Provided email is alrady in use. Please provided another email address.";
                $output = $this->Json->encode($outPutArray);
                echo $output;
                exit;
            }


            //IF FB ID IS EMPTY THEN UPDATE IT AND ALSO UPDATE FB NAME
            if (empty($isUserExist['User']['facebook_id']) && !empty($_POST['social_id']) && $_POST['type'] == 'f') {
                $updateUserInfo['facebook_id'] = $_POST['social_id'];
                $updateUserInfo['fb_name'] = $_POST['display_name'];
                $this->User->id = $isUserExist['User']['id'];
                $this->User->save($updateUserInfo);
            }

            //IF TWITTER ID IS EMPTY THEN UPDATE IT AND ALSO UPDATE FB NAME
            if (empty($isUserExist['User']['twitter_id']) && !empty($_POST['social_id']) && $_POST['type'] == 't') {
                $updateUserInfo['twitter_id'] = $_POST['social_id'];
                $updateUserInfo['tw_name'] = $_POST['display_name'];
                $this->User->id = $isUserExist['User']['id'];
                $this->User->save($updateUserInfo);
            }


            //if user laready registred and login again
            if (!empty($isUserExist)) {
                if ($this->Auth->login($isUserExist['User'])) {
                    $currentUser = $this->Auth->user('id');

                    $sid = trim(base64_encode($currentUser), '==');

                    //update time zone
                    $this->User->id = $currentUser;
                    $this->User->saveField('time_zone', $time_zone);


                    //update iphone id
                    if (!empty($_POST['device_token']))
                        $this->General->setUdid($currentUser, $_POST['device_token']);

                    $arr1 = array(
                        "replyCode" => "success",
                        "replyMsg" => "",
                        "sid" => $sid,
                        "name" => $this->Auth->user('first_name') . ' ' . $this->Auth->user('last_name'),
                        "image" => $this->General->getUploadImage($this->Auth->user('image'), 'users', 80, 80),
                        "location" => $this->Auth->user('address'),
                        "is_register" => 0
                    );
                    echo $output = $this->Json->encode($arr1);
                    exit;
                } else {
                    $arr = array("replyCode" => $this->General->getMessage('ERROR'), "replyMsg" => $this->General->getMessage('users_login_ERROR_USERNAME_PASSWORD_INCORRECT'));
                    echo $output = $this->Json->encode($arr);
                    exit;
                }
            }
        }



        //NOTE:-     NEW REGISTRATION CODE START HERE
        if (empty($isUserExist['User']['id'])) {
            $addUser['id'] = '';
            $is_register = 1;
        } else {
            $addUser['id'] = $isUserExist['User']['id'];
            $is_register = 0;
        }

        if (!empty($_FILES['image'])) {

            $file = $_FILES['image'];
            $imgName = time() . '_' . $file['name'];
            $destination = realpath('../../app/webroot/uploads/users') . '/';

            if (move_uploaded_file($file['tmp_name'], $destination . $imgName)) {
                //copy image
                $addUser['image'] = $imgName;
            } else {
                unset($_FILES['image']);
            }
        }


        $peggdFriends = array();

        if ($this->User->save($addUser, false)) {
            $lastInsertId = $this->User->id;
            //update iphone id
            if (!empty($_POST['device_token']))
                $this->General->setUdid($lastInsertId, $_POST['device_token']);

            if ($is_register) {
                //enter following into follower table
                if (!empty($_POST['friends']))
                    $peggdFriends = $this->interFollowing($_POST['friends'], $isUserExist['User']['id'], 'facebook_id');
            }


            //send email
            if (!empty($isUserExist['User']['id'])) {


                $this->loadModel('Setting');

                $eventSetting['Setting']['user_id'] = $lastInsertId;
                $this->Setting->save($eventSetting);
                //end user event setting code
                ////////////Send Email..............................
                $fullName = $_POST['name'];
                $message = "Hi " . $fullName . ",<br/><br/>";
                $message .= "Welcome! Thank you for registering with Peggd.We hope you enjoy the experience and all of the extra time you'll have." . "<br/><br/>";
                $message .= "Your Username :<strong>" . $_POST['email'] . "</strong>" . "<br/><br/>";
                //send email back to user
                $this->Email->to = $_POST['email'];


                $this->Email->from = ' <' . Configure::read('Site.fromMail') . '>';
                $this->Email->subject = 'Successful Registration';


                $this->Email->sendAs = 'html';

                if ($this->Email->send($message)) {
                    $outPutArray['replyCode'] = 'success';
                    $outPutArray['replyMsg'] = 'An email has been sent successfully.Thanks';
                    $outPutArray['response']['message'] = 'An email has been sent successfully.Thanks';
                } else {
                    $outPutArray['replyCode'] = 'error';
                    $outPutArray['replyMsg'] = 'An error occurred while sending the email, please try again.';
                }
            }
            // .............Send Email End...............................    

            $outPutArray['replyCode'] = 'success';
            $outPutArray['replyMsg'] = 'Welcome you are login successfully.';

            if (!empty($isUserExist['User']['id']))
                $userId = $isUserExist['User']['id'];
            else
                $userId = $this->User->getLastInsertId();

            $this->request->data = $this->User->find('first', array('conditions' => array('User.id' => $userId)));

            if (!empty($this->request->data)) {
                if ($this->Auth->login($this->request->data['User'])) {
                    $sid = trim(base64_encode($this->Auth->user('id')), '==');

                    $arr1 = array(
                        "replyCode" => "success",
                        "replyMsg" => "",
                        "sid" => $sid,
                        "name" => $this->Auth->user('first_name') . ' ' . $this->Auth->user('last_name'),
                        "image" => $this->General->getUploadImage($this->Auth->user('image'), 'users', 80, 80),
                        "location" => $this->Auth->user('address'),
                        "is_register" => $is_register,
                        "peggdFriends" => $peggdFriends,
                    );
                    $output = $this->Json->encode($arr1);
                } else {
                    $arr = array("replyCode" => $this->General->getMessage('ERROR'), "replyMsg" => $this->General->getMessage('users_login_ERROR_USERNAME_PASSWORD_INCORRECT'));
                    $output = $this->Json->encode($arr);
                }
            } else {
                $arr = array("replyCode" => $this->General->getMessage('ERROR'), "replyMsg" => $this->General->getMessage('users_login_ERROR_USERNAME_PASSWORD_INCORRECT'));
                $output = $this->Json->encode($arr);
            }
        } else {
            $outPutArray['replyCode'] = $this->General->getMessage('ERROR');
            $outPutArray['replyMsg'] = $this->General->getMessage('users_fbLogin_ERROR_PROBLEM_IN_SAVING_INFO');
            $output = $this->Json->encode($outPutArray);
        }


        echo $output;
        exit;
    }

    /**
     * update social id 
     * http://192.168.0.122/peggd/users/updateSocialId/sid:Mg
     * params: facebook_id, twitter_id, google_id
     */
    function updateSocialId() {
        $currentUser = $this->Auth->user('id');

        $userInfo = $this->User->find('first', array('conditions' => array('User.id' => $currentUser), 'fields' => 'facebook_id,twitter_id,google_id', 'recursive' => -1));
        //pr($userInfo);die;

        if (empty($userInfo['User']['facebook_id']) && !empty($_POST['facebook_id']))
            $updateUser['facebook_id'] = $_POST['facebook_id'];

        if (empty($userInfo['User']['twitter_id']) && !empty($_POST['twitter_id']))
            $updateUser['twitter_id'] = $_POST['twitter_id'];

        if (empty($userInfo['User']['google_id']) && !empty($_POST['google_id']))
            $updateUser['google_id'] = $_POST['google_id'];
        //pr($updateUser);die;

        if (!empty($updateUser)) {
            $this->User->id = $currentUser;
            if ($this->User->save($updateUser)) {
                $arr = array("replyCode" => "success", "replyMsg" => $this->General->getMessage('user_profile_updated'));
                $output = $this->Json->encode($arr);
            }
        } else {
            $arr = array("replyCode" => "success", "replyMsg" => $this->General->getMessage('user_profile_already_updated'));
            $output = $this->Json->encode($arr);
        }

        echo $output;
        exit;
    }

    /**
     * find friends
     * http://192.168.0.122/peggd/users/findFriends/sid:MjQ
     * params: social_id, type=(f/t/g), friend_ids='605444529,2338330898,2338382042'
     */
    function findFriends() {
        $currentUser = $this->Auth->user('id');
        $this->loadModel('Follower');

        if (empty($_POST['type']) || empty($_POST['friend_ids'])) {
            $arr = array("replyCode" => "error", "replyMsg" => $this->General->getMessage('provide_complete_technical_detail'));
            echo $this->Json->encode($arr);
            exit;
        }

        if ($_POST['type'] == 'f')
            $condition_field = 'facebook_id';
        elseif ($_POST['type'] == 't')
            $condition_field = 'twitter_id';
        elseif ($_POST['type'] == 'g')
            $condition_field = 'google_id';



        //update social id if have NULL
        $userInfo = $this->User->find('first', array('conditions' => array('User.id' => $currentUser), 'fields' => $condition_field, 'recursive' => -1));


        if (empty($userInfo[$condition_field]) && !empty($_POST['social_id'])) {
            $updateUser[$condition_field] = $_POST['social_id'];
            $this->User->id = $currentUser;
            $this->User->save($updateUser);
        }


        $followingIds = explode(',', $_POST['friend_ids']);

        $limit = '20';
        $this->paginate = array(
            'conditions' => array('User.role_id' => 3, 'User.' . $condition_field => $followingIds),
            'limit' => $limit,
            'fields' => array('User.id', 'User.fullname', 'User.first_name', 'User.last_name', 'User.image'),
            'order' => array('User.id' => 'asc')
        );
        $getFollowingUsers = $this->paginate('User');


        $followingInfo = array();
        foreach ($getFollowingUsers as $value) {

            $isFollowingExists = $this->Follower->find('count', array('conditions' => array('Follower.follower_id' => $value['User']['id'], 'Follower.following_id' => $currentUser)));


            if ($isFollowingExists)
                $isFollowing = "1";
            else
                $isFollowing = "0";
            //new code
            $followingInfo[] = array(
                'user_id' => '' . $value['User']['id'],
                'fullname' => '' . $value['User']['first_name'] . ' ' . $value['User']['last_name'],
                'image' => $this->General->getUploadImage($value['User']['image'], 'users', 160, 160),
                'isFollowing' => $isFollowing,
            );
        }

        if (!empty($followingInfo)) {



            $currentPage = '1';
            if (!empty($this->params['named']['page']))
                $currentPage = $this->params['named']['page'];

            $arr = array(
                "replyCode" => "success",
                "replyMsg" => "peggd user listing",
                "total_count" => '' . $this->params['paging']['User']['count'],
                "per_page" => $limit,
                "current_page" => $currentPage,
                "peggdFriends" => $followingInfo,
            );
        } else {



            $arr = array(
                "replyCode" => "success",
                "replyMsg" => "peggd user listing",
                "total_count" => '0',
                "per_page" => '0',
                "current_page" => '0',
                "peggdFriends" => array(),
            );
        }

        echo $this->Json->encode($arr);
        exit;
    }

    /**
     * find peggd friends
     * http://192.168.0.122/peggd/users/findPeggdFriends/sid:Mg/page:1
     * params: 
     */
    function findPeggdFriends() {
        $currentUser = $this->Auth->user('id');
        $this->loadModel('Follower');

        $limit = '150';
        $this->paginate = array(
            'conditions' => array('User.role_id' => 3, 'User.id <>' => $currentUser),
            'limit' => $limit,
            'fields' => array('User.id', 'User.fullname', 'User.first_name', 'User.last_name', 'User.image'),
            'order' => array('User.id' => 'asc')
        );
        $getFollowingUsers = $this->paginate('User');



        $followingInfo = array();
        foreach ($getFollowingUsers as $value) {

            $isFollowingExists = $this->Follower->find('count', array('conditions' => array('Follower.follower_id' => $value['User']['id'], 'Follower.following_id' => $currentUser)));


            if ($isFollowingExists)
                $isFollowing = "1";
            else
                $isFollowing = "0";
            //new code
            $followingInfo[] = array(
                'user_id' => '' . $value['User']['id'],
                'fullname' => '' . $value['User']['first_name'] . ' ' . $value['User']['last_name'],
                'image' => $this->General->getUploadImage($value['User']['image'], 'users', 160, 160),
                'isFollowing' => $isFollowing,
            );
        }

        if (!empty($followingInfo)) {

            $currentPage = '1';
            if (!empty($this->params['named']['page']))
                $currentPage = $this->params['named']['page'];

            $arr = array(
                "replyCode" => "success",
                "replyMsg" => "peggd user listing",
                "total_count" => '' . $this->params['paging']['User']['count'],
                "per_page" => $limit,
                "current_page" => $currentPage,
                "peggdFriends" => $followingInfo,
            );
        } else {



            $arr = array(
                "replyCode" => "success",
                "replyMsg" => "peggd user listing",
                "total_count" => '0',
                "per_page" => '0',
                "current_page" => '0',
                "peggdFriends" => array(),
            );
        }

        echo $this->Json->encode($arr);
        exit;
    }

    /**
     * inter following ids 
     * @param type $followingIds('605444529,2338330898,2338382042')
     * @param type $currentUser(27)
     * @param type $condition_id(twitter_id/facebook_id)
     */
    function interFollowing($followingIds = null, $currentUser = null, $condition_id = 'twitter_id') {
        $this->loadModel('Follower');
        $followingIds = explode(',', $followingIds);
        $this->User->recursive = -1;
        $getFollowingUsers = $this->User->find('all', array('conditions' => array('User.' . $condition_id => $followingIds), 'fields' => array('User.id', 'User.fullname', 'User.image'), 'order' => 'User.id'));


        $followingInfo = array();
        foreach ($getFollowingUsers as $value) {

            //new code
            $followingInfo[] = array(
                'user_id' => '' . $value['User']['id'],
                'fullname' => '' . $value['User']['fullname'],
                'image' => $this->General->getUploadImage($value['User']['image'], 'users', 160, 160),
                'isFollowing' => '0',
            );
        }

        return $followingInfo;
    }

    /**
     * get profile
     * http://192.168.0.79/peggd/users/getProfile/sid:MjQ
     * params:
     */
    function getProfile() {
        $currentUser = $this->Auth->user('id');

        if (!empty($currentUser)) {

            $userInfo = $this->User->find('first', array('conditions' => array('User.id' => $currentUser)));
            //pr($userInfo);die;

            $data = array(
                'fullname' => '' . $userInfo['User']['fullname'],
                'first_name' => '' . $userInfo['User']['first_name'],
                'last_name' => '' . $userInfo['User']['last_name'],
                'email' => '' . $userInfo['User']['username'],
                'category_id' => '' . $userInfo['User']['category_id'],
                'category_name' => '' . $this->userCategories($userInfo['User']['category_id']),
                'lat' => '' . $userInfo['User']['lat'],
                'long' => '' . $userInfo['User']['long'],
                'address' => '' . $userInfo['User']['address'],
                'city' => '' . $userInfo['User']['city'],
                'state' => '' . $userInfo['User']['state'],
                'country' => '' . $userInfo['User']['country'],
                'image' => $this->General->getUploadImage($userInfo['User']['image'], 'users', 160, 160),
            );


            $arr = array("replyCode" => "success", "replyMsg" => $this->General->getMessage('get_user_profile_USER_FOUND'), "data" => $data);
            $output = $this->Json->encode($arr);
        } else {

            $arr = array("replyCode" => "error", "replyMsg" => $this->General->getMessage('provide_complete_technical_detail'));
            $output = $this->Json->encode($arr);
        }

        echo $output;
        exit;
    }

    /**
     * return user selected categories title
     * @param type $category_id
     * @return type 
     */
    function userCategories($category_id) {
        $this->loadModel('SiteCategory');

        $listUserCategory = explode(',', $category_id);

        $listSiteCategory = $this->SiteCategory->find('list', array('conditions' => array('SiteCategory.id' => $listUserCategory)));

        return implode(',', $listSiteCategory);
    }

    /**
     * change password
     * http://192.168.0.79/peggd/users/changePassword/sid:MjQ
     * params: old_password, new_password, conf_password
     */
    function changePassword() {
        $currentUser = $this->Auth->user('id');

        if (!empty($currentUser)) {


            $userInfo = $this->User->find('first', array('conditions' => array('User.id' => $currentUser), 'fields' => 'User.password'));


            if (!empty($_POST['old_password']))
                $oldPassword = $this->Auth->password(trim($_POST['old_password']));
            else
                $oldPassword = null;

            if (trim($userInfo['User']['password']) != $oldPassword) {
                $arr = array("replyCode" => "error", "replyMsg" => $this->General->getMessage('old_password_not_matched'));
                echo $this->Json->encode($arr);
                exit;
            } elseif (trim($_POST['new_password']) == '' || trim($_POST['conf_password']) == '') {
                $arr = array("replyCode" => "error", "replyMsg" => $this->General->getMessage('enter_new_password_and_confirm_password_both'));
                echo $this->Json->encode($arr);
                exit;
            } elseif (trim($_POST['new_password']) != trim($_POST['conf_password'])) {
                $arr = array("replyCode" => "error", "replyMsg" => $this->General->getMessage('New_Password_and_Confirm_Password_did_not_match'));
                echo $this->Json->encode($arr);
                exit;
            } elseif (trim($_POST['new_password']) == trim($_POST['conf_password'])) {

                $this->request->data['User']['password'] = $_POST['new_password'];

                $this->User->id = $currentUser;
                if ($this->User->saveField('password', $this->request->data['User']['password']))
                    $arr = array("replyCode" => "success", "replyMsg" => $this->General->getMessage('change_password_successfully'));
                else
                    $arr = array("replyCode" => "error", "replyMsg" => $this->General->getMessage('user_password_could_not_change'));


                echo $this->Json->encode($arr);
                exit;
            }
        } else {

            $arr = array("replyCode" => "error", "replyMsg" => $this->General->getMessage('provide_complete_technical_detail'));
            $output = $this->Json->encode($arr);
        }

        echo $output;

        exit;
    }

    /**
     * edit user profile
     * http://192.168.0.122/peggd/users/editProfile/sid:MjQ
     * params: name, last_name, lat, long, image, category_id('1,3,5')
     */
    function editProfile() {
        $currentUser = $this->Auth->user('id');

        if (empty($_POST['lat']) || empty($_POST['long']) || empty($_POST['name'])) {
            $arr = array("replyCode" => "error", "replyMsg" => $this->General->getMessage('provide_complete_technical_detail'));
            echo $this->Json->encode($arr);
            exit;
        }

        //get address, city, state, country by passing lat, long
        $responce = "http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $_POST['lat'] . ',' . $_POST['long'] . '&sensor=true';

        $result = $this->General->run_curl($responce);
        $addUser['first_name'] = $_POST['name'];
        $addUser['fullname'] = $_POST['name'];
        if (!empty($_POST['last_name']))
            $addUser['last_name'] = $_POST['last_name'];

        $addUser['category_id'] = $_POST['category_id'];
        $addUser['lat'] = $_POST['lat'];
        $addUser['long'] = $_POST['long'];
        if (!empty($result->results[0]->formatted_address))
            $addUser['address'] = $result->results[0]->formatted_address;
        if (!empty($result->results[0]->address_components[4]->long_name))
            $addUser['city'] = $result->results[0]->address_components[4]->long_name;
        if (!empty($result->results[0]->address_components[5]->long_name))
            $addUser['state'] = $result->results[0]->address_components[5]->long_name;
        if (!empty($result->results[0]->address_components[6]->long_name))
            $addUser['country'] = $result->results[0]->address_components[6]->long_name;


        if (!empty($currentUser)) {

            //upload profile image 
            if (!empty($_FILES['image'])) {

                $file = $_FILES['image'];
                $destination = realpath('../../app/webroot/uploads/users') . '/';

                $imgName = time() . '_' . $file['name'];
                if (move_uploaded_file($file['tmp_name'], $destination . $imgName)) {
                    $addUser['image'] = $imgName;

                    //Delete old image
                    $userInfo = $this->User->find('first', array('conditions' => array('User.id' => $currentUser), 'fields' => array('User.image'), 'resursive' => '-1'));
                    @unlink('uploads/users/' . $userInfo['User']['image']);
                } else {
                    unset($_FILES['image']);
                }
            }

            $this->User->id = $currentUser;

            if ($this->User->save($addUser)) {


                ######################################   SEND PUSH NOTIFICATION      ##############################################
                // NOTE :- when any user update profile then followrs get push
                $this->loadModel('Follower');

                $getFollowers = $this->Follower->find('all', array('conditions' => array('Follower.follower_id' => $currentUser, 'User.device_token !=' => ""), 'group' => 'Follower.follower_id', 'fields' => array('User.id', 'User.device_token'), 'recursive' => 1));
                //pr($getFollowers);die;
                foreach ($getFollowers as $value) {
                    //$getDeiceToken = $value;
                    $getDeiceToken = $value['User']['device_token'];

                    if (!empty($getDeiceToken)) {
                        $message = $this->Auth->user('fullname') . " has updated profile";
                        $type = "isRevised";
                        $this->General->iphone_push_notification($getDeiceToken, $message, $type, '', $value['User']['id'], $type);
                    }
                }
                ######################################   END PUSH NOTIFICATION CODE  ##############################################


                $arr = array("replyCode" => "success", "replyMsg" => $this->General->getMessage('profile_updation_PROFILE_UPDATED'));
            } else {
                $arr = array("replyCode" => "error", "replyMsg" => $this->General->getMessage('profile_updation_ERROR_PROFILE_CANT_UPDATED'));
            }
        } else {

            $arr = array("replyCode" => "error", "replyMsg" => $this->General->getMessage('provide_complete_technical_detail'));
        }

        echo $this->Json->encode($arr);
        exit;
    }

    /**
     * check application version
     * http://192.168.0.122/peggd/users/checkAppVersion
     * method: post
     * params: appVersion
     */
    function checkAppVersion() {
        $this->loadModel('SiteSetting');

        //check app version 
        $siteSettingInfo = $this->SiteSetting->find('first', array('conditions' => array('key' => 'Site.version')));
        if ($siteSettingInfo['SiteSetting']['value'] <= $_POST['appVersion'])
            $isEqualVersion = '1';
        else
            $isEqualVersion = '0';

        //check it is mandetory or not
        $siteSettingInfo = $this->SiteSetting->find('first', array('conditions' => array('key' => 'Site.isMandetory')));
        if ($siteSettingInfo['SiteSetting']['value'])
            $isMandatory = '1';
        else
            $isMandatory = '0';

        $arr = array("replyCode" => "success", "replyMsg" => "", "isEqualVersion" => $isEqualVersion, "isMandatory" => $isMandatory);
        echo $this->Json->encode($arr);
        exit;
    }

    /**
     * copy profile image from facebook
     * http://192.168.0.122/peggd/users/copyImageFromFB
     * support24hour.com/workplace2/peggd/users/copyImageFromFB
     */
    function copyImageFromFB() {
        //get fb users which not have profile image
        $fbUsers = $this->User->find('list', array('conditions' => array('User.facebook_id != ' => "", 'User.facebook_id != ' => "0", 'User.image' => ""), 'fields' => array('User.facebook_id')));

        //upload profile image into users folder & update users table in image field
        foreach ($fbUsers as $id => $user) {
            $url = 'http://graph.facebook.com/' . $user . '/picture?type=large';
            echo $url;
            echo '<br>';

            $destination = realpath('../../app/webroot/uploads/users') . '/';
            $newfile = $user . '.jpg';
            if (copy($url, $destination . $newfile)) {
                //save image name into users table
                $this->User->id = $id;
                $this->User->saveField('image', $newfile);
            }
        }
        exit;
    }

    /**
     * get user profile 
     * http://192.168.0.122/peggd/users/userProfileFromFB
     * support24hour.com/workplace2/peggd/users/userProfileFromFB
     * peggd.com/users/userProfileFromFB
     */
    function userProfileFromFB() {
        //get fb users which not have profile image
        $fbUsers = $this->User->find('list', array('conditions' => array('User.facebook_id != ' => "", 'User.facebook_id != ' => "0", 'User.last_name' => ""), 'fields' => array('User.facebook_id')));

        //upload profile image into users folder & update users table in image field
        foreach ($fbUsers as $id => $user) {
            $url = 'http://graph.facebook.com/' . $user;

            $result = json_decode(file_get_contents($url, true));
            pr($result->last_name); //die('done');

            $this->User->id = $id;
            $this->User->saveField('last_name', $result->last_name);
        }
        exit;
    }

    function edit_Profile_info() {
        $currentUser = '';
        if ($this->Auth->user('id') != "") {
            $currentUser = $this->Auth->user('id');
            $this->set('id', $this->Auth->user('id'));
        }

        if ($this->request->is('post') || $this->request->is('put')) {

            if (!empty($this->request->data) && $currentUser != '') {

                $this->request->data['User']['id'] = $currentUser;
                $int = array();
                foreach ($this->request->data['User']['intrested_in'] as $intrests) {
                    if ($intrests != 0) {
                        $int[] = $intrests;
                    }
                }
                if (count($int) > 0) {
                    $this->request->data['User']['intrested_in'] = join(",", $int);
                } else {
                    $this->request->data['User']['intrested_in'] = '';
                }
                /* Change Password */
                if (isset($this->request->data['User']['old_password']) && $this->request->data['User']['old_password'] != '') {

                    $userInfo = $this->User->find('first', array('conditions' => array('User.id' => $currentUser), 'fields' => 'User.password'));
                    $oldPassword = $this->Auth->password(trim($this->request->data['User']['old_password']));

                    if ($userInfo['User']['password'] == $oldPassword) {
                        if ($this->request->data['User']['new_password'] != '' && $this->request->data['User']['confirm_password'] != '') {
                            if ($this->request->data['User']['new_password'] == $this->request->data['User']['confirm_password']) {
                                $this->request->data['User']['password'] = $this->request->data['User']['new_password'];
                            } else {
                                $this->Session->write('messages', 'error');
                                $this->Session->setFlash(__('New password and confirm password did not match', true));
                                $this->redirect(array('action' => 'edit_profile_info'));
                                exit;
                            }
                        } else {
                            $this->Session->write('messages', 'error');
                            $this->Session->setFlash(__('Please enter new password and confirm password', true));
                            $this->redirect(array('action' => 'edit_profile_info'));
                            exit;
                        }
                    } else {
                        $this->Session->write('messages', 'error');
                        $this->Session->setFlash(__('old_password_not_matched', true));
                        $this->redirect(array('action' => 'edit_profile_info'));
                        exit;
                    }
                }

                if ($this->User->save($this->request->data['User'])) {
                    $this->Session->write('Auth', $this->User->read(null, $this->Auth->User('id')));
                    $this->redirect(array('action' => 'myprofile'));
                } else {
                    $this->Session->write('messages', 'error');
                    $this->Session->setFlash(__('Profile not updated,Please try again', true));
                }
            }
        }

        if (empty($this->request->data) && $this->Auth->user('id') != '') {
            $this->request->data = $this->User->read(null, $this->Auth->user('id'));
        }
    }

    function myprofile() {

        $currentUser = '';
        if ($this->Auth->user('id') != "") {
            $currentUser = $this->Auth->user('id');
            $this->set('id', $this->Auth->user('id'));
        }
        $conditions = array('User.id' => $currentUser, 'User.status' => 1);
        $userdetail = $this->User->find('first', array('conditions' => $conditions));


        if (count($userdetail) > 0) {
            if ($userdetail['User']['intrested_in'] != '') {
                $intrests = explode(',', $userdetail['User']['intrested_in']);

                foreach ($intrests as $intrest) {
                    $intrestIn[] = $this->General->getCategoryName($intrest);
                }
                $userdetail['User']['intrested_in'] = (join(', ', $intrestIn));
            }


            $this->set('userdetail', $userdetail);
        }
    }

    function img_bnr_uplod() {
        $this->layout = false;
        $this->autoRender = false;
        return 'success';
    }

    function changebannerimageupload($type = null) {
        if (empty($type)) {
            $type = 'banners';
        }
        $destination = WWW_ROOT . 'uploads' . DS . $type . DS;
        $chkdestination = Configure::read('Site.url') . 'uploads/' . $type . '/';
        $file = $_FILES['uploadfile'];

        $chkimage = getimagesize($file['tmp_name']);

        if ($chkimage['mime'] != 'image/psd') {
            if ($type == 'logo' && $bannertype == 'logo') {
                $filesizesfsads = filesize($file[tmp_name]);
                if ($filesizesfsads > 2097152) {
                    echo $filesizeerror = 'filesize';
                } else {

                    list($width, $height, $type, $attr) = getimagesize($file['tmp_name']);
                    $filename = time() . $file['name'];
                    if ($width <= 160 && $height <= 80) {
                        move_uploaded_file($file['tmp_name'], $destination . DS . $filename);
                    } else if ($width > 160 && $height > 80) {
                        $this->Image->setImage($file['tmp_name']);
                        $this->Image->resizeTo(160, 80, 'maxWidth');
                        $this->Image->saveImage($destination . DS . $filename);
                    } else {
                        $this->Image->setImage($file['tmp_name']);
                        $this->Image->resizeTo(160, 80, 'maxHeight');
                        $this->Image->saveImage($destination . DS . $filename);
                    }
                    $companyArr['Company']['logo'] = $filename;

                    $this->Company->save($companyArr, false);
                    $this->Session->write('Auth.User.Company.logo', $filename);
                }
            } else if ($type == 'banners') {

                $filesizesfsads = filesize($file['tmp_name']);
                if ($filesizesfsads > 2097152000) {
                    echo $filesizeerror = 'filesize';
                } else {
                    list($width, $height, $type, $attr) = getimagesize($file['tmp_name']);
                    $filename = time() . $file['name'];
                    if ($width <= 1103 && $height <= 280) {
                        move_uploaded_file($file['tmp_name'], $destination . DS . $filename);
                    } elseif ($width > 1103 && $height <= 280) {
                        $this->Image->setImage($file['tmp_name']);
                        $this->Image->resizeTo(1103, 280, 'maxWidth');
                        $this->Image->saveImage($destination . DS . $filename);
                    } else {
                        $this->Image->setImage($file['tmp_name']);
                        $this->Image->resizeTo(1103, 280, 'maxWidth');
                        $this->Image->saveImage($destination . DS . $filename);
                    }
                }
            }

            if (!isset($result)) {
                echo $filename;
            } else {

                echo $errors = $this->Upload->errors;
            }
        } else {
            echo $errors = 'error';
        }
        exit();
    }

    function upload_cover() {
        $this->layout = false;
        $this->autoRender = false;
        $folderName = WWW_ROOT . "uploads" . DS . "users" . DS . $this->Auth->user('id');

        if (!file_exists($folderName)) {
            chmod($folderName, 0777);
            mkdir($folderName);
        }
        $file = $_FILES['uploadfile'];
        $filename = 'original.jpg'; //.pathinfo($file['name'],PATHINFO_EXTENSION);
        if (file_exists($folderName . DS . "original.jpg")) {
            unlink($folderName . DS . "original.jpg");
        }
        if (file_exists($folderName . DS . "cover.jpg")) {
            unlink($folderName . DS . "cover.jpg");
        }


        if (move_uploaded_file($file['tmp_name'], $folderName . DS . $filename)) {

            echo Configure::read('Site.url') . 'uploads/users/' . $this->Auth->user('id') . '/original.jpg?' . time();
        }
        exit;
    }

    function upload_profile_image() {
        $this->layout = false;
        $this->autoRender = false;
        $folderName = WWW_ROOT . "uploads" . DS . "users" . DS . $this->Auth->user('id') . DS . 'profile';

        if (!file_exists($folderName)) {
            chmod($folderName, 0777);
            mkdir($folderName);
        }

        $file = $_FILES['uploadfile'];
        $filename = 'original.jpg'; //.pathinfo($file['name'],PATHINFO_EXTENSION);
        if (file_exists($folderName . DS . "original.jpg")) {
            unlink($folderName . DS . "original.jpg");
        }

        list($width, $height, $type, $attr) = getimagesize($file['tmp_name']);
        $this->Image->setImage($file['tmp_name']);
        $this->Image->resizeTo(500, $height, 'maxWidth');
        $this->Image->saveImage($folderName . DS . $filename);

        $img = Configure::read('Site.url') . 'uploads/users/' . $this->Auth->user('id') . '/profile/original.jpg?' . time();
        echo json_encode(array('img' => $img, 'width' => 500, 'height' => $this->resizeHeightByWidth($width, $height, 500)));


        exit;
    }

    function setpositions() {
        $this->layout = false;
        $this->autoRender = false;
        if (!empty($this->request->data)) {
            $from_top = abs($this->request->data['pos']);
            $default_cover_width = 1103;
            $default_cover_height = 331;
            // includo la classe
            require_once("thumbncrop.inc.php"); //php class for image resizing & cropping
            $folderName = WWW_ROOT . "uploads/users/" . $this->Auth->user('id');
            // valorizzo la variabile
            $tb = new ThumbAndCrop();

            // apro l'immagine
            $tb->openImg($folderName . DS . "original.jpg"); //original cover image

            $newHeight = $tb->getRightHeight($default_cover_width);

            $tb->creaThumb($default_cover_width, $newHeight);

            $tb->setThumbAsOriginal();

            $tb->cropThumb($default_cover_width, 331, 0, $from_top);


            $tb->saveThumb($folderName . DS . "cover.jpg"); //save cropped cover image

            $tb->resetOriginal();

            $tb->closeImg();

            $data['status'] = 200;
            $data['url'] = Configure::read('Site.url') . 'uploads/users/' . $this->Auth->user('id') . '/cover.jpg';
            echo json_encode($data);
        }
    }

    function user_profile_pic() {

        if (isset($this->request->data)) {
            //Get the new coordinates to crop the image.
            $x1 = $this->request->data["x1"];
            $y1 = $this->request->data["y1"];
            $x2 = $this->request->data["x2"];
            $y2 = $this->request->data["y2"];
            $w = $this->request->data["w"];
            $h = $this->request->data["h"];

            //Scale the image to the thumb_width set above
            $scale = 150 / $w;
            $large_image_location = WWW_ROOT . "uploads" . DS . "users" . DS . $this->Auth->user('id') . DS . 'profile' . DS . 'original.jpg';
            $thumb_image_location = WWW_ROOT . "uploads" . DS . "users" . DS . $this->Auth->user('id') . DS . 'profile' . DS . 'profile.jpg';


            $cropped = $this->Image->resizeThumbnailImage($thumb_image_location, $large_image_location, $w, $h, $x1, $y1, $scale);

            exit();
        }
    }

    function resizeHeightByWidth($orgW, $orgH, $width) {
        return floor(($orgH / $orgW) * $width);
    }

    function changeLocation() {
        $this->layout = false;
        $this->autoRender = false;
        $ajax = "";
        if (isset($this->request->data['isAjax']) && $this->request->data['isAjax'] == 1) {
            $ajax = 1;
        }
        $this->Geomap->get_lat_long_byaddress($this->request->data['jsonData'], $ajax);
    }

}
