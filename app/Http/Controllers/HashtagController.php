<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tweet;
use App\Models\Hashtag;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;


class HashtagController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validateWithBag('post', [
            'hashtag' => ['required', 'string', 'max:50', 'alpha_dash'],
        ]);

        $token = \config('twitter.bearer_token');
        $url = \config('twitter.api_url');

        $response = Http::withToken($token)->get("$url/tweets/search/recent", [
            'query' => '#'.$validated['hashtag'],
            'tweet.fields' => 'created_at',
            'expansions' =>	'author_id'
        ]);

        if(!$response->successful()) {
            return response()->json(['error' => 'A aplicação não conseguiu retornar os tweets'], 500);
        }

        $hashtag = Hashtag::where('hashtag', $validated['hashtag'])->first();

        if (!$hashtag) {
            $hashtag = Hashtag::create($validated);
        }

        $tweets = $response->json()['data'];
        $users = $response->json()['includes']['users'];

        foreach ($tweets as &$value) {
            $tweet = Tweet::where('tweet_id', $value['id'])
            ->where('hashtag_id', $hashtag->id)
            ->first();
            
            foreach ($users as $user) {
                if($user['id'] === $value['author_id']){
                    $value['name'] = $user['name'];
                    $value['username'] = $user['username'];
                    break;
                }
            }

            $value = [
                'hashtag_id' => $hashtag->id,
                'author_id' => $value['author_id'],
                'name' => $value['name'],
                'username' => $value['username'],
                'created_at' => Tweet::correctDateTime($value['created_at']),
                "tweet_id" => $value['id'],
                "text" => $value['text']
            ];
            
            if (!$tweet) {
                Tweet::create($value);
            }
        }

        return response()->json($tweets, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
