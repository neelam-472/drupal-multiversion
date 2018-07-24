<?php

namespace Drupal\multiversion;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Menu\MenuTreeStorage as CoreMenuTreeStorage;

class MenuTreeStorage extends CoreMenuTreeStorage {

  /**
   * @var \Drupal\workspaces\WorkspaceManagerInterface
   */
  protected $workspaceManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $connection, CacheBackendInterface $menu_cache_backend, CacheTagsInvalidatorInterface $cache_tags_invalidator, $table, array $options = []) {
    $this->connection = $connection;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->table = $table;
    $this->options = $options;

    $this->entityTypeManager = \Drupal::service('entity_type.manager');
    $this->workspaceManager = \Drupal::service('workspaces.manager');

    $this->menuCacheBackend = $menu_cache_backend;
  }

  /**
   * {@inheritdoc}
   */
  protected function loadLinks($menu_name, MenuTreeParameters $parameters) {
    $links = parent::loadLinks($menu_name, $parameters);
    // Return links if the menu_link_content is not enabled.
    if (!\Drupal::moduleHandler()->moduleExists('menu_link_content')) {
      return $links;
    }
    $map = [];
    // Collect all menu_link_content IDs from the links.
    foreach ($links as $i => $link) {
      if ($link['provider'] != 'menu_link_content') {
        continue;
      }
      $metadata = unserialize($link['metadata']);
      $map[$metadata['entity_id']] = $i;
    }

    // Load all menu_link_content entities and remove links for the those that
    // don't belong to the active workspace.
    $entities = $this->entityTypeManager
      ->getStorage('menu_link_content')
      ->loadMultiple(array_keys($map));

    foreach ($map as $entity_id => $link_id) {
      if (!isset($entities[$entity_id])) {
        unset($links[$link_id]);
      }
    }
    return $links;
  }

  /**
   * {@inheritdoc}
   */
  public function purgeMultiple(array $ids) {
    parent::purgeMultiple($ids);
  }

}
