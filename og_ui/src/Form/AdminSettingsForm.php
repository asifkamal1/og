<?php

/**
 * @file
 * Contains \Drupal\og_ui\Form\AdminSettingsForm.
 */

namespace Drupal\og_ui\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the main administration settings form for Organic groups.
 */
class AdminSettingsForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs an AdminSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    parent::__construct($config_factory);

    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'og_ui_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'og.settings',
      'og_ui.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config_og = $this->config('og.settings');
    $config_og_ui = $this->config('og_ui.settings');

    $form['og_group_manager_full_access'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Group manager full permissions'),
      '#description' => $this->t('When enabled the group manager will have all the permissions in the group.'),
      '#default_value' => $config_og->get('group_manager_full_access'),
    ];

    $form['og_node_access_strict'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Strict node access permissions'),
      '#description' => $this->t('When enabled Organic groups will restrict permissions for creating, updating and deleting according to the Organic groups access settings. Example: A content editor with the <em>Edit any page content</em> permission who is not a member of a group would be denied access to modifying page content in that group. (For restricting view access use the Organic groups access control module.)'),
      '#default_value' => $config_og->get('node_access_strict'),
    );

    // @todo: Port og_ui_admin_people_view.

    $form['og_features_ignore_og_fields'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Prevent "Features" export piping'),
      '#description' => $this->t('When exporting a content type using the Features module, this will prevent the OG related fields from being exported.'),
      '#default_value' => $config_og->get('features_ignore_og_fields'),
      '#access' => $this->moduleHandler->moduleExists('features'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('og.settings')
      ->set('group_manager_full_access', $form_state->getValue('og_group_manager_full_access'))
      ->set('node_access_strict', $form_state->getValue('og_node_access_strict'))
      ->set('features_ignore_og_fields', $form_state->getValue('og_features_ignore_og_fields'))
      ->save();
  }

}
