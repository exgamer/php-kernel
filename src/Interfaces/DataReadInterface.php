<?php
namespace Citizenzet\Php\Core\Interfaces;

/**
 * Interface DataReadInterface
 * @package Citizenzet\Php\Core\Interfaces
 */
interface DataReadInterface
{
    public function oneById(int $id, $condition = null);
    public function oneByCondition($condition);
    public function allByIds(array $ids, $condition = null);
    public function allByCondition($condition);
}
