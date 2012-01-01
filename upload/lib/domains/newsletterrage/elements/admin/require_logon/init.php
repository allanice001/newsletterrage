<?php
  global $Session;
  $userid = $Session->get('userid');
  $Session->set('redirect', urlPath(), false);
  if (!$userid) {
    redirect(url($System['pages']['login']));
  }