<?php

namespace Tests\Unit;

use App\Http\Controllers\QuestionController;
use App\Http\Controllers\TrialController;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class TrialControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_generate_handles_arrays_from_question_queries(): void
    {
        $questionController = Mockery::mock(QuestionController::class);
        $questionController->shouldReceive('obtainExamByAdmGrp')
            ->once()
            ->with('A', 'B')
            ->andReturn([
                ['id' => 11],
                ['id' => 12],
            ]);

        $trialQuestionsRelation = Mockery::mock();
        $trialQuestionsRelation->shouldReceive('createMany')->once()->withArgs(function ($items) {
            $this->assertCount(2, $items);
            $this->assertSame(99, $items[0]['trial_id']);
            $this->assertSame(11, $items[0]['question_id']);
            return true;
        });

        $trial = Mockery::mock('alias:App\\Models\\Trial');
        $trial->shouldReceive('create')->once()->withArgs(function ($attributes) {
            $this->assertSame('Examen genérico', $attributes['name']);
            $this->assertSame('A', $attributes['administration']);
            $this->assertSame('B', $attributes['grup']);
            $this->assertSame(2, $attributes['num_questions']);
            $this->assertSame(7, $attributes['user_id']);
            return true;
        })->andReturnUsing(function () use ($trialQuestionsRelation) {
            $createdTrial = new class {
                public $id = 99;
                public $trialQuestions;

                public function trialQuestions()
                {
                    return $this->trialQuestions;
                }
            };

            $createdTrial->trialQuestions = $trialQuestionsRelation;

            return $createdTrial;
        });

        $trial->shouldReceive('with')->once()->with('trialQuestions')->andReturnSelf();
        $trial->shouldReceive('find')->once()->with(99)->andReturnUsing(function () use ($trialQuestionsRelation) {
            $result = new class {
                public $id = 99;
                public $trialQuestions;
            };
            $result->trialQuestions = $trialQuestionsRelation;
            return $result;
        });

        $controller = new TrialController($questionController);

        $request = Request::create('/trials/generate', 'POST', [
            'adm' => 'A',
            'grp' => 'B',
            'typ' => 'exam',
        ]);
        $request->setUserResolver(function () {
            return (object) ['id' => 7];
        });

        $response = $controller->generate($request);

        $this->assertSame(200, $response->getStatusCode());
    }
}
