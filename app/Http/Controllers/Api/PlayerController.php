<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlayerResource;
use App\Models\Player;
use App\Models\Stat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class PlayerController extends Controller
{
    /**
     * Here I decided to store a new record for each request to this endpoint for a few reasons
     * The first reason is that the client needs a vary fast response in this endpoint and create a new record is better than update an existing record
     * because if we are trying to update the same resource at the same time in the database one of this request will need to wait (because this can create a race condition)
     * and that is a lot of time and also we don't want to create a query monster to achieve that. I think that taking this solution
     * we have something maintainable
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'player_id' => 'required|exists:players,id',
            'stats' => 'array|min:1',
            'stats.*.name' => 'required|string',
            'stats.*.value' => 'required|numeric'
        ]);

        $player = Player::find($fields['player_id']);
        $created_at = Carbon::now();

        //get an array of stats fields
        $stats = collect($request->get('stats'))->map(function ($item, $key) use ($player, $created_at) {
            return ['name' => $item['name'], 'value' => $item['value'], 'player_id' => $player->id, 'created_at' => $created_at];
        })->toArray();

        //save stats attached to the
        //we need to be aware that using insert function we are not triggering some events in the boot method of the model
        $player->stats()->insert($stats);

        //new values added
        //we can also implement this with another var name if we want to track a possible insertion of players
        Redis::set('new_stats_records',true);

        return PlayerResource::make($player);
    }

    public function getStandings(Request $request)
    {
        $statQueryParameter = $request->get('stat_name');

        //data structure
        $data = [];

        //first verify if we have new values added
        //if the answer is no then we can use our previous request in redis
        $haveNewValues = Redis::get('new_stats_records');

        if (is_null($haveNewValues) || !is_null($haveNewValues) && $haveNewValues) {
            //use a chunk function in order to don't kill the server with only one query
            // because this can use too many resources of the server
            //if we have millions of players and stats data
            DB::table('players')
                ->select('id')
                ->orderBy('id', 'desc')
                ->chunk(10000, function ($players) use (&$data, $statQueryParameter) {

                    $stats = null;

                    $statsQuery = Stat::query()
                        ->whereIn('player_id', $players->pluck('id'))
                        ->selectRaw('`player_id`,`name`, SUM(`value`) as `value`')
                        ->groupBy(['player_id', 'name']);


                    //if we receive the query parameter then add this to the query
                    if (!is_null($statQueryParameter))
                        $statsQuery->orderByRaw("CASE WHEN NAME = '$statQueryParameter' THEN 1 END DESC");


                    $stats = $statsQuery->get()->toArray();

                    //first create our data structure for each player
                    //because even if the player doesn't have any stats they should have this attribute as an empty array
                    //we are going to built a hash map taking player_id as key|hash
                    foreach ($players as $player) {
                        $data[$player->id] = [
                            'player_id' => $player->id,
                            'stats' => []
                        ];
                    }

                    //use $data as a hash map using the player_id as hash
                    //attach all stats to each player
                    foreach ($stats as $stat) {
                        //hash|key
                        $playerId = $stat['player_id'];
                        $statFields = [
                            'name' => $stat['name'],
                            'value' => $stat['value']
                        ];

                        //push stat to the player
                        array_push($data[$playerId]['stats'], $statFields);
                    }
                });

            //get only array values without the player_id as key
            $standings = ['standings' => array_values($data)];

            //store value in redis
            //this function json_encode can take time...is a possibility set this process to the queue
            Redis::set('standings',json_encode($standings));

            //set new values to false
            Redis::set('new_stats_records',false);
        }else{
            $standings = json_decode(Redis::get('standings'),true);
        }

        return response($standings);
    }
}

