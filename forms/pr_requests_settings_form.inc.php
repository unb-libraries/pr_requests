<?php
/**
 * @file
 * Defines the PR Requests settings form.
 */

/**
 * Form constructor for the PR Requests settings form.
 *
 * @see pr_requests_form_validate()
 *
 * @ingroup forms
 */
function pr_requests_settings_form($form, &$form_state) {
  $form = array();

  drupal_set_title(
    t('Design & Communications Request form settings')
  );

  $form['pr_requests_email_list'] = array(
    '#type' => 'textfield',
    '#title' => t('Design & Communication Committee email list:'),
    '#size' => '110',
    '#default_value' => variable_get('pr_requests_email_list', ''),
    '#description' => t('Enter a valid email address - separate multiple addresses with a comma'),
    '#required' => TRUE,
  );

  $form['file_uploads'] = array(
    '#type' => 'fieldset',
    '#title' => t('File upload settings:'),
  );

  $form['file_uploads']['pr_requests_files_limit'] = array(
    '#type' => 'numberfield',
    '#title' => t('Maximum allowable file uploads:'),
    '#description' => t('Enter an upper limit of file attachments'),
    '#default_value' => variable_get('pr_requests_files_limit', 3),
    '#element_validate' => array('element_validate_integer_positive'),
    '#maxlength' => 2,
    '#attributes' => array(
      'min' => 1,
      'max' => 12,
    ),
  );

  $form['file_uploads']['pr_requests_file_extensions'] = array(
    '#type' => 'textfield',
    '#title' => t('Valid file upload types:'),
    '#default_value' => variable_get('pr_requests_file_extensions', 'gif jpg png doc docx pdf zip'),
    '#description' => t('Restrict accepted file extensions, i.e. gif jpg png doc docx pdf zip'),
  );

  $my_max_fsize = file_upload_max_size() / 1024 / 1024;
  $form['file_uploads']['pr_requests_file_maxsize'] = array(
    '#type' => 'numberfield',
    '#title' => t('Maximum allowable file upload size:'),
    '#description' => t('Enter a numeric file size between <b>1</b> and
      <b>%limit</b>', array('%limit' => format_size(file_upload_max_size()))
    ),
    '#default_value' => variable_get('pr_requests_file_maxsize', $my_max_fsize),
    '#element_validate' => array('element_validate_integer_positive'),
    '#maxlength' => 3,
    '#attributes' => array(
      'min' => 1,
      'max' => $my_max_fsize,
    ),
  );

  $form['fogbugz_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Fogbugz credentials:'),
  );

  $form['fogbugz_settings']['pr_requests_fogbugz_email'] = array(
    '#type' => 'textfield',
    '#title' => t('Email'),
    '#default_value' => variable_get('pr_requests_fogbugz_email'),
    '#size' => 50,
    '#maxlength' => 50,
    '#required' => TRUE,
    '#prefix' => '<p>Enter the credentials for the FogBugz account that will be used for API calls in this module.</p>',
  );

  $form['fogbugz_settings']['pr_requests_fogbugz_password'] = array(
    '#type' => 'textfield',
    '#title' => t('Password'),
    '#default_value' => variable_get('pr_requests_fogbugz_password'),
    '#size' => 50,
    '#maxlength' => 50,
    '#required' => TRUE,
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#name' => 'save',
    '#value' => t('Save settings'),
  );
  $form['#submit'][] = 'system_settings_form_submit';

  return $form;
}

/**
 * Form validation handler for pr_requests_settings_form().
 */
function pr_requests_settings_form_validate($form, &$form_state) {
  $form_state['values']['pr_requests_email_list'] = preg_replace('/\s+/', '', $form_state['values']['pr_requests_email_list']);
  $form_state['values']['pr_requests_fogbugz_email'] = preg_replace('/\s+/', '', $form_state['values']['pr_requests_fogbugz_email']);
  $email_list = explode(",", $form_state['values']['pr_requests_email_list']);

  foreach ($email_list as $value) {
    if (!valid_email_address($value)) {
      form_set_error('pr_requests_email_list', t('Invalid email format: please correct the Communication Committee email list (multiple addresss must be separated with a comma).'));
    }
  }

  if (!valid_email_address($form_state['values']['pr_requests_fogbugz_email'])) {
    form_set_error('pr_requests_fogbugz_email', t('Invalid email format: please correct the FogBugz e-mail.'));
  }
}
