<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use DateTime;
use DB;

class LibraryController extends Controller
{
    public function POST_Collection(Request $data)
    {
        set_time_limit(0);
        $user = Auth::user();
        $response = Http::withToken(env('CHATDOC_KEY',''))->post('https://api.chatdoc.com/api/v1/collections',
        [
            'name' => $data->name
        ]
        );
        $id = DB::table('collections')->insertGetId(
            ['id_api' => $response["data"]["id"],
            'name' => $response["data"]["name"],
            'created_at' => (new DateTime())->setTimestamp($response["data"]["created_at"]),
            'id_user' => $user->id
            ]
        );
        foreach ($data->file('files') as $file) {
            $response_doc = Http::attach('file', file_get_contents($file), $file->getClientOriginalName())->withHeaders([
                'Authorization' => 'Bearer ' . env('CHATDOC_KEY',''),
            ])->post('https://api.chatdoc.com/api/v1/documents/upload',['collection_id'=> $response["data"]["id"]]);
            $id_doc = DB::table('documents')->insertGetId(
                ['id_api' =>  $response_doc["data"]["id"],
                 'id_user' =>  $user->id,
                 'id_collection' => $response["data"]["id"],
                 'name' =>  $response_doc["data"]["name"],
                 'extention' => $file->getClientOriginalExtension(),
                 'created_at' => (new DateTime())->setTimestamp($response_doc["data"]["created_at"]),
                 'id_user' => $user->id
                ]
            );
        }
        return 'Ok';
    }
    
    public function GET_Collections(Request $data)
    {
        $user = Auth::user();
        $response = DB::table('collections')->select(DB::raw("REPLACE(name,'\"','') as name, id, id_api"))->where('id_user','=',$user->id)->get();
        return $response;
    }

    public function POST_AskQuestion(Request $data)
    {
        $user = Auth::user();
        $documents = DB::table('documents')->select('id_api')->where('id_collection','=',$data->id_api)->get();
        $documents_final = [];
        for($i = 0;$i<sizeof($documents);$i++){
            array_push($documents_final,$documents[$i]->id_api);
        }
        $response = Http::withToken(env('CHATDOC_KEY',''))->post('https://api.chatdoc.com/api/v1/questions/multi-documents',
        [
            'question' => $data->question,
            'upload_ids' => $documents_final
        ]
        );
        return $response;
    }
}
