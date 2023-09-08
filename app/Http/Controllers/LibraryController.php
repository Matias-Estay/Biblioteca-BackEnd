<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use DB;

class LibraryController extends Controller
{
    public function POST_Collection(Request $data)
    {
        set_time_limit(0);
        ini_set('memory_limit', '500M');
        $response = Http::withToken(env('CHATDOC_KEY',''))->post('https://api.chatdoc.com/api/v1/collections',
        [
            'name' => $data->name
        ]
        );
        $id = DB::table('collections')->insertGetId(
            ['id_api' => $response->id],
            ['name' => $response->name],
            ['created_at' => $response->created_at],
            ['id_user' => $user->id]
        );
        foreach ($data->file('files') as $file) {
            $response_doc = Http::withToken(env('CHATDOC_KEY',''))->post('https://api.chatdoc.com/api/v1/collections',
            [
                'collection_id'=> $response->id,
                'file' => $file                
            ]
            );
            $id_doc = DB::table('documents')->insertGetId(
                [ 'id_api' =>  $response_doc->id],
                [ 'id_user' =>  $user->id],
                [ 'id_collection' => $response->id],
                [ 'name' =>  $response_doc->name],
                ['extention' => $file->getClientOriginalExtension()],
                ['created_at' => $response->created_at],
                ['id_user' => $user->id]
            );
        }
    }
    
}
