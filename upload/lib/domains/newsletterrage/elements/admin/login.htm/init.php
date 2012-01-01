<?php
  global $Session, $DB;
  
  $action = get('action');
  $username = get('username');
  $password = get('password');
  $remember = get('remember');
  $userid = $Session->get('userid', '');
  
  if ($action) {
    $userid = $DB->lookup('SELECT userid FROM users WHERE username="'. $username .'" AND password="'. md5($password) .'"');
    $Session->set('userid', $userid);
  }
  
  if ($userid) {
    $Session->set('userid', $userid, ($remember ? true : false), 86400);
    $redirect = $Session->get('redirect');
    redirect(($redirect ? $redirect : url('admin_home')));
  };