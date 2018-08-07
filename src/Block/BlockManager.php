<?php

namespace Drupal\multiversion\Block;

use Drupal\Core\Block\BlockManager as CoreBlockManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\workspaces\WorkspaceManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Adds the workspace ID to the cache key.
 *
 * @see \Drupal\Core\Block\BlockPluginInterface
 */
class BlockManager extends CoreBlockManager {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The workspace manager service.
   *
   * @var \Drupal\workspaces\WorkspaceManagerInterface
   */
  protected $workspaceManager;

  /**
   * The string to prefix the workspace ID for the cache key.
   * @var string
   */
  protected $workspaceCacheKeyPrefix = 'block_plugins:workspace';

  /**
   * Constructs a new \Drupal\multiversion\Block\BlockManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Psr\Log\LoggerInterface $logger
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\workspaces\WorkspaceManagerInterface $workspace_manager
   *   The workspace manager service.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, LoggerInterface $logger, Connection $database, WorkspaceManagerInterface $workspace_manager) {
    parent::__construct($namespaces, $cache_backend, $module_handler, $logger);

    $this->database = $database;
    $this->workspaceManager = $workspace_manager;
  }

  /**
   * {@inheritdoc}
   */
  protected function setCachedDefinitions($definitions) {
    $this->cacheKey = $this->workspaceCacheKeyPrefix . $this->workspaceManager->getActiveWorkspace()->id();
    parent::setCachedDefinitions($definitions);
  }

  /**
   * {@inheritdoc}
   */
  public function clearCachedDefinitions() {
    $active_workspace_id = $this->workspaceManager->getActiveWorkspace()->id();
    if (isset($active_workspace)) {
      $this->cacheKey = $this->workspaceCacheKeyPrefix . $active_workspace_id;
    }
    parent::clearCachedDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  protected function getCachedDefinitions() {
    $this->cacheKey = $this->workspaceCacheKeyPrefix . $this->workspaceManager->getActiveWorkspace()->id();
    parent::getCachedDefinitions();
  }

}