<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Auth\GoogleController;
use App\Http\Controllers\API\Common\BannerController;
use App\Http\Controllers\API\Common\CommentController;
use App\Http\Controllers\API\Common\RatingController;
use App\Http\Controllers\API\Common\SearchController;
use App\Http\Controllers\API\Common\TransactionController;
use App\Http\Controllers\API\Common\UserController;
use App\Http\Controllers\API\Common\WishListController;
use App\Http\Controllers\API\Instructor\ChapterController;
use App\Http\Controllers\API\Instructor\CourseController;
use App\Http\Controllers\API\Instructor\DocumentController;
use App\Http\Controllers\API\Instructor\LessonController;
use App\Http\Controllers\API\Instructor\LivestreamController;
use App\Http\Controllers\API\Instructor\RegisterController;
use App\Http\Controllers\API\Instructor\SendRequestController;
use App\Http\Controllers\API\Verify\VerificationController;
use App\Http\Controllers\API\Common\CourseController as CommonCourseController;
use App\Http\Controllers\API\Instructor\PostController;
use App\Http\Controllers\API\Instructor\SupportBankController;
use App\Http\Controllers\API\Student\NoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

#============================== ROUTE AUTH =============================
Route::prefix('auth')->as('auth.')->group(function () {
    Route::post('sign-up', [AuthController::class, 'signUp']);
    Route::post('sign-in', [AuthController::class, 'signIn']);
    Route::post('verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    Route::get('google', [GoogleController::class, 'redirectToGoogle']);
    Route::get('google/callback', [GoogleController::class, 'handleGoogleCallback']);
});

Route::get('/vnpay-callback', [TransactionController::class, 'vnpayCallback']);
Route::get('/reset-password/{token}', function ($token) {
    return view('emails.auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');
#============================== ROUTE SEARCH =============================
Route::prefix('search')
    ->group(function () {
        Route::get('/', [SearchController::class, 'search']);
    });

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/vnpay-payment', [TransactionController::class, 'createVNPayPayment']);

    Route::prefix('auth')->as('auth.')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });

    Route::prefix('instructor')->as('instructor.')->group(function () {
        Route::post('register', [RegisterController::class, 'register']);
    });

    Route::get('user', function (Request $request) {
        return $request->user();
    });

    #============================== ROUTE USER =============================
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'showProfile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::put('/change-password', [UserController::class, 'changePassword']);
        Route::get('/my-course-bought', [UserController::class, 'getMyCourseBought']);

        #============================== ROUTE NOTIFICATION =============================
        Route::prefix('notifications')
            ->group(function () {
            });
    });

    #============================== ROUTE WISH LIST =============================
    Route::prefix('wish-lists')->as('wish-lists.')->group(function () {
        Route::get('/', [WishListController::class, 'index']);
        Route::post('/', [WishListController::class, 'store']);
        Route::delete('/{wishListID}', [WishListController::class, 'destroy']);
    });

    #============================== ROUTE TRANSACTION =============================
    Route::prefix('transactions')->as('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/{transactionID}', [TransactionController::class, 'show']);
        Route::post('/deposit', [TransactionController::class, 'deposit']);
        Route::post('/buyCourse', [TransactionController::class, 'buyCourse']);
    });

    #============================== ROUTE LEARNING =============================
    Route::prefix('learning-path')
        ->group(function () {
        });

    #============================== ROUTE INSTRUCTOR MANAGE =============================
    Route::prefix('instructor')
        ->middleware('roleHasInstructor')
        ->as('instructor.')
        ->group(function () {
            Route::prefix('statistics')
                ->group(function () {
                });

            Route::prefix('manage')
                ->group(function () {
                    #============================== ROUTE COURSE =============================
                    Route::prefix('courses')
                        ->group(function () {
                            Route::get('/', [CourseController::class, 'index']);
                            Route::get('/{course}', [CourseController::class, 'getCourseOverView']);
                            Route::post('/', [CourseController::class, 'store']);
                            Route::put('/{course}/courseOverView', [CourseController::class, 'updateCourseOverView']);
                            Route::put('/{course}/courseObjective', [CourseController::class, 'updateCourseObjectives']);
                            Route::delete('/{course}', [CourseController::class, 'deleteCourse']);
                            Route::get('/{slug}/chapters', [CourseController::class, 'getChapters']);
                            Route::get('/{slug}/validate-course', [CourseController::class, 'validateCourse']);
                            Route::get('/{slug}/check-course-complete', [CourseController::class, 'checkCourseComplete']);

                            Route::post('{slug}/submit-course', [SendRequestController::class, 'submitCourse']);
                        });

                    #============================== ROUTE CHAPTER =============================
                    Route::prefix('chapters')
                        ->as('chapters.')
                        ->group(function () {
                            Route::post('/', [ChapterController::class, 'storeChapter']);
                            Route::put('/{chapter}/update-order', [ChapterController::class, 'updateOrderChapter']);
                            Route::put('/{slug}/{chapter}', [ChapterController::class, 'updateContentChapter']);
                            Route::delete('/{slug}/{chapter}', [ChapterController::class, 'deleteChapter']);
                            Route::get('/{chapter}/lessons', [ChapterController::class, 'getLessons']);
                        });

                    #============================== ROUTE LESSON =============================
                    Route::prefix('lessons')
                        ->as('lessons.')
                        ->group(function () {
                            Route::post('/', [LessonController::class, 'storeLesson']);
                            Route::put('/{lesson}/update-order', [LessonController::class, 'updateOrderLesson']);
                            Route::put('/{chapterId}/{lesson}', [LessonController::class, 'updateTitleLesson']);
                            Route::put('/{chapterId}/{lesson}/content', [LessonController::class, 'updateContentLesson']);
                            Route::delete('/{chapterId}/{lesson}', [LessonController::class, 'deleteLesson']);

                            Route::post('/{chapterId}/store-lesson-video', [\App\Http\Controllers\API\Instructor\LessonVideoController::class, 'storeLessonVideo']);
                            Route::get('/{chapterId}/{lesson}/show-lesson', [\App\Http\Controllers\API\Instructor\LessonVideoController::class, 'getLessonVideo']);
                            Route::put('/{chapterId}/{lesson}/update-lesson-video', [\App\Http\Controllers\API\Instructor\LessonVideoController::class, 'updateLessonVideo']);

                            Route::post('/{chapterId}/store-lesson-quiz', [\App\Http\Controllers\API\Instructor\QuizController::class, 'storeLessonQuiz']);

                            Route::prefix('quiz')
                                ->group(function () {
                                    Route::get('download-quiz-form', [\App\Http\Controllers\API\Instructor\QuizController::class, 'downloadQuizForm']);
                                    Route::get('{quiz}/show-quiz', [\App\Http\Controllers\API\Instructor\QuizController::class, 'showQuiz']);
                                    Route::get('{question}/show-quiz-question', [\App\Http\Controllers\API\Instructor\QuizController::class, 'showQuestion']);
                                    Route::post('{quiz}/store-quiz-question-multiple', [\App\Http\Controllers\API\Instructor\QuizController::class, 'storeQuestionMultiple']);
                                    Route::post('{quiz}/store-quiz-question-single', [\App\Http\Controllers\API\Instructor\QuizController::class, 'storeQuestionSingle']);
                                    Route::post('{quiz}/import-quiz-question', [\App\Http\Controllers\API\Instructor\QuizController::class, 'importQuiz']);
                                    Route::put('{question}/update-quiz-question', [\App\Http\Controllers\API\Instructor\QuizController::class, 'updateQuestion']);
                                    Route::delete('{question}/delete-quiz-question', [\App\Http\Controllers\API\Instructor\QuizController::class, 'deleteQuestion']);
                                });

                            Route::post('/{chapterId}/store-lesson-document', [DocumentController::class, 'storeLessonDocument']);
                            Route::put('/{documentID}', [DocumentController::class, 'update']);

                            Route::post('/{chapterId}/store-lesson-coding', [\App\Http\Controllers\API\Instructor\LessonCodingController::class, 'storeLessonCoding']);
                            Route::get('/{lesson}/{coding}/coding-exercise', [\App\Http\Controllers\API\Instructor\LessonCodingController::class, 'getCodingExercise']);
                            Route::put('/{lesson}/{coding}/coding-exercise', [\App\Http\Controllers\API\Instructor\LessonCodingController::class, 'updateCodingExercise']);
                        });
                });

            #============================== ROUTE POST =============================
            Route::prefix('posts')->as('posts.')->group(function () {
                Route::get('/', [PostController::class, 'index']);
                Route::get('/{post}', [PostController::class, 'getPostBySlug']);
                Route::post('/', [PostController::class, 'store']);
                Route::put('/{post}', [PostController::class, 'update']);
            });
        });

    #============================== ROUTE NOTE =============================
    Route::prefix('notes')->as('notes.')->group(function () {
        Route::get('/{courseId}', [NoteController::class, 'index']);
        Route::post('/', [NoteController::class, 'store']);
        Route::put('/{note}', [NoteController::class, 'update']);
        Route::delete('/{note}', [NoteController::class, 'destroy']);
    });

    #============================== ROUTE COUPON =============================
    Route::prefix('coupons')->as('coupons.')->group(function () {
    });

    #============================== ROUTE TRANSACTION =============================
    Route::prefix('transactions')->as('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/{transactionID}', [TransactionController::class, 'show']);
        Route::post('/deposit', [TransactionController::class, 'deposit']);
        Route::post('/buyCourse', [TransactionController::class, 'buyCourse']);
    });

    #============================== ROUTE CHAT =============================
    Route::prefix('chats')
        ->group(function () {
        });

    #============================== ROUTE COMMENT =============================
    Route::prefix('comments')
        ->group(function () {
            Route::post('/', [CommentController::class, 'store']);
            Route::put('/{id}', [CommentController::class, 'update']);
            Route::delete('/{id}', [CommentController::class, 'destroy']);
            Route::get('/{commentableId}/{commentableType}', [CommentController::class, 'index']);
        });

    #============================== ROUTE RATING =============================
    Route::prefix('ratings')
        ->group(function () {
            Route::get('/{courseId}', [RatingController::class, 'index']);
            Route::post('/', [RatingController::class, 'store']);
        });

    #============================== ROUTE LIVESTREAM =============================
    Route::prefix('livestreams')
        ->group(function () {
            Route::post('/', [LivestreamController::class, 'createLiveStream']);
        });

    #============================== ROUTE POST =============================
    Route::prefix('posts')->as('posts.')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::post('/', [PostController::class, 'store']);

    });
});

#============================== ROUTE COURSE =============================
Route::prefix('courses')
    ->group(function () {
        Route::get('/discounted', [CommonCourseController::class, 'getDiscountedCourses']);
        Route::get('/free', [CommonCourseController::class, 'getFreeCourses']);
        Route::get('/popular', [CommonCourseController::class, 'getPopularCourses']);
        Route::get('/top-categories-with-most-courses', [CommonCourseController::class, 'getTopCategoriesWithMostCourses']);
        Route::get('/{slug}', [CommonCourseController::class, 'getCourseDetail']);
    });

#============================== ROUTE BANNER =============================
Route::get('/banners', [BannerController::class, 'index']);

#============================== ROUTE CATEGORY =============================
Route::get('/categories', [\App\Http\Controllers\API\Common\CategoryController::class, 'index']);

#============================== ROUTE POST =============================
Route::prefix('blogs')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\API\Common\BlogController::class, 'index']);
        Route::get('/{blog}', [\App\Http\Controllers\API\Common\BlogController::class, 'getBlogBySlug']);
    });

#============================== ROUTE SUPPORT BANK =================================
Route::prefix('support-banks')->group(function () {
    Route::post('/', [SupportBankController::class, 'index']);
    Route::post('/generate-qr', [SupportBankController::class, 'generateQR']);
});

#============================== ROUTE QA SYSTEM =================================
Route::prefix('qa-systems')->group(function () {
    Route::get('/', [\App\Http\Controllers\API\Common\QaSystemController::class, 'index']);
});

Route::prefix('mux-upload')->group(function () {
    Route::post('video', [\App\Http\Controllers\Api\Instructor\HandleVideoController::class, 'handleUpload']);
});

#============================== ROUTE VERIFY MAIL =================================
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/resend', [VerificationController::class, 'resend'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.resend');
