<?php
namespace Citizenzet\Php\Core\Interfaces;

/**
 * Interface DataModifyInterface
 * @package Citizenzet\Php\Core\Interfaces
 */
interface DataModifyInterface
{
    public function create(array $data);
    public function update(array $data, $condition);
    public function delete($condition);
    public function deleteById($id);
    public function updateById($id, $data);
}