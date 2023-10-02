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

    public function POST_UploadDocumentsCollection(Request $request)
    {
        set_time_limit(0);
        $user = Auth::user();
        foreach ($request->file('files') as $file) {
            $response_doc = Http::attach('file', file_get_contents($file), $file->getClientOriginalName())->withHeaders([
                'Authorization' => 'Bearer ' . env('CHATDOC_KEY',''),
            ])->post('https://api.chatdoc.com/api/v1/documents/upload',['collection_id'=> $request->id_api]);
            $id_doc = DB::table('documents')->insertGetId(
                ['id_api' =>  $response_doc["data"]["id"],
                 'id_user' =>  $user->id,
                 'id_collection' => $request->id_api,
                 'name' =>  $response_doc["data"]["name"],
                 'extention' => $file->getClientOriginalExtension(),
                 'created_at' => (new DateTime())->setTimestamp($response_doc["data"]["created_at"]),
                 'id_user' => $user->id
                ]
            );
        }
    }
    
    public function GET_Collections(Request $data)
    {
        $user = Auth::user();
        $response = DB::table('collections as c')->select(DB::raw("REPLACE(c.name,'\"','') as name, c.id, c.id_api, if(p.description='All allowed',true,false) as description"))
        ->join('shared as sh', 'c.id','=','sh.id_collection')
        ->join('permissions as p','p.id','=','sh.id_permission')
        ->where('id_user','=',$user->id)->get();
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
    
    public function GET_quota(Request $request)
    {
        $response = Http::withToken(env('CHATDOC_KEY',''))->get('https://api.chatdoc.com/api/v1/users/quota');
        return $response;
    }

    public function GET_CollectionDocuments(Request $request)
    {
        $documents = DB::SELECT("SELECT id_api, name as title, if(extention='pdf','pdf-box',if(extention='docx','word','word')) as extention, created_at as subtitle FROM documents as d WHERE d.id_collection='".$request->id_api."';");
        return $documents;
    }

    public function POST_DeleteDocument(Request $request)
    {
        $response = Http::withToken(env('CHATDOC_KEY',''))->post("https://api.chatdoc.com/api/v2/documents/".$request->id_doc);
        return $response;
    }

    public function POST_ShareCollection(Request $request)
    {
        try{
            DB::table('shared')->where('id_collection','=',$request->item['id'])->update(['id_permission'=>$request->item['description']==true?1:2]);
            return http_response_code(200);
        }catch(Exception $ex){
            return http_response_code(500,$ex);
        }
    }

    public function GET_IsShared(Request $request)
    {
        try{
            $isShared = DB::table('shared')->select("id")
            ->where('id_collection','=',$request->id_col)
            ->where('id_permission','=',1)->get();
            if(sizeof($isShared)>0){
                return true;
            }else{
                return false;
            }
        }catch(Exception $ex){
            return false;
        }
    }
}
