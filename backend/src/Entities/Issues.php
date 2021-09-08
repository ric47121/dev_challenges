<?php
declare(strict_types=1);

namespace Entities;

use Entities\User;
use Entities\Vote;
use Entities\Issue;

class Issues{

    public static $issues_arr = [];
    // public static $issues_obj = [];

    public static function clearAll()
    {
        self::$issues_arr = [];

        $redis = new \Redis();
        $redis->connect('redis', 6379);

        $redis->flushAll();
    }
    public static function loadIssuesFromMemory()
    {
        $redis = new \Redis();
        $redis->connect('redis', 6379);

        $data = $redis->get('issues_arr');
        if( $data ){
            $arr = json_decode($data); 
            self::$issues_arr = [];

            foreach ($arr as $d) {
                $issue = new Issue($d->id, $d->status);

                $issue->users_arr = [];
                foreach ($d->users_arr as $u) {
                    $user = new User($u->name, $u->status);
                    $user->vote = new Vote($u->vote->status, $u->vote->value);
                    $issue->users_arr[] = $user;
                }

                self::$issues_arr[] = $issue;
            }

        }else{
            self::$issues_arr = [];
        }
        
    }

    public static function getUsersForIssue(int $issue)
    {
        $cant = count(self::$issues_arr);
        for ($i=0; $i < $cant; $i++) { 
            $obj = self::$issues_arr[$i];
            // var_dump($obj);
            if($obj->id == $issue){
                return $obj->users_arr;
            }
        }                
        return null;        
    }

    public static function saveIssuesInMemory()
    {
        $redis = new \Redis();
        $redis->connect('redis', 6379);

        if( self::$issues_arr ){
            $redis->set('issues_arr', json_encode(self::$issues_arr));
  
        }else{
            $redis->set('issues_arr', json_encode([]));
        }
    }

    public static function loadAllFromMemory()
    {
        $redis = new \Redis();
        $redis->connect('redis', 6379);

        $data = $redis->sMembers('issues');
        self::$issues_arr = $data;
    }

    public static function findByIdInMemory($id)
    {
        $redis = new \Redis();
        $redis->connect('redis', 6379);
        return $redis->sIsMember('issues', $id);    
    }

    public static function addIntoMemory($issue)
    {
        $redis = new \Redis();
        $redis->connect('redis', 6379);
        $redis->sAdd('issues', $issue);  
        
        $redis->set('issue:'.$issue.':status', 'voting');
    }

    public static function removeFromMemory($issue)
    {
        $redis = new \Redis();
        $redis->connect('redis', 6379);
        $redis->sRem('issues', $issue);   

        $redis->del('issue:'.$issue.':status');
    }


    public static function findById($id)
    {
        $cant = count(self::$issues_arr);
        for ($i=0; $i < $cant; $i++) { 
            $obj = self::$issues_arr[$i];

            if($obj->id == $id){
                return $obj;
            }
        }
        return null;
    }

    public static function push(Issue $issue)
    {
        self::$issues_arr[] = $issue;
    }

    public static function getCant()
    {
        return count( self::$issues_arr );
    }

}