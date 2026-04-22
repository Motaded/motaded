<?php


use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Implements hook_form_system_theme_settings_alter() for settings form.
 *
 * Replace Barrio setting options with subtheme ones.
 *
 * Example on how to alter theme settings form
 */

function event_pro_form_system_theme_settings_alter(&$form, FormStateInterface $form_state) {
  $form['components']['navbar']['bootstrap_barrio_navbar_top_background']['#options'] = [
    'bg-primary' => t('Primary'),
    'bg-secondary' => t('Secondary'),
    'bg-light' => t('Light'),
    'bg-dark' => t('Dark'),
    'bg-white' => t('White'),
    'bg-transparent' => t('Transparent'),
  ];
  $form['components']['navbar']['bootstrap_barrio_navbar_background']['#options'] = [
    'bg-primary' => t('Primary'),
    'bg-secondary' => t('Secondary'),
    'bg-light' => t('Light'),
    'bg-dark' => t('Dark'),
    'bg-white' => t('White'),
    'bg-transparent' => t('Transparent'),
  ];
  /*Core theme  settings*/
  $form['pannel_color'] = array(
    '#type' => 'details',
    '#title' => t('Color Settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#group' => 'visibility',
    '#open' => FALSE,
    '#weight' => -992,
  );

  $form['color_options'] = array(
    '#type' => 'value',
    '#value' => array('APPLICATION' => t('Application'),
                      'DEVELOPMENT' => t('Development'),
                      'ENHANCEMENT' => t('Enhancement'))
    );
     $form['pannel_color']['default_color'] = [
      '#type' => 'checkbox',
      '#title' => t('Use the Default Color'),
      '#default_value' => theme_get_setting('default_color'),
      '#tree' => FALSE,
    ];
    $form['pannel_color']['color_settings'] = [
      '#type' => 'container',
      '#states' => [
        // Hide the logo settings when using the default logo.
        'invisible' => [
          'input[name="default_color"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['pannel_color']['color_settings']['primary_color'] = [
      '#type' => 'color',
      '#title' => t('Select Primary Color'),
      '#default_value' => theme_get_setting('primary_color'),
    ];
    $form['pannel_color']['color_settings']['secondary_color'] = [
      '#type' => 'color',
      '#title' => t('Select Secondary Color'),
      '#default_value' => theme_get_setting('secondary_color'),
    ];
  /*Core theme  settings*/
  $form['logo']['#group'] = 'visibility';
  $form['logo']['#title'] = t('Logo Image');
  $form['logo']['#weight'] = -995;
  $form['favicon']['#group'] = 'visibility';
  $form['favicon']['#weight'] = -994;

  $form['logo']['#open'] = TRUE;
  $form['favicon']['#open'] = TRUE;
  unset($form['theme_settings']); 
  unset($form['bootstrap_barrio_source']); 

  $form['visibility'] = [
    '#type' => 'vertical_tabs',
    '#prefix' => '<h2><small>' . t('Theme settings') . '</small></h2>',
    '#weight' => -999,
  ];

  

// Header Style
$form['header'] = [
  '#type' => 'details',
  '#title' => t('Header Options'),
  '#weight' => -998,
  '#group' => 'visibility',
  '#open' => FALSE,
];
$form['header']['header_variation'] = [
  '#title' => 'Header', 
  '#type' => 'select',
  '#options' => array(
    'header-1' => 'Header Style 1',
    'header-2' => 'Header Style 2',
    'header-3' => 'Header Style 3',
  ),
  '#default_value' => theme_get_setting('header_variation'),
  '#description' => t('Please choose your prefered header style'),
];
$form['header']['sticky'] = array(
  '#type'          => 'checkbox',
  '#title'         => t('Sticky Header'),
  '#default_value' => theme_get_setting('sticky'),
); 
$form['header']['loader'] = array(
  '#type'          => 'checkbox',
  '#title'         => t('Page Loader'),
  '#default_value' => theme_get_setting('loader'),
);
$form['header']['scroll_top'] = array(
  '#type'          => 'checkbox',
  '#title'         => t('Scroll to Top'),
  '#default_value' => theme_get_setting('scroll_top'),
); 


  //general settings 
  $form['general'] = [
    '#type' => 'details',
    '#title' => t('General Options'),
    '#weight' => -999,
    '#group' => 'visibility',
    '#open' => FALSE,
  ];
  //General Site Details
  $form['general']['header_and_footer'] = array(
    '#type' => 'details',
    '#title' => t('General Site Details'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['general']['header_and_footer']['contact_info_title'] = [
    '#type'          => 'textfield',
    '#title'         => t('Contact Informations Title'),
    '#default_value' => theme_get_setting('contact_info_title'),
    '#description'   => t("Eg: This is the Title for Contact Informations in Footer and Hamburger "),
  ];
  $form['general']['header_and_footer']['address'] = [
    '#type'          => 'textfield',
    '#title'         => t('Address'),
    '#default_value' => theme_get_setting('address'),
    '#description'   => t("Eg: This Will be Displayed in Header, Footer and Hamburger Menu"),
  ];
  $form['general']['header_and_footer']['contact_number'] = [
    '#type'          => 'textfield',
    '#title'         => t('Contact Number'),
    '#default_value' => theme_get_setting('contact_number'),
    '#description'   => t("Eg: This Will be Displayed in Header, Footer and Hamburger Menu"),
  ];
  $form['general']['header_and_footer']['mail_id'] = [
    '#type'          => 'textfield',
    '#title'         => t('Mail id'),
    '#default_value' => theme_get_setting('mail_id'),
    '#description'   => t("Eg: This Will be Displayed in Header, Footer and Hamburger Menu"),
  ];
  $form['general']['header_and_footer']['banner_bg'] = [
    '#title' => t('Page Banner Background Image'),
    '#description' => t('Use: png|jpg|jpeg'),
    '#type' => 'managed_file',
    '#upload_location' => 'public://',
    '#upload_validators' => [
    'file_validate_extensions' => ['png jpg jpeg'],
    ],
    '#default_value' => theme_get_setting('banner_bg'),
    '#description'   => t("This image be shown as Bakground image in all Basic Page not applicable for Team(user)"),
  ];
  //CopyRight
  $form['general']['header_and_footer']['copyright_detail'] = [
    '#type'          => 'text_format',
    '#title'         => t('Copyright'),
    '#default_value' => theme_get_setting('copyright_detail')['value'],
    '#description'   => t("Copyright text that appears in the footer"),
  ]; 
  //Button
  $form['general']['header_and_footer']['menu_button'] = array(
    '#type' => 'details',
    '#title' => t('Menu Button Link'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#description'   => t("For Header & Footer"),
  );
  $form['general']['header_and_footer']['menu_button']['button_title'] = [
    '#type'          => 'textfield',
    '#title'         => t('Button Title'),
    '#default_value' => theme_get_setting('button_title'),
    '#description'   => t("Title Above the Button in Footer"),
  ];
  $form['general']['header_and_footer']['menu_button']['button_text'] = [
    '#type'          => 'textfield',
    '#title'         => t('Button Text'),
    '#default_value' => theme_get_setting('button_text'),
  ];
  $form['general']['header_and_footer']['menu_button']['button_link'] = [
    '#type'          => 'textfield',
    '#title'         => t('Button Link'),
    '#default_value' => theme_get_setting('button_link'),
  ];
  //hamburger
$form['general']['hamburger'] = [
  '#type'         => 'details',
  '#title'        => t('hamburger Details'),
  '#open'         => FALSE,
  ];
  $form['general']['hamburger']['about_title'] = [
    '#type'          => 'textfield',
    '#title'         => t('About Title'),
    '#default_value' => theme_get_setting('about_title'),
  ];
  $form['general']['hamburger']['about_site'] = [
    '#type'          => 'textarea',
    '#title'         => t('About You'),
    '#default_value' => theme_get_setting('about_site'),
    '#description'   => t("Short Description about the Site."),
  ];

  //Search
  $form['general']['search_result'] = array(
    '#type' => 'details',
    '#title' => t('Search Result Page'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );  
    $form['general']['search_result']['search_banner_title'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Banner Title'),
    '#default_value' => theme_get_setting('search_banner_title'),
    '#description'   => t("Please enter title for search result page banner."),
  ); 

  // Login page 
  $form['general']['login'] = array(
    '#type' => 'details',
    '#title' => t('Login Page Settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['general']['login']['login_banner_title'] = array(  
    '#type'          => 'textfield',
    '#title'         => t('Banner Title'),
    '#default_value' => theme_get_setting('login_banner_title'),
    '#description'   => t("Please enter the banner title of Login Page."),
  );
  $form['general']['login']['login_title'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Form Title'),
    '#default_value' => theme_get_setting('login_title'),
    '#description'   => t("Please enter the title for Login Form."),
  );
  $form['general']['login']['login_name_description'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Name field description'),
    '#default_value' => theme_get_setting('login_name_description'),
    '#description'   => t("Please enter the description for name field in Login Form."),
  );
  $form['general']['login']['login_password_description'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Password field description'),
    '#default_value' => theme_get_setting('login_password_description'),
    '#description'   => t("Please enter the description for password field in Login Form."),
  );
   // Register page 
   $form['general']['register'] = array(
    '#type' => 'details',
    '#title' => t('Register Page Settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    );
    $form['general']['register']['register_banner_title'] = array(
      '#type'          => 'textfield',
      '#title'         => t('Banner Title'),
      '#default_value' => theme_get_setting('register_banner_title'),
      '#description'   => t("Please enter the title of Register page banner."),
    );
    $form['general']['register']['register_title'] = array(
      '#type'          => 'textfield',
      '#title'         => t('Form Title'),
      '#default_value' => theme_get_setting('register_title'),
      '#description'   => t("Please enter the title of the Register form."),
    );
    $form['general']['register']['register_mail_description'] = array(
      '#type'          => 'textfield',
      '#title'         => t('Email field description'),
      '#default_value' => theme_get_setting('register_mail_description'),
      '#description'   => t("Please enter the description for mail field in Register form."),
    );
    $form['general']['register']['register_name_description'] = array(
      '#type'          => 'textfield',
      '#title'         => t('User Name field description'),
      '#default_value' => theme_get_setting('register_name_description'),
      '#description'   => t("Please enter the description for name field in Register form."),
    );

  
    // Reset password page 
    $form['general']['reset_pass'] = array(
      '#type' => 'details',
      '#title' => t('Reset Password Page Settings'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );
    $form['general']['reset_pass']['reset_banner_title'] = array(
      '#type'          => 'textfield',
      '#title'         => t('Banner Title'),
      '#default_value' => theme_get_setting('reset_banner_title'),
      '#description'   => t("Please enter the Reset Password Page banner title."),
    );
    $form['general']['reset_pass']['reset_page_title'] = array(
      '#type'          => 'textfield',
      '#title'         => t('Form Title'),
      '#default_value' => theme_get_setting('reset_page_title'),
      '#description'   => t("Please enter the title of the Reset Password form."),
    );
    $form['general']['reset_pass']['reset_pass_name_description'] = array(
      '#type'          => 'textfield',
      '#title'         => t('Mail field description'),
      '#default_value' => theme_get_setting('reset_pass_name_description'),
      '#description'   => t("Please enter the description for mail field in Reset Password Form."),
    );
    

  // Maintenance and coming soon Section Start
  $form['general']['maintenance_coming_soon']['maintenance_mode'] = array(
    '#type' => 'details',
    '#title' => t('Maintenance'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['general']['maintenance_coming_soon']['mode_type'] = array(
    '#type'        => 'select',
    '#title'       => t('Mode Type'),
    '#options'     => ['1' => t('Maintenance Mode'),'2' => t('Coming Soon')],
    '#default_value' => theme_get_setting('mode_type'),
    '#description'   => t("Please select any one mode to change the content of Maintenance page. If Coming soon mode selected, while site under Maintenance, Coming Soon page content will be displayed"),
  );
  // Maintenace mode
  $form['general']['maintenance_coming_soon']['maintenance_mode']['maintenance_mode_title'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Title'),
    '#default_value' => theme_get_setting('maintenance_mode_title'),
    '#description'   => t("Please enter the title of Maintenance Page."),
  );
  $form['general']['maintenance_coming_soon']['maintenance_mode']['maintenance_mode_description'] = array(
    '#type'          => 'textarea',
    '#title'         => t('Description'),
    '#default_value' => theme_get_setting('maintenance_mode_description'),
    '#description'   => t("Please enter the description of Maintenance Page."),
  );
  $form['general']['maintenance_coming_soon']['maintenance_mode']['bg_image_m'] = [
    '#type' => 'managed_file',
    '#title'    => t('Background Image'),
    '#default_value' => theme_get_setting('bg_image_m'),
    '#upload_location' => 'public://',
    '#description' => t('Choose background image for maintenance page.'),
  ];
  // Comming soon
  $form['general']['maintenance_coming_soon']['coming_soon'] = array(
    '#type' => 'details',
    '#title' => t('Coming Soon'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['general']['maintenance_coming_soon']['coming_soon']['coming_soon_title'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Title'),
    '#default_value' => theme_get_setting('coming_soon_title'),
    '#description'   => t("Please enter the title of Coming soon Page."),
  );
  $form['general']['maintenance_coming_soon']['coming_soon']['coming_soon_description'] = array(
    '#type'          => 'textarea',
    '#title'         => t('Description'),
    '#default_value' => theme_get_setting('coming_soon_description'),
    '#description'   => t("Please enter the description of Coming soon Page."),
  );
  $form['general']['maintenance_coming_soon']['coming_soon']['launch_date'] = [
    '#type' => 'date',
    '#title' => t('Set Date'),
    '#description' => t('Please enter the date of site coming to alive, This date will be displayed in Coming soon page.'),
    '#default_value' => theme_get_setting('launch_date'),
  ];
  $form['general']['maintenance_coming_soon']['coming_soon']['bg_image_c'] = [
    '#type' => 'managed_file',
    '#title'    => t('Background Image'),
    '#default_value' => theme_get_setting('bg_image_c'),
    '#upload_location' => 'public://',
    '#description' => t('Choose background image for Coming Soon page.'),
  ];
  // custom css Section Start
$form['custom_css'] = array(
  '#type' => 'details',
  '#title' => t('Custom CSS'),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
  '#group' => 'visibility',
  '#open' => FALSE,
  '#weight' => -993,
);
$form['custom_css']['styles'] = array(
  '#type'          => 'textarea',
  '#title'         => t('Custom Style'),
  '#default_value' => theme_get_setting('styles'),
  '#description'   => t("Place your custom style for your site."),
);

  $form['#submit'][] = 'event_pro_form_submit';
}

function event_pro_form_submit(&$form, $form_state) {
   if ($file_id = $form_state->getValue(['banner_bg', '0'])) {
    $file = \Drupal::entityTypeManager()->getStorage('file')->load($file_id);
    $file->setPermanent();
    $file->save();
  }

  if ($file_id = $form_state->getValue(['bg_image_c', '0'])) {
    $file = \Drupal::entityTypeManager()->getStorage('file')->load($file_id);
    $file->setPermanent();
    $file->save();
  }

  if ($file_id = $form_state->getValue(['bg_image_m', '0'])) {
    $file = \Drupal::entityTypeManager()->getStorage('file')->load($file_id);
    $file->setPermanent();
    $file->save();
  }

}