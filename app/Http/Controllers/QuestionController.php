<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;

class QuestionController extends Controller
{

    public function index()
    {
        if (request()->adm && request()->grp) {
            //$questions = Question::all();

            $admValue = request()->adm;
            $grpValue = request()->grp;

            if (request()->thm) {
                $thmValue = request()->thm;
                $conditions = [['administration', '=', $admValue], ['grup', '=', $grpValue], ['theme', '=', $thmValue]];
            } else {
                $conditions = [['administration', '=', $admValue], ['grup', '=', $grpValue]];
            }
            
            $questions = Question::where($conditions)->get();
            //dd($conditions);
            dd($questions);
            return response()->json($questions, 200, [], JSON_UNESCAPED_UNICODE);
        }

        return response([
            'status' => 'error',
            'description' => "Missing required parameters"
        ]);
    }

    /*public function store(Request $request)
    {
        $question = new Question;
        $question->name = $request->name;
        $question->author = $request->author;
        $question->publish_date = $request->publish_date;
        $question->save();
        return response()->json([
            "message" => "Question Added."
        ], 201);
    }*/

    public function show($id)
    {

        $question = Question::find($id);

        if (!empty($question)) {
            return response()->json($question, 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json([
                "message" => "Question not found"
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        if (Question::where('id', $id)->exists()) {
            $question = Question::find($id);

            if (!$question->verified) {
                $question->answer = is_null($request->answer) ? $question->answer : $request->answer;
                $question->explanation = is_null($request->explanation) ? $question->explanation : $request->explanation;
                $question->verified = is_null($request->verified) ? $question->verified : $request->verified;

                $question->save();

                return response()->json([
                    "message" => "Question Updated."
                ], 200);
            } else {
                return response()->json([
                    "message" => "Question already verified is not updatable."
                ], 401);
            }
        } else {
            return response()->json([
                "message" => "Question Not Found."
            ], 404);
        }
    }

    /*public function destroy($id) {
        if(Question::where('id', $id)->exists()) {
            $question = Question::find($id);
            $question->delete();

            return response()->json([
                "message" => "records deleted."
            ], 202);
        } else {
            return response()->json([
                "message" => "Question not found."
            ], 404);
        }
    }*/
}
