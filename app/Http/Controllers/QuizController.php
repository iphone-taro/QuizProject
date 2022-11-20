<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Play;
use App\Consts\Consts;

class QuizController extends Controller {

    //
    // SNSカード　クイズ
    //
    public function snsQuiz(Request $req) {

        $path = "/#/";
        $card = "card_base.png";

        if (isset($_GET["quizId"])) {
            //パラメータあり
            $quizId = $_GET["quizId"];

            //クイズID照合
            if (mb_strlen($quizId) == 20) {
                //長さOK
                //DB照合
                $quizData = DB::table('quizzes as quiz')->where('publishing', 1)->where('quiz.quiz_id', $quizId)->first();

                if ($quizData != null) {
                    //データあり
                    $path = "/#/play/" . $quizId;

                    //結果パラメータ
                    if (isset($_GET["result"])) {
                        //結果パラメータあり
                        $result = $_GET["result"];

                        //結果画像データがあるか
                        if (file_exists('../storage/app/public/card/card_' . $quizId . '_' . $result . '.jpg')) {
                            //結果画像ある
                            $card = 'card_' . $quizId . '_' . $result . '.jpg';
                        } else {
                            dd("A");
                        }
                    } else {
                        dd("AA");
                    }
                } else {
                    dd("AAA");
                }
            } else {
                dd("AAAA");
            }
        }
        dd($path . "    " . $card);
        return redirect($path, 307)->withInput(['card' => $card]);
    }

    public function baseAction(Request $req) {
        // dd($url);
        dd(URL::full());
    }

    public function retIsLogin () {
        if (Auth::check()) {
            return Auth::User()->user_id;
        } else {
            return "";
        }
    }

    public function test (Request $request) {

    }

    // public function updateIcon (Request $request): JsonResponse {
    //     $file = $request->file('iconFile');

    //     //入力チェック
    //     try {
    //         if ($file != null) {
    //             //アイコン添付あり
    //             $validatedData = $request->validate(['iconFile' => 'max:1024|mimes:jpg,jpeg,png']);
    //         }
    //     } catch (ValidationException $e) {
    //         return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => $e->getMessage()]);
    //     }

    //     //ユーザー情報を取得
    //     $userData = Auth::User();

    //     //アイコンファイル
    //     if ($request->iconFileName == "") {
    //         //何もしない
    //     } else if ($request->iconFileName == "TWITTER") {
    //         //ツイッターのアイコンを設定
    //         copy('../storage/app/public/icon/' . $userData->user_id . '_default.jpg', '../storage/app/public/icon/' . $userData->user_id . '.jpg');
    //     } else {
    //         //アップロード
    //         $file->storeAs('public/icon', $userData->user_id . ".jpg");
    //     }

    //     return response()->json(['status' => Consts::API_SUCCESS]);
    // }

    public function updateMyData (Request $request): JsonResponse {
        $file = $request->file('iconFile');

        //入力チェック
        try {
            if ($file != null) {
                //アイコン添付あり
                $validatedData = $request->validate(['iconFile' => 'max:1024|mimes:jpg,jpeg,png']);
            }
        } catch (ValidationException $e) {
            return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => $e->getMessage()]);
        }

        //入力チェック 公開方法別
        $publishing = $request->publishing;
        if ($publishing == 0) {
            //非公開
            try {
                $validatedData = $request->validate([
                    'quiz_id' => 'required|string',
                    'quiz_name' => 'string|max:30',
                    'quiz_sub_name' => 'string|max:20',
                    'description' => 'string|max:100',
                    'url1' => 'string|max:200',
                    'url2' => 'string|max:200',
                    'url3' => 'string|max:200',
                    'tag1' => 'string|max:20',
                    'tag2' => 'string|max:20',
                    'tag3' => 'string|max:20',
                    'tag4' => 'string|max:20',
                    'tag5' => 'string|max:20',
                    'questionData' => '|string',
                ]);
            } catch (ValidationException $e) {
                return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => $e->getMessage()]);
            }
        } else if ($publishing == 1 || $publishing == 2) {
            //公開　限定公開
            try {
                $validatedData = $request->validate([
                    'quiz_id' => 'required|string',
                    'quiz_name' => 'required|string|max:30',
                    'quiz_sub_name' => 'string|max:20',
                    'description' => 'required|string|max:100',
                    'url1' => 'string|max:200',
                    'url2' => 'string|max:200',
                    'url3' => 'string|max:200',
                    'tag1' => 'string|max:20',
                    'tag2' => 'string|max:20',
                    'tag3' => 'string|max:20',
                    'tag4' => 'string|max:20',
                    'tag5' => 'string|max:20',
                    'questionData' => 'required|string',
                ]);
            } catch (ValidationException $e) {
                return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => $e->getMessage()]);
            }
        } else {
            //エラー
            return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => '']);
        }
        $request->quiz_id = trim($request->quiz_id);
        $request->quiz_name = trim($request->quiz_name);
        $request->quiz_sub_name = trim($request->quiz_sub_name);
        $request->description = trim($request->description);
        $request->tag1 = trim($request->tag1);
        $request->tag2 = trim($request->tag2);
        $request->tag3 = trim($request->tag3);
        $request->tag4 = trim($request->tag4);
        $request->tag5 = trim($request->tag5);
        $request->url1 = str_replace(' ', '', $request->url1);
        $request->url2 = str_replace(' ', '', $request->url2);
        $request->url3 = str_replace(' ', '', $request->url3);

        //質問データ
        $questionList = json_decode($request->questionData);

        for ($i=0; $i < count($questionList); $i++) { 
            //入力チェック
            $qData = $questionList[$i];
        
            //トリム　空白がある場合は詰める
            $qData->quiz_body = trim($qData->quiz_body);
            $qData->choices1 = trim($qData->choices1);
            $qData->choices2 = trim($qData->choices2);
            $qData->choices3 = trim($qData->choices3);
            $qData->choices4 = trim($qData->choices4);

            
            $flg = true;
            while ($flg) {
                $flg = false;
                if ($qData->choices3 == "" && $qData->choices4 != "") {
                    $qData->choices3 = $qData->choices4;
                    $qData->choices4 = "";
                    $flg = true;
                }
                if ($qData->choices2 == "" && $qData->choices3 != "") {
                    $qData->choices2 = $qData->choices3;
                    $qData->choices3 = "";
                    $flg = true;
                }
            }
        
            //問題文
            if (mb_strlen($qData->quiz_body) > 200) {
                //エラー
                return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => 'questionErr']);
            }
            if (mb_strlen($qData->choices1) > 50) {
                //エラー
                return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => 'questionErr']);
            }
            if (mb_strlen($qData->choices2) > 50) {
                //エラー
                return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => 'questionErr']);
            }
            if (mb_strlen($qData->choices3) > 50) {
                //エラー
                return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => 'questionErr']);
            }
            if (mb_strlen($qData->choices4) > 50) {
                //エラー
                return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => 'questionErr']);
            }
            
            if ($publishing != 0) {
                //非公開以外の場合
                if ($qData->quiz_body == "") {
                    //エラー
                    return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => 'questionErr']);
                }

                //選択肢は2つ以上
                if ($qData->choices2 == "") {
                    //エラー
                    return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => 'questionErr']);
                }

                if ($qData->choices1 == "") {
                    //エラー
                    return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => 'questionErr']);
                }
            }
        }

        //URLのトリム
        $urlArray = [$request->url1, $request->url2, $request->url3];
        $newUrlArray = [];
        for ($i=0; $i < 3; $i++) { 
            if ($urlArray[$i] != "") {
                $flg = true;
                for ($j=0; $j < count($newUrlArray); $j++) { 
                    if ($urlArray[$i] == $newUrlArray[$j]) {
                        $flg = false;
                    }
                }
                if ($flg) {
                    $newUrlArray[] = $urlArray[$i];
                }
            }
        }
        //補填
        for ($i=0; $i < 3; $i++) { 
            $newUrlArray[] = "";
        }

        //ユーザー情報を取得
        $userData = Auth::User();

        //更新処理
        //クイズテーブル
        //既存データ取得
        $curData = quiz::where('quiz_id', $request->quiz_id)->where('user_id', $userData->user_id)->first();

        if ($curData == null) {
            //該当なし
            return response()->json(['status' => Consts::API_FAILED_NODATA, 'errMsg' => $request->quiz_id]);
        }

        //既存の問題数退避
        $curQuizCount = $curData->questions;

        $curData->quiz_name = $request->quiz_name;
        $curData->quiz_sub_name = $request->quiz_sub_name;
        $curData->description = $request->description;
        $curData->url1 = $newUrlArray[0];
        $curData->url2 = $newUrlArray[1];
        $curData->url3 = $newUrlArray[2];
        $curData->questions = count($questionList);
        $curData->publishing = $request->publishing;
        $curData->save();

        //タグのトリム
        $tagArray = [$request->tag1, $request->tag2, $request->tag3, $request->tag4, $request->tag5];
        $newTagArray = [];
        for ($i=0; $i < 5; $i++) { 
            if ($tagArray[$i] != "") {
                $flg = true;
                for ($j=0; $j < count($newTagArray); $j++) { 
                    if ($tagArray[$i] == $newTagArray[$j]) {
                        $flg = false;
                    }
                }
                if ($flg) {
                    $newTagArray[] = $tagArray[$i];
                }
            }
        }
        //補填
        for ($i=0; $i < 5; $i++) { 
            $newTagArray[] = "";
        }

        //タグテーブル
        DB::table('tags')->where('quiz_id', $request->quiz_id)->delete();
        DB::table('tags')->insert([
            ['quiz_id' => $request->quiz_id, 'tag_no' => 1, 'tag' => $newTagArray[0]],
            ['quiz_id' => $request->quiz_id, 'tag_no' => 2, 'tag' => $newTagArray[1]],
            ['quiz_id' => $request->quiz_id, 'tag_no' => 3, 'tag' => $newTagArray[2]],
            ['quiz_id' => $request->quiz_id, 'tag_no' => 4, 'tag' => $newTagArray[3]],
            ['quiz_id' => $request->quiz_id, 'tag_no' => 5, 'tag' => $newTagArray[4]]
        ]);

        //質問テーブル
        DB::table('questions')->where('quiz_id', $request->quiz_id)->delete();
        $qAllArray = array();
        for ($i=0; $i < count($questionList); $i++) { 
            $qData = $questionList[$i];
            $qArray = [
                'quiz_id' => $request->quiz_id,
                'quiz_no' => $i,
                'quiz_body' => $qData->quiz_body,
                'choices1' => $qData->choices1,
                'choices2' => $qData->choices2,
                'choices3' => $qData->choices3,
                'choices4' => $qData->choices4,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ];
            array_push($qAllArray, $qArray);
        }
        DB::table('questions')->insert($qAllArray);

        //アイコンファイル
        if ($request->iconFileName == "") {
            //何もしない
        } else if ($request->iconFileName == "TWITTER") {
            //ツイッターのアイコンを設定
            copy('../storage/app/public/icon/user_' . $userData->user_id . '.jpg', '../storage/app/public/icon/quiz_' . $request->quiz_id . '.jpg');
        } else {
            //アップロード
            $file->storeAs('public/icon', "quiz_" . $request->quiz_id . ".jpg");
        }

        if ($request->deleteScore == "1") {
            //みんなの結果を削除
            $flg = DB::table('plays')->where('quiz_id', $request->quiz_id)->delete();
        }
        
        return response()->json(['status' => Consts::API_SUCCESS]);
        
        // //問題数が変わった場合、それまでのカードを削除
        // if ($curQuizCount != count($questionList)) {
        //     $foo_files = glob("../storage/app/public/card/card_" . $request->quiz_id . "_*");
        //     foreach ($foo_files as $filePath) {
        //         unlink($filePath);
        //     }
        // }

        // //Twitterカード更新
        // // base64デコード
        // $base64data = $request->card;
        // if ($base64data != "") {
        //     $data = base64_decode($base64data);

        //     // finfo_bufferでMIMEタイプを取得
        //     $finfo = finfo_open(FILEINFO_MIME_TYPE);
        //     $mime_type = finfo_buffer($finfo, $data);
    
        //     //MIMEタイプから拡張子を選択してファイル名を作成
        //     $filename = '../storage/app/public/card/card_' . $request->quiz_id . '.jpg';
    
        //     // 画像ファイルの保存
        //     file_put_contents($filename, $data);
    
        //     return response()->json(['status' => Consts::API_SUCCESS]);
        // } else {
        //     return response()->json(['status' => Consts::API_SUCCESS]);
        // }
    }

    public function getScoreData (Request $request): JsonResponse {
        try {
            $validatedData = $request->validate([
                'quizId' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => $e->getMessage()]);
        }

        $scoreDataList = DB::table('plays')
        ->select('nick_name as name', 'score', 'q_count', 'updated_at as time')
        ->orderBy('score', 'desc')->get();

        $retScoreData = array();
        $retScoreData = $retScoreData + array('dataList' => $scoreDataList);
        $retScoreData = $retScoreData + array('currentTime' => date('Y-m-d H:i:s'));

        return response()->json(['status' => Consts::API_SUCCESS, 'scoreData' => $retScoreData, 'isLogin' => $this->retIsLogin()]);
        
    }

    public function retMyData () {
        if (Auth::check()) {
            $myDataList = DB::table('quizzes as quiz')
            ->select('quiz.quiz_id', 'quiz.user_id', 'quiz.quiz_name', 'quiz.quiz_sub_name', 'quiz.questions', 'quiz.description'
            , 'quiz.twitter_id', 'quiz.url1', 'quiz.url2', 'quiz.url3', 'quiz.publishing', 'quiz.challenge_count', 'quiz.created_at'
            , 'tag1.tag as tag1', 'tag2.tag as tag2', 'tag3.tag as tag3', 'tag4.tag as tag4', 'tag5.tag as tag5')
            ->leftJoin('tags as tag1', function ($join) {
                $join->on('quiz.quiz_id', '=', 'tag1.quiz_id')->where('tag1.tag_no', '1');
            })
            ->leftJoin('tags as tag2', function ($join) {
                $join->on('quiz.quiz_id', '=', 'tag2.quiz_id')->where('tag2.tag_no', '2');
            })
            ->leftJoin('tags as tag3', function ($join) {
                $join->on('quiz.quiz_id', '=', 'tag3.quiz_id')->where('tag3.tag_no', '3');
            })
            ->leftJoin('tags as tag4', function ($join) {
                $join->on('quiz.quiz_id', '=', 'tag4.quiz_id')->where('tag4.tag_no', '4');
            })
            ->leftJoin('tags as tag5', function ($join) {
                $join->on('quiz.quiz_id', '=', 'tag5.quiz_id')->where('tag5.tag_no', '5');
            })
            ->where('quiz.user_id', Auth::user()->user_id)->get();
            
            foreach ($myDataList as $data) {
                $data->isMyData = "1";
            }

            return $myDataList;
        } else {
            return null;
        }
    }

    //
    //編集情報取得
    //
    public function getEditData(Request $request): JsonResponse {
        try {
            $validatedData = $request->validate([
                'quizId' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => $e->getMessage()]);
        }
        
        $quizId = $request->quizId;

        //基本情報
        $myQuizData = DB::table('quizzes as quiz')
        ->select('quiz.quiz_id', 'quiz.user_id', 'quiz.quiz_name', 'quiz.quiz_sub_name', 'quiz.questions', 'quiz.description'
        , 'quiz.twitter_id', 'quiz.url1', 'quiz.url2', 'quiz.url3', 'quiz.publishing', 'quiz.challenge_count', 'quiz.created_at'
        , 'tag1.tag as tag1', 'tag2.tag as tag2', 'tag3.tag as tag3', 'tag4.tag as tag4', 'tag5.tag as tag5')
        ->leftJoin('tags as tag1', function ($join) {
            $join->on('quiz.quiz_id', '=', 'tag1.quiz_id')->where('tag1.tag_no', '1');
        })
        ->leftJoin('tags as tag2', function ($join) {
            $join->on('quiz.quiz_id', '=', 'tag2.quiz_id')->where('tag2.tag_no', '2');
        })
        ->leftJoin('tags as tag3', function ($join) {
            $join->on('quiz.quiz_id', '=', 'tag3.quiz_id')->where('tag3.tag_no', '3');
        })
        ->leftJoin('tags as tag4', function ($join) {
            $join->on('quiz.quiz_id', '=', 'tag4.quiz_id')->where('tag4.tag_no', '4');
        })
        ->leftJoin('tags as tag5', function ($join) {
            $join->on('quiz.quiz_id', '=', 'tag5.quiz_id')->where('tag5.tag_no', '5');
        })
        ->where('quiz.quiz_id', $quizId)->first();

        if ($myQuizData == null) {
            //未ログイン？            
        }

        //問題情報
        $questionList = DB::table('questions')->where('quiz_id', '=', $myQuizData->quiz_id)->orderBy('quiz_no', 'asc')->get();

        //テンプレート一覧
        $templateList = DB::table('templates')->where('active', '1')->orderBy('temp_no', 'asc')->get();

        return response()->json(['status' => Consts::API_SUCCESS, 'questionList' => $questionList, 'baseQuizData' => $myQuizData, 'tempList' => $templateList, 'isLogin' => $this->retIsLogin()]);
    }

    //
    //HOTタグ取得
    //
    public function getHotTagList(Request $request): JsonResponse {    
        //タグ一覧を取得
        $getTagList = DB::table('tags')->selectRaw('count(tag) as count, tag')
        ->leftJoin('quizzes as quiz', function ($join) {
            $join->on('tags.quiz_id', '=', 'quiz.quiz_id');
        })
        ->where('tag', '<>', '')
        ->where('quiz.publishing', '1')
        ->groupBy('tag')->orderBy('count', 'desc')->take(10)->get();

        return response()->json(['status' => Consts::API_SUCCESS, 'hotTags' => $getTagList]);
    }

    //
    //クイズデータ取得
    //
    public function getQuizData(Request $request): JsonResponse {
        try {
            $validatedData = $request->validate([
                'quiz_id' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => $e->getMessage()]);
        }

        $quizData = DB::table('quizzes as quiz')->where('publishing', 1)->where('quiz.quiz_id', $request->quiz_id)
        ->select('quiz.quiz_id', 'quiz.quiz_name', 'quiz.quiz_sub_name', 'quiz.user_id', 'quiz.questions', 'quiz.description'
        , 'quiz.twitter_id', 'quiz.url1', 'quiz.url2', 'quiz.url3', 'quiz.created_at'
        , 'tag1.tag as tag1', 'tag2.tag as tag2', 'tag3.tag as tag3', 'tag4.tag as tag4', 'tag5.tag as tag5')
        ->leftJoin('tags as tag1', function ($join) {
            $join->on('quiz.quiz_id', '=', 'tag1.quiz_id')->where('tag1.tag_no', '1');
        })
        ->leftJoin('tags as tag2', function ($join) {
            $join->on('quiz.quiz_id', '=', 'tag2.quiz_id')->where('tag2.tag_no', '2');
        })
        ->leftJoin('tags as tag3', function ($join) {
            $join->on('quiz.quiz_id', '=', 'tag3.quiz_id')->where('tag3.tag_no', '3');
        })
        ->leftJoin('tags as tag4', function ($join) {
            $join->on('quiz.quiz_id', '=', 'tag4.quiz_id')->where('tag4.tag_no', '4');
        })
        ->leftJoin('tags as tag5', function ($join) {
            $join->on('quiz.quiz_id', '=', 'tag5.quiz_id')->where('tag5.tag_no', '5');
        })->first();

        if ($quizData == null) {
            //該当なし
            return response()->json(['status' => Consts::API_FAILED_NODATA, 'errMsg' => $request->quiz_id]);
        }

        //質問リスト取得
        $questionList = DB::table('questions')->where('quiz_id', '=', $request->quiz_id)->orderBy('quiz_no', 'asc')->get();

        foreach ($questionList as &$question) {
            //選択肢を入れ替える
            $correctNo = 0;
            $cCount = 3;
            $choiceList = array($question->choices1, $question->choices2, $question->choices3, $question->choices4);
            if ($choiceList[2] == "") {
                $cCount = 1;
            } else if ($choiceList[3] == "") {
                $cCount = 2;
            }

            for ($i=0; $i < 20; $i++) { 
                $ran1 = mt_rand(0, $cCount);
                $ran2 = mt_rand(0, $cCount);

                $val1 = $choiceList[$ran1];
                $choiceList[$ran1] = $choiceList[$ran2];
                $choiceList[$ran2] = $val1;

                if ($ran1 == $correctNo) {
                    $correctNo = $ran2;
                } else if ($ran2 == $correctNo) {
                    $correctNo = $ran1;
                }
            }

            $question->choices1 = $choiceList[0];
            $question->choices2 = $choiceList[1];
            $question->choices3 = $choiceList[2];
            $question->choices4 = $choiceList[3];
            $question->correct_no = $correctNo;
        }

        return response()->json(['status' => Consts::API_SUCCESS, 'quizData' => $quizData, 'questionList' => $questionList]);
    }

    //
    //新規クイズ作成
    //
    public function createQuiz(Request $request): JsonResponse {
        //入力チェック
        try {
            $credentials = $request->validate([
                'displayName' => 'required',
                'twitterId' => 'required',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => $e->getMessage()]);
        }

        $displayName = $request->displayName;
        $twitterId = $request->twitterId;

        if (!Auth::check()) {
            //未ログイン
            return response()->json(['status' => Consts::API_FAILED_LOGIN]);
        }
        $userId = Auth::user()->user_id;

        //クイズテーブル
        $quizId = "";
        $randomStr = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

        while ($quizId == "") {
            for ($i = 0; $i < 20; $i++) {
                $ch = substr($randomStr, mt_rand(0, strlen($randomStr)) - 1, 1);
                $quizId = $quizId . $ch;
            }

            //重複チェック
            $checkData = DB::table('quizzes')->where('quiz_id', $quizId)->first();

            if ($checkData != null) {
                $quizId = "";
            }
        }
        $newQuiz = new quiz;
        $newQuiz->quiz_id = $quizId;
        $newQuiz->user_id = $userId;
        $newQuiz->quiz_name = $displayName;
        $newQuiz->quiz_sub_name = "";
        $newQuiz->questions = 0;
        $newQuiz->description = "";
        $newQuiz->twitter_id = $twitterId;
        $newQuiz->url1 = "";
        $newQuiz->url2 = "";
        $newQuiz->url3 = "";
        $newQuiz->publishing = 0;
        $newQuiz->challenge_count = 0;            
        $flg = $newQuiz->save();

        //twitterのアイコンを設定
        copy('../storage/app/public/icon/user_' . $userId . '.jpg', '../storage/app/public/icon/quiz_' . $quizId . '.jpg');

        if ($flg) {
            return response()->json(['status' => Consts::API_SUCCESS, 'quizId' => $quizId, 'isLogin' => $this->retIsLogin()]);
        }
    }

    //
    //投稿一覧取得（初期表示用）
    //
    public function getQuizList(Request $request): JsonResponse {
        $page = $request->page;
        $order = $request->order;
        $keyword = "";
        
        if ($request->keyword != null) {
            $keyword = trim(str_replace("　", " ", $request->keyword));
        }
        $query = DB::table('quizzes as quiz')->where('publishing', 1)
        ->select('quiz.quiz_id', 'quiz.user_id', 'quiz.quiz_name', 'quiz.quiz_sub_name', 'quiz.questions', 'quiz.description'
        , 'quiz.twitter_id', 'quiz.url1', 'quiz.url2', 'quiz.url3', 'quiz.publishing', 'quiz.challenge_count', 'quiz.created_at'
        , 'tag1.tag as tag1', 'tag2.tag as tag2', 'tag3.tag as tag3', 'tag4.tag as tag4', 'tag5.tag as tag5')
        ->leftJoin('tags as tag1', function ($join) {
            $join->on('quiz.quiz_id', '=', 'tag1.quiz_id')->where('tag1.tag_no', '1');
        })
        ->leftJoin('tags as tag2', function ($join) {
            $join->on('quiz.quiz_id', '=', 'tag2.quiz_id')->where('tag2.tag_no', '2');
        })
        ->leftJoin('tags as tag3', function ($join) {
            $join->on('quiz.quiz_id', '=', 'tag3.quiz_id')->where('tag3.tag_no', '3');
        })
        ->leftJoin('tags as tag4', function ($join) {
            $join->on('quiz.quiz_id', '=', 'tag4.quiz_id')->where('tag4.tag_no', '4');
        })
        ->leftJoin('tags as tag5', function ($join) {
            $join->on('quiz.quiz_id', '=', 'tag5.quiz_id')->where('tag5.tag_no', '5');
        });

        //キーワード設定
        if ($keyword != "") {
            $keywordArray = explode(" ", $keyword);
			for ($i = 0; $i < count($keywordArray); $i++) {
                if ($keywordArray[$i] != "") {
                    $value = $keywordArray[$i];
                    $query->where(function($query) use ($value) {
                        $query->where('quiz_name', 'like', "%" . $value . "%");
                        $query->where('quiz_sub_name', 'like', "%" . $value . "%");
                        $query->orWhere('description', 'like', "%" . $value . "%");
                        $query->orWhere('tag1.tag', 'like', "%" . $value . "%");    
                        $query->orWhere('tag2.tag', 'like', "%" . $value . "%");    
                        $query->orWhere('tag3.tag', 'like', "%" . $value . "%");    
                        $query->orWhere('tag4.tag', 'like', "%" . $value . "%");    
                        $query->orWhere('tag5.tag', 'like', "%" . $value . "%");    
                    });
                }
			}
        }

        //表示順設定
        if ($order == 0) {
            //新着順
            $query->orderBy('created_at', 'desc');
        } else if ($order == 1) {
            //人気順
            $query->orderBy('challenge_count', 'desc');
            $query->orderBy('created_at', 'desc');
        }
        
        //取得件数を指定
        $quizList = $query->skip($page * 20)->take(20)->get();

        //追加取得の場合
        if ($page != 0) {
            return response()->json(['status' => Consts::API_SUCCESS, 'quizList' => $quizList, 'isLogin' => $this->retIsLogin()]);
        }

        //タグ一覧を取得
        $getTagList = DB::table('tags')->selectRaw('count(tag) as count, tag')
        ->leftJoin('quizzes as quiz', function ($join) {
            $join->on('tags.quiz_id', '=', 'quiz.quiz_id');
        })
        ->where('tag', '<>', '')
        ->where('quiz.publishing', '1')
        ->groupBy('tag')->orderBy('count', 'desc')->take(10)->get();

        return response()->json(['status' => Consts::API_SUCCESS, 'quizList' => $quizList, 'myQuizList' => $this->retMyData(), 'isLogin' => $this->retIsLogin(), 'hotTags' => $getTagList]);
    }

    //
    //ログイン処理（Twitter）
    //
    public function login(Request $request): JsonResponse
    {
        //入力チェック
        try {
            $credentials = $request->validate([
                'twitterUID' => 'required',
                'kbn' => 'required',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => $e->getMessage()]);
        }

        //ユーザーテーブルから該当を取得
        $userData = user::where('twitter_uid', $request->twitterUID)->first();

        $iconUrl = str_replace("_normal", "", $request->twitterIconUrl);
        
        $twitterId = $request->twitterId;

        if ($userData == null) {
            //データがない場合、新規作成

            //ログインボタンの場合は戻す
            if ($request->kbn == "LOGIN") {
                return response()->json(['status' => Consts::API_FAILED_NODATA]);
            }

            //ユーザーID生成
            $randomStr = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            $userId = "";

            while ($userId == "") {
				for ($i = 0; $i < 20; $i++) {
					$ch = substr($randomStr, mt_rand(0, strlen($randomStr)) - 1, 1);
					$userId = $userId . $ch;
				}

				//重複チェック
                $checkData = DB::table('users')->where('user_id', $userId)->first();

				if ($checkData != null) {
					$userId = "";
				}
			}

            $twitterUID = $request->twitterUID;
            $displayName = $request->displayName;
            
            //ユーザーテーブル
            //データ挿入
            $newUser = new user;
            $newUser->user_id = $userId;
            $newUser->twitter_uid = $twitterUID;
            $flg = $newUser->save();

            $image_path = file_get_contents($iconUrl);

            if ($flg) {
                //デフォルト用
                $newFileName = '../storage/app/public/icon/user_' . $userId . ".jpg";
                file_put_contents($newFileName, $image_path);
            }

            //クイズテーブル
            $quizId = "";

            while ($quizId == "") {
				for ($i = 0; $i < 20; $i++) {
                    $ch = substr($randomStr, mt_rand(0, strlen($randomStr)) - 1, 1);
                    $quizId = $quizId . $ch;
				}

				//重複チェック
                $checkData = DB::table('quizzes')->where('quiz_id', $quizId)->first();

				if ($checkData != null) {
					$quizId = "";
				}
			}
            $newQuiz = new quiz;
            $newQuiz->quiz_id = $quizId;
            $newQuiz->user_id = $userId;
            $newQuiz->quiz_name = $displayName;
            $newQuiz->quiz_sub_name = "";
            $newQuiz->questions = 0;
            $newQuiz->description = "";
            $newQuiz->twitter_id = $twitterId;
            $newQuiz->url1 = "";
            $newQuiz->url2 = "";
            $newQuiz->url3 = "";
            $newQuiz->publishing = 0;
            $newQuiz->challenge_count = 0;            
            $flg = $newQuiz->save();
            
            DB::table('tags')->insert([
                ['quiz_id' => $quizId, 'tag_no' => 1, 'tag' => ''],
                ['quiz_id' => $quizId, 'tag_no' => 2, 'tag' => ''],
                ['quiz_id' => $quizId, 'tag_no' => 3, 'tag' => ''],
                ['quiz_id' => $quizId, 'tag_no' => 4, 'tag' => ''],
                ['quiz_id' => $quizId, 'tag_no' => 5, 'tag' => '']
            ]);

            $newQuiz->tag1 = "";
            $newQuiz->tag2 = "";
            $newQuiz->tag3 = "";
            $newQuiz->tag4 = "";
            $newQuiz->tag5 = "";
            
            $newQuiz->isMyData = "1";

            $myQuizList = array();
            array_push($myQuizList, $newQuiz);

            //クイズのアイコン
            $newFileName = '../storage/app/public/icon/quiz_' . $quizId . ".jpg";
            file_put_contents($newFileName, $image_path);

            Auth::login($newUser);

            return response()->json(['status' => Consts::API_SUCCESS, 'myQuizList' => $myQuizList, 'isLogin' => $this->retIsLogin()]);
        } else {
            //データがある場合、ログイン成功
            try {
                Auth::login($userData);                
            } catch (AuthenticationException $e) {
                return response()->json(['status' => Consts::API_FAILED_LOGIN]);
            }
            
            //デフォルトファイルの更新
            $newFileName = '../storage/app/public/icon/user_' . Auth::User()->user_id . ".jpg";
            $image_path = file_get_contents($iconUrl);
            file_put_contents($newFileName, $image_path);

            //twitterIdの更新
            DB::table('quizzes')->where('user_id', Auth::User()->user_id)->update(['twitter_id' => $twitterId]);

            return response()->json(['status' => Consts::API_SUCCESS, 'myQuizList' => $this->retMyData(), 'isLogin' => $this->retIsLogin()]);
        }
    }

    //
    //挑戦数更新
    //
    public function updateChallenge(Request $request): JsonResponse {
        // //入力チェック
        // try {
        //     $credentials = $request->validate([
        //         'quiz_id' => 'required',
        //     ]);
        // } catch (ValidationException $e) {
        //     return response()->json(['status' => Consts::API_FAILED_PARAM, 'msg' => $e->getMessage()]);
        // }
        //プレイ履歴を更新

        $playData = play::where('quiz_id', $request->quizId)
        ->where('player_id', $request->playerId)
        ->where('nick_name', $request->nickName)->first();

        //データがあるか
        if ($playData == null) {
            //新規作成
            $playData = new play;
            $playData->quiz_id = $request->quizId;
            $playData->player_id = $request->playerId;
            $playData->nick_name = $request->nickName;
            $playData->score = $request->score;
            $playData->q_count = $request->quizCount;
            $playData->save();
        } else {
            //データがある場合、高得点orクイズカウントが違う場合に更新
            if ($playData->score < $request->score || $playData->q_count != $request->quizCount) {
                $playData->score = $request->score;
                $playData->q_count = $request->quizCount;
                $playData->save();
            }
        }

        //挑戦回数を更新
        DB::table('quizzes')->where('quiz_id', $request->quizId)->increment('challenge_count');

        //Twitterカード更新
        // base64デコード
        $base64data = $request->card;
        $scoreData = $request->score . "_" . $request->quizCount;

        if ($base64data != "") {
            $data = base64_decode($base64data);

            // finfo_bufferでMIMEタイプを取得
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_buffer($finfo, $data);
            
            //MIMEタイプから拡張子を選択してファイル名を作成
            $filename = '../storage/app/public/card/card_' . $request->quizId . '_' . $scoreData . '.jpg';
    
            // 画像ファイルの保存
            file_put_contents($filename, $data);
    
            return response()->json(['status' => Consts::API_SUCCESS]);
        }

        return response()->json(['status' => Consts::API_SUCCESS]);
    }

    //
    //ログアウト
    //
    public function logout()
    {
        Auth::logout();
        return response()->json(['status' => Consts::API_SUCCESS]);
    }
}
