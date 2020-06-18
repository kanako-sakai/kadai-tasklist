<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Task; 

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (\Auth::check()) { 
            // 認証済みユーザを取得
            $user = \Auth::user();
            // ユーザの投稿の一覧を作成日時の降順で取得
            $tasks = $user->tasks()->orderBy('created_at', 'desc')->paginate(10);

            $data = [
                'user' => $user,
                'tasks' => $tasks,
            ];
            
            return view('tasks.index', $data);
        }else {
            
            // Welcomeビューでそれらを表示
            return view('welcome');
        };
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $task = new Task;
        
        //Show task creation view
        return view('tasks.create', [
            'task' => $task,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //バリデーション
        $request->validate([
            'content'=>'required|max:255',
            'status'=>'required|max:10',
        ]);
        
        // 認証済みユーザ（閲覧者）の投稿として作成（リクエストされた値をもとに作成）
        $request->user()->tasks()->create([
            'content' => $request->content,
            'status' => $request->status,
        ]);
        
        //Redirect to top page
        return redirect('/');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //get task
        $task = Task::findOrFail($id);
        
        //show on detail view
        return view('tasks.show', [
            'task' => $task,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //get task
       $task = \App\Task::findOrFail($id);
        
        //承認ユーザの場合削除
        if (\Auth::id() === $task->user_id) {
            //show on edit view
            return view('tasks.edit', [
                'task' => $task,
            ]);
        }
    }    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //valudation
        $this->validate($request, [
            'content'=>'required|max:255',
            'status'=>'required|max:10',
        ]);

        //get task
        $task = \App\Task::findOrFail($id);
        
         //承認ユーザの場合アップデート
        if (\Auth::id() === $task->user_id) {
            //$requestの内容で$taskをupdateする。
            $task->content=$request->content;
            $task->save();
        }
        //redirect to top page
        return redirect('/');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     
    public function destroy($id)
    {
        //get taks
        $task = \App\Task::findOrFail($id);
        
        //承認ユーザの場合削除
        if (\Auth::id() === $task->user_id) {
            $task->delete();
        }
        
        //redirect to top page
        return redirect('/');
    }
}
