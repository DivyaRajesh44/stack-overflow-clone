<?php

namespace App\Http\Controllers;

use App\Http\Requests\Questions\CreateQuestionRequest;
use App\Http\Requests\Questions\UpdateQuestionRequest;
use App\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QuestionsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'])
                ->only(
                    [
                        'create',
                        'store',
                        'edit',
                        'update',
                        'destroy',
                    ]
                );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /**
         * Eager loader : loads all things at that time only with relationships
         */
        $questions = Question::with('owner')
                        ->latest()
                        ->paginate(10);

        return view('questions.index', compact([
            'questions',
        ]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $this->middleware(['auth']);
        app('debugbar')->disable();
        return view('questions.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateQuestionRequest $request)
    {
        auth()->user()
            ->questions()
            ->create([
            'title' => $request->title,
            'body' => $request->body,
        ]);

        session()->flash('success', "Question has been added Successfully!");
        return redirect(route('questions.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Question  $question
     * @return \Illuminate\Http\Response
     */
    // public function show($slug)
    // {
    //     $question = Question::where('slug', $slug)->firstOrFail();
    // }

    public function show(Question $question)
    {
        $question->increment('views_count');
        return view('questions.show', compact([
            'question',
        ]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Question  $question
     * @return \Illuminate\Http\Response
     */
    public function edit(Question $question)
    {
        if ($this->authorize('update', $question)) {
            app('debugbar')->disable();

            return view('questions.edit', compact([
                'question'
            ]));
        }
        abort(403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Question  $question
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateQuestionRequest $request, Question $question)
    {
        
        if ($this->authorize('update', $question)) {
            $question->update([
                'title' => $request->title,
                'body' => $request->body,
            ]);
            session()->flash('success', "Question has been updated Successfully!");
            return redirect(route('questions.index'));
        }
        abort(403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Question  $question
     * @return \Illuminate\Http\Response
     */
    public function destroy(Question $question)
    {

        /**
         * METHOD 1 :
         * if (Gate::allows('update-question', $question)) {

            }

         * METHOD 2 :
         * if (auth()->user()->can('delete-question', $question)) {
            
            }
         */
        if ($this->authorize('delete', $question)) {
            $question->delete();
            session()->flash('success', 'Question has been deleted Successfully!!');
            return redirect()->to(route('questions.index'));
        }
        abort(403);
    }
}