<?php

/**
 * @file
 * Contains \Drupal\Tests\multiversion\Unit\SessionWorkspaceNegotiatorTest.
 */

namespace Drupal\Tests\multiversion\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\multiversion\Workspace\SessionWorkspaceNegotiator;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\multiversion\Workspace\SessionWorkspaceNegotiator
 * @group multiversion
 */
class SessionWorkspaceNegotiatorTest extends UnitTestCase {

  /**
   * The workspace negotiator.
   *
   * @var \Drupal\multiversion\Workspace\SessionWorkspaceNegotiator
   */
  protected $workspaceNegotiator;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $requestStack;

  /**
   * @var \Drupal\multiversion\Workspace\WorkspaceManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $workspaceManager;

  /**
   * The cache render.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $cacheRender;

  /**
   * The path used for testing.
   *
   * @var string
   */
  protected $path;

  /**
   * The entity type used for testing.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityType;

  /**
   * @var \Drupal\multiversion\Workspace\SessionWorkspaceNegotiator|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $negotiator;

  /**
   * The entities values.
   *
   * @var array
   */
  protected $values;

  /**
   * The machine name of the default entity.
   *
   * @var string
   */
  protected $defaultMachineName = 'default';

  /**
   * The entity type used for testing.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityTypeId;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->entityTypeId = 'workspace';
    $second_id = $this->randomMachineName();
    $this->values = [
      ['id' => 1, 'machine_name' => $this->defaultMachineName, 'label' => $this->defaultMachineName],
      ['id' => 2, 'machine_name'=> $second_id, 'label' => $second_id]
    ];

    foreach ($this->values as $value) {
      $entity = $this->getMockBuilder('Drupal\multiversion\Entity\Workspace')
        ->disableOriginalConstructor()
        ->getMock();
      $entity->expects($this->any())
        ->method('create')
        ->with($value)
        ->will($this->returnValue($this->entityType));
      $this->entities[] = $entity;
    }

    $this->path = '<front>';
    $this->request = Request::create($this->path);

    $this->entityType = $this->getMock('Drupal\multiversion\Entity\WorkspaceInterface');
    $this->entityManager = $this->getMock('Drupal\Core\Entity\EntityManagerInterface');
    $this->cacheRender = $this->getMock('Drupal\Core\Cache\CacheBackendInterface');
    $this->entityManager->expects($this->any())
      ->method('getDefinition')
      ->with($this->entityTypeId)
      ->will($this->returnValue($this->entityType));
    $this->requestStack = $this->getMock('Symfony\Component\HttpFoundation\RequestStack');
    $this->workspaceManager = $this->getMock('Drupal\multiversion\Workspace\WorkspaceManagerInterface');

    $container = new ContainerBuilder();
    $container->setParameter('workspace.default', 1);
    $container->set('entity.manager', $this->entityManager);
    $container->set('workspace.manager', $this->workspaceManager);
    $container->set('request_stack', $this->requestStack);
    $container->set('cache.render', $this->cacheRender);
    \Drupal::setContainer($container);

    $this->workspaceNegotiator = new SessionWorkspaceNegotiator();
    $this->workspaceNegotiator->setContainer($container);
  }

  /**
   * Tests the applies() method.
   */
  public function testApplies() {
    $this->assertTrue($this->workspaceNegotiator->applies($this->request));
  }

  /**
   * Tests the getWorkspaceId() method.
   */
  public function testGetWorkspaceId() {
    $this->assertSame(1, $this->workspaceNegotiator->getWorkspaceId($this->request));
  }

  /**
   * Tests the persist() method.
   */
  public function testPersist() {
    $this->entities[0]->expects($this->once())
      ->method('id')
      ->will($this->returnValue(1));
    $this->assertTrue($this->workspaceNegotiator->persist($this->entities[0]));
    $this->assertSame(1, $_SESSION['workspace']);
  }

}
