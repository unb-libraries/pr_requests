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
  drupal_set_title(
    t('PR Group Assistance Request')
  );

  $form['supervisor_approved'] = array(
    '#type' => 'checkbox',
    '#title' => t('Unit manager is aware of and supports this request.'),
    '#prefix' => '<p class="instructions">To request assistance from the PR
      group, please complete and submit this form. A confirmation email will be
      sent upon receipt. If there are any follow up questions, or if consultation
      is required, you will be notified. Please note that requests will be
      prioritized. Once the request is approved, the appropriate individuals on
      the PR Committee will complete the tasks involved by the agreed-upon date.
      </p>',
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
    '#title' => t('PR Category'),
    '#options' => array(
      'Announcement' => t('Announcement'),
      'Event' => t('Event (General)'),
      'Teaching Session/Workshop' => t('Teaching Session/Workshop'),
      'e-Resource' => t('e-Resource'),
      'Services' => t('Services'),
      'Physical Spaces' => t('Physical Spaces'),
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
    '#description' => 'Please provide any vital information concerning your
      request. i.e. Dates, times, locations, product information, etc.',
    '#required' => TRUE,
  );

  $form['conditional-wrapper']['project']['content'] = array(
    '#type' => 'textarea',
    '#title' => t('Content'),
    '#description' => t('Content to be included with the PR Request. i.e.
      information/images to include in Library News article, Library Feature,
      Social Media Posts, etc.'),
    '#required' => TRUE,
  );

  $form['conditional-wrapper']['project']['target'] = array(
    '#type' => 'textarea',
    '#title' => t('Target Audience'),
    '#description' => t('i.e. Faculty, students, general public, UNB/STU,
      etc.'),
    '#required' => TRUE,
  );

  $my_file_limit = variable_get('pr_requests_files_limit');
  $my_file_extensions = variable_get('pr_requests_file_extensions');
  $my_file_maxsize = variable_get('pr_requests_file_maxsize');

  $form['conditional-wrapper']['files'] = array(
    '#type' => 'fieldset',
    '#title' => t('Files'),
    '#description' => t('<p>You may upload up to %my_file_limit files to
      accompany your request. If you have more than %my_file_limitfiles,
      you may upload a .zip file instead.<br />Permitted file extensions
      include: <b>%my_file_extensions</b></p><p><b>Note:</b> the maximum file
      size is %my_file_max_sizeMB</p>', array(
        '%my_file_limit' => $my_file_limit,
        '%my_file_extensions' => $my_file_extensions,
        '%my_file_max_size' => $my_file_maxsize,
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

  // Form widgets for public file uploads.
  for ($i = 1; $i <= $my_file_limit; $i++) {
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
          $my_file_maxsize * 1024 * 1024,
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

  /* Assemble contact information fron authenticated user for FogBugz, etc. in
  hidden form inputs in case we ever need to make these values overrideable via
  visible pre-populated inputs. */
  global $user;
  $user_profile = entity_metadata_wrapper('user', user_load($user->uid));
  $uname = $user_profile->name->value();

  /* Construct full name from user account name fields. */
  $fullname = array($user_profile->field_first_name->value(), $user_profile->field_last_name->value());
  $fullname = trim(implode(' ', $fullname));
  if (!isset($fullname)) {
    /* Use username as backup (required field for user account). */
    $fullname = $uname;
  }

  $myemail = $uname . '@unb.ca';

  $form['fullname'] = array(
    '#type' => 'hidden',
    '#value' => $fullname,
  );
  $form['email'] = array(
    '#type' => 'hidden',
    '#value' => $myemail,
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
    '#value' => variable_get('pr_requests_email_list'),
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
    drupal_set_message(t('Your request has been submitted to the PR Group. We will send you a confirmation email shortly.'));
  }
  else {
    drupal_set_message(t('Your submission request was not successful. If this error persists, please contact the site administrator.'), 'error');
  }
}
