<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/api/login', [QuizController::class, 'login']);
Route::get('/api/logout', [QuizController::class, 'logout']);
Route::post('/api/getQuizList', [QuizController::class, 'getQuizList']);
Route::get('/api/getHotTagList', [QuizController::class, 'getHotTagList']);
Route::post('/api/getQuizData', [QuizController::class, 'getQuizData']);
Route::post('/api/updateChallenge', [QuizController::class, 'updateChallenge']);

Route::get('/quiz', [QuizController::class, 'snsQuiz']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/api/getEditData', [QuizController::class, 'getEditData']);
    Route::post('/api/getScoreData', [QuizController::class, 'getScoreData']);
    Route::post('/api/createQuiz', [QuizController::class, 'createQuiz']);
    Route::post('/api/updateMyData', [QuizController::class, 'updateMyData']);
    // Route::post('/api/updateIcon', [QuizController::class, 'updateIcon']);
    Route::post('/api/test', [QuizController::class, 'test']);
    
});

// ルートのURLにクイズIDと結果を付与
// サーバーでそれを受け取ったらURLを修正し、元の引数を渡してVueを表示が理想

Route::get('/{any}', function (Request $req) {
    $cardName = "card_base.png";
    $title = "あなたに関するクイズサイト『わたくぴ』";
    $description = "あなたに関するクイズを作ってみんなに挑戦してもらおう";

    if ($req->old('card') != null) {
        //特殊カード
        $cardName = $req->old('card');
        $title = $req->old('title');
        $description = $req->old('description');
    }
    // dd($req->old('test'));
    return view('spa.app')->with(['card' => $cardName, 'title' => $title, 'description' => $description]);
})->where('any', '.*');
// Route::get('/{any}', [QuizController::class, 'baseAction'])->where('any', '.*');

// Route::get('/{any}', function () {
//     dd(url["fragment"];);
// })->where('any', '.*');
