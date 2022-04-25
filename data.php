<?php
require 'vendor/autoload.php';

use LearnositySdk\Request\DataApi;

/**
* Data API documentation:
* https://reference.learnosity.com/data-api/endpoints
**/

function processSessions() 
{
    $sessions = downloadSessions();

    ////
    // To do: Add code to process the sessions and save to CSV,
    // feel free to add more functions if needed.
    //

    // Question Reference => question_reference
    // Session ID => session_id
    // Q. Score => score
    // Max Score => max_score
    // Question Type => question_type
    // Column Headers for CSV files
    $csv_fields = [
      "Session ID", 
      "Question Reference", 
      "Question Type", 
      "Score", 
      "Max Score"
    ];

    // Open the CSV file to write to

    $fp = fopen('results.csv', 'w');

    // Write the header row to the csv
    fputcsv($fp, $csv_fields);

    // Nested loop which retrieves the session id, question_ref, question type, score and max_score,
    // These are written to an ordered array

    foreach($sessions as $sess_row){
      foreach($sess_row['responses'] as $resp){
        $data = [
                 $sess_row['session_id'],
                 $resp['question_reference'],
                 $resp['question_type'],
                 $resp['score'],
                 $resp['max_score']
        ];
        // Write row to csv file
        fputcsv($fp, $data);
      }
    } 
    // Close the file one
    fclose($fp);
}
function downloadSessions():array
{
    $request = json_decode(<<<EOS
    {
        "session_id": [
                        "a9c92dc1-725c-456a-acc9-cdd32fd9f81b",
                        "ecc0bdb2-2fc9-4489-b2d6-b818a8b0ebc0",
                        "ee5ed0c3-f590-40d0-8480-8037914738ba",
                        "93af036e-2aad-425e-a997-fa3827cb4c30"
                        ],
        "status": ["Completed"]
    }
    EOS
    , true);
    return makeDataAPICall($request, '/sessions/responses');
}

function makeDataAPICall(array $request, string $endpoint, string $consumer_key = 'yis0TYCu7U9V4o7M', $consumer_secret = '74c5fd430cf1242a527f6223aebd42d30464be22', $version = 'v2022.1.LTS', $action = 'get'):array
{

    $url = "https://data.learnosity.com/{$version}{$endpoint}";
    
    $security =[
        'consumer_key'=>$consumer_key,
        'domain' => 'localhost'
    ];

    $ret = [];
    $dataApi = new DataApi();
    $dataApi->requestRecursive(
        $url,
        $security,
        $consumer_secret,
        $request,
        $action,
        function ($return) use (&$ret, $endpoint) {
            if (is_string($return)) {
                $ret = "";
                $ret .= $return;
            } else {
                $ret = array_merge($ret, $return['data']);
            }
            echo PHP_EOL.$endpoint.PHP_EOL;
            print_r(json_encode($return['meta']));
        }
    );

    return $ret;
}

processSessions();
