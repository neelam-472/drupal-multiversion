<?php

/**
 * @file
 * Contains \Drupal\multiversion\Workspace\SessionWorkspaceNegotiator.
 */

namespace Drupal\multiversion\Workspace;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\multiversion\Entity\WorkspaceInterface;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\HttpFoundation\Request;

class SessionWorkspaceNegotiator extends WorkspaceNegotiatorBase {

  /**
   * @var \Drupal\user\PrivateTempStore
   */
  protected $tempstore;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructor.
   *
   * @param \Drupal\user\PrivateTempStoreFactory $tempstore_factory
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   */
  public function __construct(PrivateTempStoreFactory $tempstore_factory, AccountProxyInterface $current_user) {
    $this->tempstore = $tempstore_factory->get('workspace');
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(Request $request) {
    // This negotiator only applies if the current user is authenticated,
    // i.e. a session exists.
    return $this->currentUser->isAuthenticated();
  }

  /**
   * {@inheritdoc}
   */
  public function getWorkspaceId(Request $request) {
    $workspace_id = $this->tempstore->get('active_workspace_id');
    return $workspace_id ?: $this->container->getParameter('workspace.default');
  }

  /**
   * {@inheritdoc}
   */
  public function persist(WorkspaceInterface $workspace) {
    $this->tempstore->set('active_workspace_id', $workspace->id());
    return TRUE;
  }

}
