<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class StatsController extends Controller
{
    public function themesStats(Request $request)
    {
        try {
            $themesData = $request->validate([
                'adm' => 'required|string',
                'grp' => 'required|string'
            ]);

            $user = $request->user();

            $conditions = [
                ['administration', '=', $themesData['adm']],
                ['grup', '=', $themesData['grp']]
            ];

            $qryQuestionsPerTheme = DB::table('question')
                ->select('administration', 'grup', 'theme', DB::raw('COUNT(*) num_questions'))
                ->where('administration', '=', $themesData['adm'])
                ->where('grup', '=', $themesData['grp'])
                ->groupByRaw('administration, grup, theme');

            $qryQuestionsStats = $this->queryQuestionsStats($user->id, $themesData['adm'], $themesData['grp']);
            
            /*return response()->json($qryQuestionsStats->get(), 200, [], JSON_UNESCAPED_UNICODE);*/

            $qryThemesStats = DB::table('question')
                ->joinSub($qryQuestionsPerTheme, 'qxt', function (JoinClause $join) {
                    $join->on('question.administration', '=', 'qxt.administration')
                        ->on('question.theme', '=', 'qxt.theme')
                        ->on('question.grup', '=', 'qxt.grup');
                })
                ->leftJoinSub($qryQuestionsStats, 'qStats', function (JoinClause $join) {
                    $join->on('qStats.question_id', '=', 'question.id');
                })
                ->where('question.administration', $themesData['adm'])
                ->where('question.grup', $themesData['grp'])
                ->select(
                    'question.administration',
                    'question.grup',
                    'question.theme',
                    'qxt.num_questions',
                    DB::raw('COALESCE(sum(qStats.right_questions),0) as num_right'),
                    DB::raw('COALESCE(sum(qStats.wrong_questions),0) as num_wrong'),
                    DB::raw('COALESCE(sum(case when qStats.balance > 0 then 1 else 0 end),0) as questions_done')
                )
                ->groupByRaw('question.administration,question.grup,question.theme,qxt.num_questions');

            return response()->json($qryThemesStats->get(), 200, [], JSON_UNESCAPED_UNICODE);

            /*$themes = DB::table('trialQuestions')
                ->join('trial', 'trialQuestions.trial_id', '=', 'trial.id')
                ->join('question', 'question.id', '=', 'trialQuestions.question_id')
                ->where('question.administration', $themesData['adm'])
                ->where('question.grup', $themesData['grp'])
                ->select('question.theme', 'trial.id')
                ->get();

            return response()->json($themes, 200, [], JSON_UNESCAPED_UNICODE);*/
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'ko',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Questions stats by theme
     * 
     */
    public function questionsStatsByTheme(Request $request)
    {
        try {
            $themeData = $request->validate([
                'adm' => 'required|string',
                'grp' => 'required|string',
                'thm' => 'required|integer'
            ]);

            $user = $request->user();

            $conditions = [
                ['administration', '=', $themeData['adm']],
                ['grup', '=', $themeData['grp']],
                ['theme', '=', $themeData['thm']]
            ];

            $themesQuestionsStats = $this->queryQuestionsStatsByTheme($user->id, $themeData['adm'], $themeData['grp'], $themeData['thm']);

            return response()->json($themesQuestionsStats->get(), 200, [], JSON_UNESCAPED_UNICODE);

            /*Question::with('trialQuestions')->where($conditions)->get();*/
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'ko',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Questions stats by theme
     *
     * ...
     * right_questions
     * wrong_questions
     * balance
     */
    private function queryQuestionsStatsByTheme(int $user_id, String $administration, String $grup, int $theme)
    {
        $queryTrialQuestionsStats = $this->queryTrialQuestionsStats($user_id, $administration, $grup);

        $qryThemesQuestionsStats = DB::table('question')
            ->leftJoinSub($queryTrialQuestionsStats, 'myTrialQuestions', function (JoinClause $join) {
                $join->on('myTrialQuestions.question_id', '=', 'question.id');
            })
            ->select(
                'question.administration',
                'question.grup',
                'question.theme',
                'question.id as question_id',
                'question.number',
                'question.question',
                DB::raw('COALESCE(sum(case when is_right = 1 then 1 when is_right = 0 then 0 end),0) as right_questions'),
                DB::raw('COALESCE(sum(case when is_right = 0 then 1 when is_right = 1 then 0 end),0) as wrong_questions'),
                DB::raw('COALESCE(sum(case when is_right = 1 then 1 when is_right = 0 then -1 end),0) as balance')
            )
            ->where('question.administration', '=', $administration)
            ->where('question.theme', '=', $theme)
            ->where('question.grup', '=', $grup)
            ->groupByRaw('question.administration, question.grup, question.theme, question.id, question.number, question.question');

        return $qryThemesQuestionsStats;
    }

    /**
     * Questions stats by user
     * 
     * 
     */
    private function queryQuestionsStats(int $user_id, String $administration, String $grup)
    {
        $qryQuestionsStats = DB::table('question')
            ->leftJoin('trial_questions', 'question.id', '=', 'trial_questions.question_id')
            ->leftJoin('trial', function (JoinClause $join) use ($user_id) {
                $join->on('trial.id', '=', 'trial_questions.trial_id')
                    ->where('trial.is_completed', '=', '1')
                    ->where('trial.user_id', '=', $user_id);
            })
            ->select(
                'question.administration',
                'question.grup',
                'question.theme',
                'question_id',
                'question.number',
                DB::raw('COALESCE(sum(case when is_right = 1 then 1 when is_right = 0 then 0 end),0) as right_questions'),
                DB::raw('COALESCE(sum(case when is_right = 0 then 1 when is_right = 1 then 0 end),0) as wrong_questions'),
                DB::raw('COALESCE(sum(case when is_right = 1 then 1 when is_right = 0 then -1 end),0) as balance')
            )
            ->where('question.administration', '=', $administration)
            ->where('question.grup', '=', $grup)
            ->where('trial.user_id', '=', $user_id)
            ->groupByRaw('question.administration, question.grup, question.theme, question_id, question.number');

        return $qryQuestionsStats;
    }

    /*
     * Stats trial questions by user, adm and grup
     */
    private function queryTrialQuestionsStats(int $user_id, String $administration, String $grup)
    {
        $qryTrialQuestionsStats = DB::table('trial_questions')
            ->leftJoin('trial', function (JoinClause $join) use ($user_id) {
                $join->on('trial.id', '=', 'trial_questions.trial_id')
                    ->where('trial.is_completed', '=', '1')
                    ->where('trial.user_id', '=', $user_id);
            })
            ->select('trial_questions.question_id', 'trial_questions.is_right')
            ->where('trial.administration', '=', $administration)
            ->where('trial.grup', '=', $grup)
            ->where('trial.user_id', '=', $user_id);

        return $qryTrialQuestionsStats;
    }
}
