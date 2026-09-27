<?php

namespace App\Http\Controllers;

use App\Models\Trial;
use App\Models\TrialQuestions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class TrialController extends Controller
{
    protected $questionController;

    public function __construct(QuestionController $questionController)
    {
        $this->questionController = $questionController;
    }

    public function find(Request $request)
    {
        try {
            $user = $request->user();
            $trials = Trial::with('trialQuestions')
                ->where('user_id', $user->id)
                ->get();

            if (!empty($trials)) {
                return response()->json($trials, 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json([
                    "message" => "Trial not found"
                ], 404);
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'ko',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function findById(Request $request, $id)
    {
        try {
            $user = $request->user();
            $trial = Trial::with('trialQuestions.question')
                ->where('user_id', $user->id)
                ->where('id', $id)
                ->get();

            if (!empty($trial)) {
                return response()->json($trial, 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json([
                    "message" => "Trial not found"
                ], 404);
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'ko',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a Trial with its random questions
     */
    public function generate(Request $request)
    {
        try {
            $user = $request->user();
            $trialData = $request->validate([
                'adm' => 'required|string',
                'grp' => 'required|string|max:2',
                'typ' => 'required|string',
                'thm' => 'required_if:typ,theme|integer'
            ]);

            $now = Carbon::now('Europe/Madrid');
            $trialName = null;
            $questions = null;
            $numQuestions = null;

            if ($trialData['typ'] == 'exam') {
                $trialName = 'Examen genérico';
                $maxNumQuestions = 100;

                $questions = $this->questionController->obtainExamByAdmGrp($trialData['adm'], $trialData['grp']);
            } else {
                $trialName = 'Examen tema';
                $maxNumQuestions = 50;

                $questions = $this->questionController->obtainExamByAdmGrpTheme($trialData['adm'], $trialData['grp'], $trialData['thm'], $maxNumQuestions);
            }

            $numQuestions = $questions->count();
            DB::beginTransaction();

            $trial = Trial::create([
                'name' => $trialName,
                'administration' => $trialData['adm'],
                'grup' => strtoupper($trialData['grp']),
                'theme' => $trialData['thm'] ?? null,
                'num_questions' => $numQuestions,
                'user_id' => $user->id,
                'created_at' => $now,
                'updated_at' => $now
            ]);

            $idQuestions = array_column($questions, 'id');

            $trialQuestions = array_map(function ($id) use ($trial) {
                return [
                    'trial_id' => $trial->id,
                    'question_id' => $id,
                ];
            }, $idQuestions);

            $trial->trialQuestions()->createMany($trialQuestions);

            DB::commit();

            $result = Trial::with('trialQuestions')->find($trial->id);

            if (!empty($result)) {
                return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json([
                    "message" => "Question not found"
                ], 404);
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'ko',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a complete Trial with its questions
     *
     * num_mistakes, is_completed calculated in front
     */
    public function update(Request $request, $id)
    {
        try {
            $tquestionsData = $request->validate([
                'name' => 'required|string',
                'administration' => 'required|string',
                'grup' => 'required|string',
                'num_mistakes' => 'nullable|integer',
                'is_completed' => 'nullable|boolean',
                'trial_questions' => 'required|array',
                'trial_questions.*.id' => 'required|integer',
                'trial_questions.*.trial_id' => 'required|integer',
                'trial_questions.*.question_id' => 'required|integer',
                'trial_questions.*.answer' => 'nullable|integer',
                'trial_questions.*.is_right' => 'nullable|boolean'
            ]);

            $trial = Trial::find($id);
            $trial->num_mistakes = $tquestionsData['num_mistakes'];
            $trial->is_completed = $tquestionsData['is_completed'];

            $trial->save();

            DB::beginTransaction();

            foreach ($tquestionsData['trial_questions'] as $q) {
                $tQuestion = $trial->trialQuestions->find($q['id']);

                $tQuestion->update([
                    'answer' => $q['answer'],
                    'is_right' => $q['is_right']
                ]);
            }

            DB::commit();

            $trial = Trial::with('trialQuestions')->find($id);

            if ($trial && $trial->trialQuestions->isNotEmpty()) {
                return response()->json($trial, 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json([
                    "message" => "Trial not found"
                ], 404);
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'ko',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update trial question
     */
    public function updateTrialQuestion(Request $request, $id, $idq)
    {
        try {
            $user = $request->user();

            $tQuestionData = $request->validate([
                //'id' => 'required|integer',
                //'test_id' => 'required|integer',
                //'question_id' => 'required|integer',
                'answer' => 'required|integer',
                'is_right' => 'required|boolean'
            ]);

            $tQuestion = TrialQuestions::whereRelation('trial', 'user_id', $user->id)
                ->find($idq);

            if (!empty($tQuestion) and $tQuestion->trial_id == $id) {
                $tQuestion->answer = is_null($tQuestionData['answer']) ? $tQuestion->answer : $tQuestionData['answer'];
                $tQuestion->is_right = is_null($tQuestionData['is_right']) ? $tQuestion->is_right : $tQuestionData['is_right'];

                $tQuestion->save();

                return response()->json($tQuestion, 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json([
                    "message" => "Question not found " + $tQuestion->trial_id + " <> " + $id
                ], 404);
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'ko',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
