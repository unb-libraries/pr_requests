<?php
/**
 * @file
 * Provides an interface to FogBugz via the Fogbugz API.
 */

/**
 * Generate and return FogBugz token.
 *
 * Authenticates Mr. Robot through the FogBugz API and generates a token for
 * subsequent API calls.
 *
 * @return string
 *   Token for FogBugz API calls
 */
function _pr_requests_get_fogbugz_token() {
  $curl_handle = curl_init('https://support.lib.unb.ca/api.asp?cmd=logon');

  curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($curl_handle, CURLOPT_POST, TRUE);
  $ch_post_data = array(
    'email' => variable_get('pr_requests_fogbugz_email'),
    'password' => variable_get('pr_requests_fogbugz_password'),
  );
  curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $ch_post_data);

  $curl_response = curl_exec($curl_handle);
  $token = NULL;
  if ($curl_response) {
    $xml = simplexml_load_string($curl_response, 'SimpleXMLElement', LIBXML_NOCDATA);
    $token = (string) $xml->token;
  }
  curl_close($curl_handle);

  return $token;
}

/**
 * Create a ticket in FogBugz system based on user input from the ticket form.
 *
 * @param string $token
 *   Token for FogBugz API calls.
 * @param array $form_state
 *   Form state.
 *
 * @return bool
 *   TRUE if ticket creation successfully returned a case ID, FALSE if not
 */
function _pr_requests_create_fogbugz_ticket($token, array $form_state) {
  $description = "Contact Information:\n" . $form_state['values']['fullname'] . "\n";
  if (isset($form_state['values']['department'])) {
    $description .= $form_state['values']['department'] . "\n";
  }
  $description .= $form_state['values']['email'] . "\n";
  if (isset($form_state['values']['phone'])) {
    $description .= $form_state['values']['phone'] . "\n";
  }
  $description .= "\nDeadline:\n" .
    $form_state['values']['deadline'] . "\n\nPR Category:\n" .
    $form_state['values']['category'];

  // If PR Category=Other, include required text from conditional form input.
  if ($form_state['values']['category'] == 'Other') {
    $description .= ": " . $form_state['values']['other'];
  }
  $description .= "\n\nAbout the Project:\n" .
    $form_state['values']['about'] . "\n\nContent:\n" .
    $form_state['values']['content'] . "\n\nTarget Audience:\n" .
    $form_state['values']['target'] . "\n";

  // Add space after commas for readabiliy when have multiple email addresses.
  $bcc = preg_replace('/,/', ', ', $form_state['values']['email_list']);

  $ticket_information = array(
    'project' => 'PR Requests',
    'title' => 'PR Request from ' . $form_state['values']['fullname'],
    'category' => 'PR Request',
    'description' => $description,
    'email' => $form_state['values']['fullname'] . " <" . $form_state['values']['email'] . ">",
    'email_list' => $bcc,
  );

  $curl_handle = curl_init('https://support.lib.unb.ca/api.asp?cmd=new');
  curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($curl_handle, CURLOPT_POST, TRUE);
  curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
  $ch_post_data = array(
    'token' => $token,
    'sProject' => $ticket_information['project'],
    'sTitle' => $ticket_information['title'],
    'sCategory' => $ticket_information['category'],
    'sEvent' => $ticket_information['description'],
    'sCustomerEmail' => $ticket_information['email'],
    'ixMailbox' => 1,
  );

  $file_count = 0;
  foreach ($form_state['values'] as $field_name => $fid) {
    if (preg_match('/(file)\d/', $field_name) > 0 && $fid > 0) {
      $file_count++;
      $file = file_load($fid);
      $file_uri = drupal_realpath($file->uri);
      $ch_post_data['File' . $file_count] = new CurlFile($file_uri);
    }
  }
  $ch_post_data['nFileCount'] = $file_count;

  curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $ch_post_data);
  $curl_response = curl_exec($curl_handle);

  $success = FALSE;
  $case_id = NULL;
  if ($curl_response) {
    $xml = simplexml_load_string($curl_response, 'SimpleXMLElement', LIBXML_NOCDATA);
    $case_id = (string) $xml->case['ixBug'];
    if ($case_id != NULL) {
      $ticket_information['case_id'] = $case_id;
      $success = TRUE;
      _pr_requests_send_confirmation_email($token, $ticket_information);
    }
  }
  curl_close($curl_handle);
  return $success;
}

/**
 * Construct and send email notifications on successful submissions.
 *
 * @param string $token
 *   Token for FogBugz API calls.
 * @param array $ticket_information
 *   Associative array of information from the newly submitted ticket.
 */
function _pr_requests_send_confirmation_email($token, array $ticket_information) {
  $message = "PR Request submitted. Reference number for this ticket is " . $ticket_information['case_id'] . ".\n\n";
  $message .= "PR Request Details:\n-------------------\n\n" . $ticket_information['description'] . "\n";

  $curl_handle = curl_init('https://support.lib.unb.ca/api.asp?cmd=forward');
  curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($curl_handle, CURLOPT_POST, TRUE);
  $ch_post_data = array(
    'token' => $token,
    'ixBug' => $ticket_information['case_id'],
    'sFrom' => 'libsystems@unb.ca',
    'sTo' => $ticket_information['email'],
    'sBCC' => $ticket_information['email_list'],
    'sSubject' => 'PR Request submitted (Case ' . $ticket_information['case_id'] . ')',
    'sEvent' => $message,
  );
  curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $ch_post_data);
  curl_exec($curl_handle);
  curl_close($curl_handle);
}
