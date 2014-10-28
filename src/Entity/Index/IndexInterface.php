<?php

namespace Drupal\multiversion\Entity\Index;

use Drupal\Core\Entity\EntityInterface;

interface IndexInterface {

  /**
   * @param $id
   * @return \Drupal\multiversion\Entity\Index\IndexInterface
   */
  public function useWorkspace($id);

  /**
   * @param string $key
   *
   * @return array
   */
  public function get($key);

  /**
   * @param array $keys
   *
   * @return array
   */
  public function getMultiple(array $keys);

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   */
  public function add(EntityInterface $entity);

  /**
   * @param array $entities
   */
  public function addMultiple(array $entities);

  /**
   * @param string $key
   */
  public function delete($key);

  /**
   * @param array $keys
   */
  public function deleteMultiple(array $keys);

  /**
   * @todo
   */
  public function deleteAll();

}