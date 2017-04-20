<?php

namespace Kanboard\Model;

use Kanboard\Core\Base;

/**
 * Class TaskExecutorModel
 *
 * @package Kanboard\Model
 * @author  Chris Cheng
 */
class TaskExecutorModel extends Base
{
    /**
     * SQL table name
     *
     * @var string
     */
    const TABLE = 'task_has_executors';

    /**
     * Get all users associated to a task
     *
     * @access public
     * @param  integer $task_id
     * @return array
     */
    public function getUsersByTask($task_id)
    {
        return $this->db->table(UserModel::TABLE)
            ->columns(UserModel::TABLE.'.id', UserModel::TABLE.'.username', UserModel::TABLE.'.name')
            ->eq(self::TABLE.'.task_id', $task_id)
            ->join(self::TABLE, 'user_id', 'id')
            ->findAll();
    }

    /**
     * Get all users associated to a list of tasks
     *
     * @access public
     * @param  integer[] $task_ids
     * @return array
     */
    public function getUsersByTaskIds($task_ids)
    {
        if (empty($task_ids)) {
            return array();
        }

        $users = $this->db->table(UserModel::TABLE)
            ->columns(UserModel::TABLE.'.id', UserModel::TABLE.'.username', UserModel::TABLE.'.name', self::TABLE.'.task_id')
            ->in(self::TABLE.'.task_id', $task_ids)
            ->join(self::TABLE, 'user_id', 'id')
            ->asc(UserModel::TABLE.'.username')
            ->findAll();

        return array_column_index($users, 'task_id');
    }

    /**
     * Get dictionary of users
     *
     * @access public
     * @param  integer $task_id
     * @return array
     */
    public function getList($task_id)
    {
        $users = $this->getUsersByTask($task_id);
        return array_column($users, 'username', 'id');
    }

    /**
     * Add or update a list of users to a task
     *
     * @access public
     * @param  integer  $project_id
     * @param  integer  $task_id
     * @param  integer[] $users
     * @return boolean
     */
    public function save($project_id, $task_id, array $users)
    {
        $task_users = $this->getList($task_id);
        $users = array_filter($users);

        return $this->associateUsers($project_id, $task_id, $task_users, $users) &&
            $this->dissociateUsers($task_id, $task_users, $users);
    }

    /**
     * Associate a user to a task
     *
     * @access public
     * @param  integer  $task_id
     * @param  integer  $user_id
     * @return boolean
     */
    public function associateUser($task_id, $user_id)
    {
        return $this->db->table(self::TABLE)->insert(array(
            'task_id' => $task_id,
            'user_id' => $user_id,
        ));
    }

    /**
     * Dissociate a user from a task
     *
     * @access public
     * @param  integer  $task_id
     * @param  integer  $user_id
     * @return boolean
     */
    public function dissociateUser($task_id, $user_id)
    {
        return $this->db->table(self::TABLE)
            ->eq('task_id', $task_id)
            ->eq('user_id', $user_id)
            ->remove();
    }

    /**
     * Associate missing users
     *
     * @access protected
     * @param  integer  $project_id
     * @param  integer  $task_id
     * @param  array    $task_users
     * @param  integer[] $users
     * @return bool
     */
    protected function associateUsers($project_id, $task_id, $task_users, $users)
    {
        foreach ($users as $user_id) {
            if (! isset($task_users[$user_id]) && ! $this->associateUser($task_id, $user_id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Dissociate removed users
     *
     * @access protected
     * @param  integer  $task_id
     * @param  array    $task_users
     * @param  integer[] $users
     * @return bool
     */
    protected function dissociateUsers($task_id, $task_users, $users)
    {
        foreach ($task_users as $user_id => $user_name) {
            if (! in_array($user_id, $users)) {
                if (! $this->dissociateUser($task_id, $user_id)) {
                    return false;
                }
            }
        }

        return true;
    }
}
