<?php
/**
 * Implements hook_html_head_alter().
 * This will overwrite the default meta character type tag with HTML5 version.
 */
function responsive_html_head_alter(&$head_elements) {
  $head_elements['system_meta_content_type']['#attributes'] = array(
    'charset' => 'utf-8'
  );
}

/**
 * Insert themed breadcrumb page navigation at top of the node content.
 */
function responsive_breadcrumb($variables) {
  $breadcrumb = $variables['breadcrumb'];
  if (!empty($breadcrumb)) {
    // Use CSS to hide titile .element-invisible.
    $output = '<h2 class="element-invisible">' . t('You are here') . '</h2>';
    // comment below line to hide current page to breadcrumb
	$breadcrumb[] = drupal_get_title();
    $output .= '<nav class="breadcrumb">' . implode(' » ', $breadcrumb) . '</nav>';
    return $output;
  }
}

/**
 * Override or insert variables into the html template.
 */
function responsive_process_html(&$vars) {
  // Hook into color.module
  if (module_exists('color')) {
    _color_html_alter($vars);
  }
}

/**
 * Override or insert variables into the page template.
 */
function responsive_process_page(&$variables) {
  // Hook into color.module.
  if (module_exists('color')) {
    _color_page_alter($variables);
  }
 
}

/**
 * Override or insert variables into the page template.
 */
function responsive_preprocess_page(&$vars) {
  if (isset($vars['main_menu'])) {
    $vars['main_menu'] = theme('links__system_main_menu', array(
      'links' => $vars['main_menu'],
      'attributes' => array(
        'class' => array('links', 'main-menu', 'clearfix'),
      ),
      'heading' => array(
        'text' => t('Main menu'),
        'level' => 'h2',
        'class' => array('element-invisible'),
      )
    ));
  }
  else {
    $vars['main_menu'] = FALSE;
  }
  if (isset($vars['secondary_menu'])) {
    $vars['secondary_menu'] = theme('links__system_secondary_menu', array(
      'links' => $vars['secondary_menu'],
      'attributes' => array(
        'class' => array('links', 'secondary-menu', 'clearfix'),
      ),
      'heading' => array(
        'text' => t('Secondary menu'),
        'level' => 'h2',
        'class' => array('element-invisible'),
      )
    ));
  }
  else {
    $vars['secondary_menu'] = FALSE;
  }
}

/**
 * Duplicate of theme_menu_local_tasks() but adds clearfix to tabs.
 */
function responsive_menu_local_tasks(&$variables) {
  $output = '';

  if (!empty($variables['primary'])) {
    $variables['primary']['#prefix'] = '<h2 class="element-invisible">' . t('Primary tabs') . '</h2>';
    $variables['primary']['#prefix'] .= '<ul class="tabs primary clearfix">';
    $variables['primary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['primary']);
  }
  if (!empty($variables['secondary'])) {
    $variables['secondary']['#prefix'] = '<h2 class="element-invisible">' . t('Secondary tabs') . '</h2>';
    $variables['secondary']['#prefix'] .= '<ul class="tabs secondary clearfix">';
    $variables['secondary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['secondary']);
  }
  return $output;
}

/**
 * Override or insert variables into the node template.
 */
function responsive_preprocess_node(&$variables) {
  $node = $variables['node'];
  if ($variables['view_mode'] == 'full' && node_is_page($variables['node'])) {
    $variables['classes_array'][] = 'node-full';
  }
}

function responsive_page_alter($page) {
  // <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
  $viewport = array(
    '#type' => 'html_tag',
    '#tag' => 'meta',
    '#attributes' => array(
    'name' =>  'viewport',
    'content' =>  'width=device-width, initial-scale=1, maximum-scale=1'
    )
  );
  drupal_add_html_head($viewport, 'viewport');
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function responsive_form_contact_site_form_alter(&$form, &$form_state) {
  // Add a required textfield to collect
  // users phone number to the form array.
  $form['company'] = array(
    '#title'    => t('Your Company'),
    '#type'     => 'textfield',
    '#required' => TRUE,
  );
  $form['phone'] = array(
    '#title'    => t('Your Phone Number'),
    '#type'     => 'textfield',
    '#required' => TRUE,
  );
  $form['name']['#title'] = t('Your Name');
  $form['mail']['#title'] = t('Your E-Mail Address');
  $form['subject']['#required'] = FALSE;
  $form['message']['#title'] = t('Tell Us about your project');
  //unset ($form['subject']);
  //unset ($form['copy']);
  //$form['subject']['#type'] = 'hidden';
  //$form['copy']['#type'] = 'hidden';
  $form['subject']['#access'] = FALSE;
  $form['copy']['#access'] = FALSE;
  // We do not allow anonymous users to send themselves a copy
  // because it can be abused to spam people.
  //$form['copy'] = array(
  //  '#type' => 'checkbox',
  // '#title' => t('Send yourself a copy.'),
  //  '#access' => $user->uid,
  //);
 
  // Re-sort the form so that we can ensure our phone number field appears
  // in the right spot. We're going to do this by building an array of the
  // form field keys, then looping through each and setting their weight.
  // 
  // Default fields on the contact form are:
  // name    (Users Name)
  // mail    (Users Email Address)
  // subject (Email Subject)
  // cid     (Category select list,
  //            only visible if multiple contact categories are defined)
  // message (Message textarea)
  // copy    ('Send me a copy' checkbox)
  // submit  (Submit button)
 
  $order = array(
    'name',
    'company',
    'mail',
    'phone',
    'subject',
    'cid',
    'message',
    'copy',
    'submit'
  );
 
  foreach ($order as $key => $field) {
    // Set/Reset the field's 
    // weight to the array key value
    // from our order array. 
    $form[$field]['#weight'] = $key;
  }
}

/**
 * Implements hook_mail_alter().
 */
function responsive_mail_alter(&$message) {
  // We only want to alter the email if it's being
  // generated by the site-wide contact form page.
  if ($message['id'] == 'contact_site_form') {
    $message['body'][] = t('Company') .': '. $message['params']['company'];
    $message['body'][] = t('Phone') .': '. $message['params']['phone'];
  }
}

/**
 * Add javascript files for front-page jquery slideshow.
 */
if (drupal_is_front_page()) {
  drupal_add_js(drupal_get_path('theme', 'responsive') . '/js/jquery.flexslider-min.js');
  drupal_add_js(drupal_get_path('theme', 'responsive') . '/js/slide.js');
}
