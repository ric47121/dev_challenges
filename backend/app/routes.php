<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Entities\User;
use Entities\Issue;
use Entities\Vote;
use Entities\Issues;

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

return function (Slim\App $app) {

    ##### `GET /issue/{:issue}` - Returns the status of issue
    // Because during `voting` status the votes are secret you must hide each vote until all members voted.
    $app->get('/issue/{issue}', function (Request $request, Response $response, array $args) {
       
        $issue = $args['issue'];
        $res = [];

        if( Issues::findByIdInMemory($issue) ){
            Issues::loadIssuesFromMemory();
            $Objissue = Issues::findById($issue);
            
            $Objissue->checkStatus();
            $res['status'] = $Objissue->status; //'voting';

            $res['members'] = $Objissue->getMmbers() ;

            if( $Objissue->status != 'voting' ){
                $res['avg'] = $Objissue->calculateAvg(); 
            }
            
        }else{
            // $msj = "NO existe esta issue ". $issue;
            $res['status'] = 'error';
            $res['members'] = [];
        }

     
        // $res['status'] = 'voting';
        // $res['msj'] = $msj;

        $response->getBody()->write(json_encode($res));
        return $response->withHeader('Content-Type', 'application/json');
    });

    ##### `POST /issue/{:issue}/join` - Used to join `{:issue}`. 
    // - If issue not exists generate a new one.
    // - Must receive a payload with the intended name. ie: `{"name": "florencia"}`
    // - Feel free to use a session or token to keep identified the user in subsequent requests.
    $app->post('/issue/{issue}/join', function (Request $request, Response $response, array $args) {
        $issue = $args['issue'];

        $data = $request->getParsedBody();
        $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
 
        Issues::loadIssuesFromMemory();
 
        if( Issues::findByIdInMemory($issue) ){

            $Objissue = Issues::findById($issue);
            $Objissue->checkStatus();
            if( $Objissue->status != 'voting' ){
                $msj = "the issue ".$issue." no permit votes.";           
 
            }else{
                $Objissue->loadUsersFromMemory();
                $user = $Objissue->findUserByName($name);
                if( $user ){
                    $msj = "the user ".$user->name." already exist in the issue ". $issue;
                }else{
                    $user = new User;
                    $user->name = $name;     
                    $msj = "se creo el usuario ".$user->name ." y se lo agrego a la issue ". $issue;           
    
                    $vote = new Vote('wait', -1);
                    $user->updateVote($vote);
                    $user->status = 'waiting';

                    $Objissue->addUser($user);            
                    $Objissue->saveInMemory();   
                }
            }



        }else{
            $Objissue = new Issue($issue);
            $Objissue->status = 'voting';
            Issues::push($Objissue);

            $user = new User;
            $user->name = $name;   

            $Objissue->addUser($user);            
            $msj = "el usuario ".$user->name." se agrego a la issue ". $issue;
            $Objissue->saveInMemory();   
        }

        Issues::addIntoMemory($issue); //add en redis set
        Issues::saveIssuesInMemory(); //save json arr
          
        $res = [];
        $res['msj'] = $msj;     

        $response->getBody()->write(json_encode($res));
        return $response->withHeader('Content-Type', 'application/json');
    });

    ##### `POST /issue/{:issue}/vote` - Used to vote `{:issue}`. Must receive a payload with the vote value.
    // - Reject votes when status of `{:issue}` is not `voting`. 
    // - Reject votes if user not joined `{:issue}`. 
    // - Reject votes if user already `voted` or `passed`. 
    $app->post('/issue/{issue}/vote', function (Request $request, Response $response, array $args) {
        $issue = $args['issue'];

        $data = $request->getParsedBody();
        $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
        $value = filter_var($data['value'], FILTER_SANITIZE_NUMBER_INT);

        Issues::loadIssuesFromMemory();
 
        if( Issues::findByIdInMemory($issue) ){
 
            $Objissue = Issues::findById($issue);

            if( !$Objissue->permitVote() ){
                $status = false;        
                $msj = "the issue ".$issue." no permit vote"; 
            }else{
              
                // $Objissue->loadUsersFromMemory();
                $user = $Objissue->findUserByName($name);
                
                if( $user ){

                    if( $user->status != 'waiting' ){
                        $status = false;
                        $msj = "the user ".$name."  already voted"; 
                    }else{
                        $status = true;
                        $msj = 'vote updated for user '. $name;
                        //do vote
                        $vote = new Vote('voted', $value);
                        $user->updateVote($vote); 
                    }

                }else{
                    $status = false;        
                    $msj = "the user is not joined to ".$issue; 
                }  
            }


        }else{
            $status = false;        
            $msj = "the issue ".$issue. " no exist";
        }

        Issues::saveIssuesInMemory();
  
        $res = [];
        $res['status'] = $status;     
        $res['msj'] = $msj;     

        $response->getBody()->write(json_encode($res));
        return $response->withHeader('Content-Type', 'application/json');
    });



};
