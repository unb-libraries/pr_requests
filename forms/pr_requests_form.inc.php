<?php
/**
 * @file
 * Defines the PR Requests form.
 */

/**
 * Form constructor for the PR Requests form.
 *
 * @see pr_requests_form_submit()
 *
 * @ingroup forms
 */
function pr_requests_form($form, &$form_state) {
  $form = array();

  drupal_set_title(
    t('Design & Communications Request (UNBF)')
  );

  // Check if PR Requests settings required fields have been completed.
  $pr_settings_email_list = variable_get('pr_requests_email_list');
  $pr_settings_fb_user = variable_get('pr_requests_fogbugz_email');
  $pr_settings_fb_pass = variable_get('pr_requests_fogbugz_password');
  if ($pr_settings_email_list == NULL || $pr_settings_fb_user == NULL || $pr_settings_fb_pass == NULL) {
    drupal_set_message(t('PR Requests configuration is not yet complete, please contact the site administrator.'), 'error');
    return;
  }

  $unit_approval_message = t('To request Graphic Design support and/or assistance from the Communications Committee, please complete and submit this form.
  A confirmation email will be sent upon receipt. If there are any follow up questions, or if consultation is required, you will be notified.
  Please note that requests will be prioritized. Once the request is approved, the appropriate individuals will complete the tasks involved
  by the agreed-upon deadline. Ideally 2 - 4 weeks lead-time is recommended for promotional campaigns/assistance with events/workshops.');

  // Attach module css and/or javascript files for module page only.
  $form['#attached']['css'] = array(
    drupal_get_path('module', 'pr_requests') . '/css/pr_requests.css',
  );

  $form['supervisor_approved'] = array(
    '#type' => 'checkbox',
    '#title' => t('Unit manager is aware of and supports this request.'),
    '#prefix' => "<p class=\"instructions\">$unit_approval_message</p>",
    '#required' => TRUE,
  );

  $form['conditional-wrapper'] = array(
    '#type' => 'container',
    '#attributes' => array(
      'class' => array(
        'pr-request-form-toggle',
      ),
    ),
    '#states' => array(
      'visible' => array(
        ':input[name="supervisor_approved"]' => array('checked' => TRUE),
      ),
    ),
  );

  $form['conditional-wrapper']['project'] = array(
    '#type' => 'fieldset',
    '#title' => t('Project Information:'),
  );

  $form['conditional-wrapper']['project']['deadline'] = array(
    '#type' => 'date_popup',
    '#title' => t('Deadline'),
    '#required' => TRUE,
    '#date_format' => 'Y-m-d',
  );

  $form['conditional-wrapper']['project']['category'] = array(
    '#type' => 'select',
    '#title' => t('Category'),
    '#options' => array(
      'Announcement' => t('Announcement'),
      'Event' => t('Event'),
      'Teaching Session/Workshop' => t('Teaching Session/Workshop'),
      'Services' => t('Services'),
      'e-Resource' => t('e-Resource'),
      'Physical Space' => t('Physical Space'),
      'Web Design' => t('Web Design'),
      'Other' => t('Other'),
    ),
    '#required' => TRUE,
  );

  $form['conditional-wrapper']['project']['other'] = array(
    '#type' => 'textfield',
    '#title' => t('Please specify other category:'),
    '#default_value' => NULL,
    '#states' => array(
      'visible' => array(
        ':input[name="category"]' => array('value' => 'Other'),
      ),
      'required' => array(
        ':input[name="category"]' => array('value' => 'Other'),
      ),
    ),
  );

  $form['conditional-wrapper']['project']['about'] = array(
    '#type' => 'textarea',
    '#title' => 'About the Project',
    '#description' => t('Please provide any vital information concerning your
      request. i.e. dates, times, locations, product information, etc.'),
    '#required' => TRUE,
  );

  $form['conditional-wrapper']['project']['graphic'] = array(
    '#type' => 'textarea',
    '#title' => 'Graphic material',
    '#description' => t('i.e. brochure, poster, website design, image, etc.'),
    '#required' => TRUE,
  );

  $form['conditional-wrapper']['project']['content'] = array(
    '#type' => 'textarea',
    '#title' => t('Content'),
    '#description' => t('Please provide necessary: text, images, logos, date, themes, any specific sizes/dimensions, etc.'),
    '#required' => TRUE,
  );

  $form['conditional-wrapper']['project']['target'] = array(
    '#type' => 'textarea',
    '#title' => t('Target Audience'),
    '#description' => t('i.e. Faculty, students, general public, UNB/STU,
      etc.'),
    '#required' => TRUE,
  );

  $request_file_limit = variable_get('pr_requests_files_limit');
  $request_file_extns = variable_get('pr_requests_file_extensions');
  $request_file_max_size = variable_get('pr_requests_file_maxsize');

  $form['conditional-wrapper']['files'] = array(
    '#type' => 'fieldset',
    '#title' => t('Files'),
    '#description' => t('<p>You may upload up to %request_file_limit files to
      accompany your request. If you have more than %request_file_limitfiles,
      you may upload a .zip file instead.<br />Permitted file extensions
      include: <b>%request_file_extns</b></p><p><b>Note:</b> the maximum file
      size is %my_file_max_sizeMB</p>', array(
        '%request_file_limit' => $request_file_limit,
        '%request_file_extns' => $request_file_extns,
        '%my_file_max_size' => $request_file_max_size,
      )
    ),
  );

  $form['conditional-wrapper']['files']['file_input_container'] = array(
    '#type' => 'container',
    '#title' => 'File Input Container',
    '#attributes' => array(
      'id' => array(
        'pr-requests-files-wrapper',
      ),
    ),
  );

  // Generate file upload widgets.
  for ($i = 1; $i <= $request_file_limit; $i++) {
    $form['conditional-wrapper']['files']['file_input_container']['file' . $i] = array(
      '#title' => 'File' . $i,
      '#title_display' => 'invisible',
      '#type' => 'managed_file',
      '#upload_location' => 'public://pr_requests/',
      '#upload_validators' => array(
        'file_validate_extensions' => array(
          'gif jpg png doc docx pdf zip',
        ),
        'file_validate_size' => array(
          $request_file_max_size * 1024 * 1024,
        ),
      ),
    );
  }

  $form['conditional-wrapper']['submit_button'] = array(
    '#type' => 'submit',
    '#value' => t('Send request'),
    '#attributes' => array(
      'class' => array(
        'pr-requests-send-button',
      ),
    ),
    '#submit' => array(
      'pr_requests_submit',
    ),
  );

  // Assemble contact information fron authenticated user for FogBugz, etc. in
  // hidden form inputs in case we ever need to make these values overrideable
  // via visible pre-populated inputs.
  global $user;
  $user_profile = entity_metadata_wrapper('user', user_load($user->uid));
  $uname = $user_profile->name->value();

  // Construct full name from user account name fields, using Drupal username
  // (i.e. UNB userid) as backup.
  $fullname = array($user_profile->field_first_name->value(), $user_profile->field_last_name->value());
  $fullname = trim(implode(' ', $fullname));
  if (trim($fullname) == '') {
    $fullname = $uname;
  }

  // The userid@unb.ca address is constant and prefered over fname.lname@unb.ca.
  $user_email = $uname . '@unb.ca';

  $form['fullname'] = array(
    '#type' => 'hidden',
    '#value' => $fullname,
  );
  $form['email'] = array(
    '#type' => 'hidden',
    '#value' => $user_email,
  );
  $form['department'] = array(
    '#type' => 'hidden',
    '#value' => $user_profile->field_position_title->value(),
  );
  $form['phone'] = array(
    '#type' => 'hidden',
    '#value' => $user_profile->field_phone_number->value(),
  );

  $form['email_list'] = array(
    '#type' => 'hidden',
    '#value' => $pr_settings_email_list,
  );

  return $form;
}

/**
 * Form submission handler for pr_requests_form().
 */
function pr_requests_submit($form, &$form_state) {
  module_load_include('php', 'pr_requests', 'inc/fogbugz.inc');

  $token = _pr_requests_get_fogbugz_token();
  if ($token == NULL) {
    drupal_set_message(t('Connection to FogBugz failed. If this error persists, please contact the site administrator.'), 'error');
    return;
  }
  $success = _pr_requests_create_fogbugz_ticket($token, $form_state);
  if ($success) {
    drupal_set_message(t('Your request has been submitted to the Communications Committee. We will send you a confirmation email shortly.'));
  }
  else {
    drupal_set_message(t('Your submission request was not successful. If this error persists, please contact the site administrator.'), 'error');
  }
}
