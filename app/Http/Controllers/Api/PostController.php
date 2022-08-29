<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Models\Image;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::with('user')->paginate(10);
        return response()->json($posts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePostRequest $request)
    {
        $post = new Post();
        $post->title = $request->title;
        $post->body = $request->body;
        $post->category_id = $request->category_id;
        $post->user_id = auth()->user()->id;

        if($post->save()) {
            $path = $request->file('image')->store('images');
            $image = new Image();
            $image->path = $path;
            $image->save();

            $post->images()->attach($image->id);

            return response()->json([
                'message' => 'Post creado correctamente',
                'post' => $post->load('images')
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return response()->json([
            'message' => 'Post solicitado',
            'post' => $post->load('images')
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $post->title = $request->title;
        $post->body = $request->body;
        $post->category_id = $request->category_id;

        // if($request->hasFile('image')) {
        //     //Storage::delete($post->path)
        //     $post->images->detach();
                        
        // }

        if($post->save()) {
            if($request->hasFile('image')) {
                $post->images()->detach();

                $path = $request->file('image')->store('images');
                $image = new Image();
                $image->path = $path;
                $image->save();

                $post->images()->attach($image->id);    
            }

            return response()->json([
                'message' => 'Post actualizado correctamente',
                'post' => $post->load('images')
            ]);
        }

        return response()->json([
            'message' => 'Post NO actualizado correctamente',
            'post' => $post->load('images')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        //$post->images()->detach();
        $post->delete();

        return response()->json([
            'message' => 'Post eliminado correctamente',
        ]);
    }
}
