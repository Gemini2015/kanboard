<?php

use Kanboard\Model\ProjectModel;
use Kanboard\Model\UserModel;
use Kanboard\Model\TaskCreationModel;
use Kanboard\Model\TaskExecutorModel;

require_once __DIR__.'/../Base.php';

class TaskExecutorModelTest extends Base
{
    public function testAssociationAndDissociation()
    {
        $projectModel = new ProjectModel($this->container);
        $taskCreationModel = new TaskCreationModel($this->container);
        $taskExecutorModel = new TaskExecutorModel($this->container);
        $userModel = new UserModel($this->container);

        $this->assertEquals(1, $projectModel->create(array('name' => 'Project1')));
        $this->assertEquals(1, $taskCreationModel->create(array('project_id' => 1, 'title' => 'Task1')));

        // user #1 is admin
        $this->assertEquals(2, $userModel->create(array('username' => 'user2')));
        $this->assertEquals(3, $userModel->create(array('username' => 'user3')));
        $this->assertEquals(4, $userModel->create(array('username' => 'user4')));

        $users = $userModel->getAll();
        $this->assertCount(4, $users);

        $this->assertTrue($taskExecutorModel->save(1, 1, array(2, 3)));

        $users = $taskExecutorModel->getUsersByTask(1);
        $this->assertCount(2, $users);

        $this->assertEquals(2, $users[0]['id']);
        $this->assertEquals('user2', $users[0]['username']);

        $this->assertEquals(3, $users[1]['id']);
        $this->assertEquals('user3', $users[1]['username']);

        $this->assertTrue($taskExecutorModel->save(1, 1, array(3, 4)));

        $users = $taskExecutorModel->getUsersByTask(1);
        $this->assertCount(2, $users);

        $this->assertEquals(3, $users[0]['id']);
        $this->assertEquals('user3', $users[0]['username']);

        $this->assertEquals(4, $users[1]['id']);
        $this->assertEquals('user4', $users[1]['username']);
        
    }

    public function testGetUsersForTasks()
    {
        $projectModel = new ProjectModel($this->container);
        $taskCreationModel = new TaskCreationModel($this->container);
        $taskExecutorModel = new TaskExecutorModel($this->container);
        $userModel = new UserModel($this->container);

        $this->assertEquals(1, $projectModel->create(array('name' => 'Project1')));
        $this->assertEquals(1, $taskCreationModel->create(array('project_id' => 1, 'title' => 'Task1')));
        $this->assertEquals(2, $taskCreationModel->create(array('project_id' => 1, 'title' => 'Task2')));
        $this->assertEquals(3, $taskCreationModel->create(array('project_id' => 1, 'title' => 'Task3')));

        // user #1 is admin
        $this->assertEquals(2, $userModel->create(array('username' => 'user2', 'name' => 'name2')));
        $this->assertEquals(3, $userModel->create(array('username' => 'user3', 'name' => 'name3')));
        $this->assertEquals(4, $userModel->create(array('username' => 'user4', 'name' => 'name4')));

        

        $this->assertTrue($taskExecutorModel->save(1, 1, array(2, 3, 4)));
        $this->assertTrue($taskExecutorModel->save(1, 2, array(3)));

        $users = $taskExecutorModel->getUsersByTaskIds(array(1, 2, 3));

        $expected = array(
            1 => array(
                array(
                    'id' => 2,
                    'username' => 'user2',
                    'name' => 'name2',
                    'task_id' => 1
                ),
                array(
                    'id' => 3,
                    'username' => 'user3',
                    'name' => 'name3',
                    'task_id' => 1
                ),
                array(
                    'id' => 4,
                    'username' => 'user4',
                    'name' => 'name4',
                    'task_id' => 1
                ),
            ),
            2 => array(
                array(
                    'id' => 3,
                    'username' => 'user3',
                    'name' => 'name3',
                    'task_id' => 2
                )
            )
        );

        $this->assertEquals($expected, $users);
    }

    public function testGetUsersForTasksWithEmptyList()
    {
        $projectModel = new ProjectModel($this->container);
        $taskCreationModel = new TaskCreationModel($this->container);
        $taskExecutorModel = new TaskExecutorModel($this->container);
        $userModel = new UserModel($this->container);

        $this->assertEquals(1, $projectModel->create(array('name' => 'Project1')));
        $this->assertEquals(1, $taskCreationModel->create(array('project_id' => 1, 'title' => 'Task1')));

        // user #1 is admin
        $this->assertEquals(2, $userModel->create(array('username' => 'user2', 'name' => 'name2')));
        $this->assertEquals(3, $userModel->create(array('username' => 'user3', 'name' => 'name3')));
        $this->assertEquals(4, $userModel->create(array('username' => 'user4', 'name' => 'name4')));

        $this->assertTrue($taskExecutorModel->save(1, 1, array(2, 3, 4)));

        $users = $taskExecutorModel->getUsersByTaskIds(array());
        $this->assertEquals(array(), $users);
    }
}
