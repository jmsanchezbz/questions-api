<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Type\Integer;
use Throwable;

class QuestionController extends Controller
{
    /**
     * Generic find questions
     */
    public function find(Request $request)
    {
        try {
            $findData = $request->validate([
                'adm' => 'required|string',
                'grp' => 'required|string',
                'thm' => 'integer',
                'verified' => 'nullable|integer'
            ]);

            if ($request->adm && $request->grp) {

                $admValue = $findData['adm'];
                $grpValue = $findData['grp'];

                if ($findData['thm']) {
                    $thmValue = $request->thm;
                    $conditions = [['administration', '=', $admValue], ['grup', '=', $grpValue], ['theme', '=', $thmValue]];
                } else {
                    $conditions = [['administration', '=', $admValue], ['grup', '=', $grpValue]];
                }

                if (isset($findData['verified']) and !is_null($findData['verified'])) {
                    $verifiedValue = $findData['verified'];
                    array_push($conditions, ['verified', '=', $verifiedValue]);
                }

                $questions = Question::where($conditions)->get();
                return response()->json($questions, 200, [], JSON_UNESCAPED_UNICODE);
            }

            return response([
                'status' => 'error',
                'description' => 'Missing required parameters adm(' . request()->adm . ') grp(' . request()->grp . ')'
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'ko',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function findById($id)
    {
        try {
            $question = Question::find($id);

            if (!empty($question)) {
                return response()->json($question, 200, [], JSON_UNESCAPED_UNICODE);
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
     * Update question (answer,explanation,verified)
     */
    public function update(Request $request, $id)
    {
        try {
            if (Question::where('id', $id)->exists() && $request->id == $id) {
                $question = Question::find($id);

                if (UserController::isAdmin($request)) {
                    $question->administration = is_null($request->administration) ? $question->administration : $request->administration;
                    $question->grup = is_null($request->grup) ? $question->grup : $request->grup;
                    $question->theme = is_null($request->theme) ? $question->theme : $request->theme;
                    $question->number = is_null($request->number) ? $question->number : $request->number;
                    $question->question = is_null($request->question) ? $question->question : $request->question;

                    $question->option1 = is_null($request->option1) ? $question->option1 : $request->option1;
                    $question->option2 = is_null($request->option2) ? $question->option2 : $request->option2;
                    $question->option3 = is_null($request->option3) ? $question->option3 : $request->option3;
                    $question->option4 = is_null($request->option4) ? $question->option4 : $request->option4;

                    $question->answer = is_null($request->answer) ? $question->answer : $request->answer;
                    $question->explanation = is_null($request->explanation) ? $question->explanation : $request->explanation;
                    $question->verified = is_null($request->verified) ? $question->verified : $request->verified;

                    $question->updated_at = Carbon::now('Europe/Madrid');

                    $question->save();

                    //return response()->json($question, 200, [], JSON_UNESCAPED_UNICODE);

                    return response()->json(["message" => "Completed Question Updated."], 200);
                } else if (!$question->verified) {
                    $question->answer = is_null($request->answer) ? $question->answer : $request->answer;
                    $question->explanation = is_null($request->explanation) ? $question->explanation : $request->explanation;

                    $question->save();

                    return response()->json(["message" => "Anwser Question Updated."], 200);
                } else {
                    return response()->json(["message" => "Question already verified is not updatable."], 403);
                }
            } else {
                return response()->json(["message" => "Question Not Found or unmatched id."], 404);
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'ko',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtain exam by administration and theme
     */
    public function obtainExamByAdmGrpTheme(String $administration, String $grup, int $theme, int $numQuestions = 50)
    {
        $conditions = [
            ['administration', '=', $administration],
            ['grup', '=', $grup],
            ['theme', '=', $theme]
        ];

        $questions = Question::where($conditions)
            ->inRandomOrder()->limit($numQuestions)->get();

        return $questions->toArray();
    }

    /**
     * Obtain exam by administration and theme with mistakes
     */
    public function obtainExamByAdmThemeWithUserMistakes(int $user_id, String $administration, int $theme, int $numQuestions = 0)
    {
        $conditions = [
            ['administration', '=', $administration],
            ['theme', '=', $theme]
        ];

        $questions = Question::where($conditions)
            ->whereHas(['trialQuestions', function ($query) use ($user_id) {
                $query->where('is_right', false)
                    ->whereHas('trial', function ($query) use ($user_id) {
                        $query->where('user_id', $user_id);
                    });
            }])
            ->inRandomOrder()->limit($numQuestions)->get();

        return $questions;
    }

    /**
     * Obtain exam by administration grup
     */
    public function obtainExamByAdmGrp(String $administration, String $grup, int $numQuestions = 100)
    {
        $conditions = [
            ['administration', '=', $administration],
            ['grup', '=', $grup]
        ];

        $themesCounts = Question::groupBy('theme')
            ->select('theme', DB::raw('COUNT(*) as count'))->pluck('count', 'theme');
        $totalThemes = count($themesCounts);

        $questionsPerTheme = ceil($numQuestions / $totalThemes);

        $questions = [];

        $theme = 0;

        for ($theme = 1; $theme <= $totalThemes; $theme++) {
            $conditions = [
                ['administration', '=', $administration],
                ['grup', '=', $grup],
                ['theme', '=', $theme]
            ];

            $themeQuestions = Question::where($conditions)
                ->inRandomOrder()->take($questionsPerTheme)->get();

            $questions = array_merge($questions, $themeQuestions->toArray());
        }

        shuffle($questions);
        $questions = array_slice($questions, 0, $numQuestions);

        return $questions;
    }
}
