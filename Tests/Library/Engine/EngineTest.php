<?php

namespace Tests\Library\Engine;

use Exception;

class EngineTest extends EngineBaseTest
{
    /**
     * @expectedException Exception
     */
    public function test_run_whenActionIsNotValid()
    {
        // Arrange
        $this->setUpEngineWithUser();

        // Act
        $this->engine->run(['rubbish' => []]);
    }

    public function test_run_fetchSimplestWithoutData()
    {
        // Arrange
        $this->setUpEngineWithUser();

        // Act
        $result = $this->engine->run([
            'fetch' => [
                'User' => [
                    'fields' => [
                        'id' => ['as' => 'id']
                    ]
                ]
            ]
        ]);

        // Assert
        $this->assertEquals([], $result);
    }
}